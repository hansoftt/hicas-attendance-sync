<?php

namespace App\Console\Commands;

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
        $mssqlAttendances = \App\Models\MsSqlAttendance::query()->orderBy('DeviceLogId')->get();

        foreach ($mssqlAttendances as $attendance) {
            // Here you would typically fetch the corresponding record from MsSQL
            // and then insert or update it in MySQL.
            $this->info("Processing attendance ID: {$attendance->DeviceLogId}");
        }
    }
}
