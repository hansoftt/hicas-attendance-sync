<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSqlAttendance extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv'; // Specify the SQL Server connection

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Set the table name dynamically if needed
        $this->table = sprintf('dbo.DeviceLogs_%s', now()->format('n_Y'));
    }
}
