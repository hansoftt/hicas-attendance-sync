<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncAttendance extends Command
{
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

        // Example: Fetch data from MsSQL and insert into MySQL
        $mssqlAttendances = \App\Models\MsSqlAttendance::query()
            ->orderBy('DeviceLogId');

        $mssqlAttendances = $mssqlAttendances
            ->join('dbo.Devices',  (new \App\Models\MsSqlAttendance())->getTable() . '.DeviceId', '=', 'dbo.Devices.DeviceId')
            ->select('dbo.DeviceLogs.*', 'dbo.Devices.IpAddress')
            ->get()
            ->transform(function ($attendance) {
                // Transform the data if necessary
                return [
                    'log_id' => $attendance->DeviceLogId,
                    'biometric_id' => $attendance->UserId,
                    'punch_time' => Carbon::parse($attendance->LogDate)->format('Y-m-d H:i:s'),
                    'device_ip' => $attendance->IpAddress,
                ];
            });

        foreach ($mssqlAttendances as $attendance) {

            $this->info("Processing attendance ID: {$attendance['log_id']}");
            $this->info("Biometric ID: {$attendance['biometric_id']} Logged at: {$attendance['punch_time']} from Device IP: {$attendance['device_ip']}");
        }
    }
}
