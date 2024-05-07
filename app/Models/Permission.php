<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'slug',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id')->withTimestamps();;
    }
}
