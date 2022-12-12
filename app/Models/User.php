<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'designation',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'skill_user', 'user_id', 'skill_id')->withTimestamps();;
    }

    public function employeeNotes()
    {
        return $this->hasMany(EmploteeNote::class, 'user_id');
    }

    public function projectTask()
    {
        return $this->hasMany(ProjectTask::class, 'developer_id');
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    public function getUserRoleAttribute()
    {
        return $this->roles;
    }
}
