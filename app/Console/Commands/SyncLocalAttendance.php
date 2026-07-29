<?php

namespace App\Console\Commands;

use App\Models\MySqlAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Collection;
use Illuminate\Support\Facades\Storage;

class SyncLocalAttendance extends Command
{
    protected $lastSynced;

    public function __construct()
    {
        parent::__construct();

        if (Storage::disk('local')->exists('synced_attendance.log')) {
            $this->lastSynced = Storage::disk('local')->get('synced_attendance.log');
        } else {
            $this->lastSynced = 0;
        }
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-local-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Logic to sync attendance data between MySQL and SQL Server
        $this->info('Syncing attendance data...');

        /**
         * @var Collection $mssqlAttendances
         */
        $localAttendances = \App\Models\LocalMySqlAttendance::query()
            ->where('id', '>=', $this->lastSynced)
            ->get()
            ->transform(function ($attendance) {
                // Transform the data if necessary
                $date = Carbon::parse($attendance->date)->format('Y-m-d');
                return [
                    'id' => $attendance->id,
                    'date' => $date,
                    'biometric_id' => $attendance->employee_id,
                    'punch_time' => sprintf('%s %s', $date, $attendance->time),
                    'is_imported' => false,
                    'device_ip' => $attendance->device_ip,
                ];
            });

        $completedCount = 0;
        $lastSynced = 0;

        $localAttendances->map(function ($attendance) use (&$completedCount, &$lastSynced) {
            $lastSynced = $attendance['id'] ?? null;
            $attendance = collect($attendance)->except(['id'])->toArray();
            // Insert into MySQL attendance table
            $exists = MySqlAttendance::query()->where($attendance)->exists();
            if (!$exists) {
                MySqlAttendance::query()->create($attendance);
            }
            $completedCount++;
            $lastSynced = $attendance['id'] ?? $lastSynced;
        });

        // Update the last synced ID
        Storage::disk('local')->put('synced_attendance.log', $lastSynced);

        $this->info('Imported attendance: ' . $completedCount);
    }
}
