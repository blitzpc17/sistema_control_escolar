<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'key','name','route','icon','parent_id','sort_order','is_menu','is_active'
    ];
}
