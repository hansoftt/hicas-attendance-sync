<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalMySqlAttendance extends Model
{
    use HasFactory;

    protected $connection = 'local'; // Specify the local MySQL connection

    protected $table = 'attendance'; // Specify the table name

    protected $fillable = [
        'id',
        'employee_id',
        'employee_name',
        'date',
        'time',
        'status',
        'machine_id',
        'device_ip',
    ];
}
