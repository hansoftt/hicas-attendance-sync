<?php

namespace App\Console\Commands;

use App\Models\MySqlAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Collection;
use Illuminate\Support\Facades\Storage;

class SyncAttendance extends Command
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
    protected $signature = 'app:sync-attendance';

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

        $mssqlAttendancesTable = (new \App\Models\MsSqlAttendance())->getTable();
        $logDeviceIdColumn = sprintf('%s.DeviceId', $mssqlAttendancesTable);

        /**
         * @var Collection $mssqlAttendances
         */
        $mssqlAttendances = \App\Models\MsSqlAttendance::query()
            ->where('DeviceLogId', '>=', $this->lastSynced)
            ->join('dbo.Devices', $logDeviceIdColumn, '=', 'dbo.Devices.DeviceId')
            ->select($mssqlAttendancesTable . '.*', 'dbo.Devices.IpAddress')
            ->orderBy('DeviceLogId')
            ->get()
            ->transform(function ($attendance) {
                // Transform the data if necessary
                return [
                    //'log_id' => $attendance->DeviceLogId,
                    'date' => Carbon::parse($attendance->LogDate)->format('Y-m-d'),
                    'biometric_id' => $attendance->UserId,
                    'punch_time' => Carbon::parse($attendance->LogDate)->format('Y-m-d H:i:s'),
                    'is_imported' => false,
                    //'device_ip' => $attendance->IpAddress,
                ];
            });

        $completedCount = 0;
        $mssqlAttendances->map(function ($attendance) use (&$completedCount) {
            // Insert into MySQL attendance table
            $exists = MySqlAttendance::query()->where($attendance)->exists();
            if (!$exists) {
                MySqlAttendance::query()->create($attendance);
            }
            $completedCount++;
        });

        $lastSynced = $mssqlAttendances->last()->DeviceLogId ?? $this->lastSynced;
        Storage::disk('local')->put('synced_attendance.log', $lastSynced);

        $this->info('Imported attendance: ' . $completedCount);
    }
}
