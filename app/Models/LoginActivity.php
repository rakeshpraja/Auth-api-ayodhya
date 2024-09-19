<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    use HasFactory;
    protected $table = 'login_activity';
    protected $fillable = [
        'user_id',
        'ip_address',
        'operating_system',
        'browser_name',
        'login_time',
        'Device_Name',
        'Device_Type',
        'logout_time'
    ];

}
