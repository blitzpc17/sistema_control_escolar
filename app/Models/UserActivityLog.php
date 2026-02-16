<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $table = 'user_activity_logs';
    public $timestamps = false; // tu tabla solo trae created_at

    protected $fillable = [
        'user_id','module_id','action',
        'entity_table','entity_id',
        'before_json','after_json',
        'ip_address','user_agent','created_at'
    ];

    protected $casts = [
        'before_json' => 'array',
        'after_json'  => 'array',
        'created_at'  => 'datetime',
    ];
}
