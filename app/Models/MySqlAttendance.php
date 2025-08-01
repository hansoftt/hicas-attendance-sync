<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MySqlAttendance extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; // Specify the MySQL connection

    protected $table = 'attendances'; // Specify the table name
}
