<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSqlDevice extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv'; // Specify the SQL Server connection

    protected $table = 'dbo.Devices'; // Default table name, can be overridden in constructor
}
