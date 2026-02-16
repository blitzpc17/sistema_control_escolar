<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';

    protected $fillable = [
        'user_id','module_id',
        'can_view','can_create','can_update','can_delete'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
