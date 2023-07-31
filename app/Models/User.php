<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasPermissionsTrait;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasPermissionsTrait;

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
        'message'
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->withTimestamps();;
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'skill_user', 'user_id', 'skill_id')->withPivot([
            'experience'
        ]);
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

    public function attendances()
    {
        return $this->hasMany(Attendace::class, 'employee_id');
    }

    public function scopeDelays($query, $year, $month)
    {
        return $query->with(['attendances' => function ($attendance) use ($year, $month) {
            return $attendance->whereTime('attendaces.check_in', '>', Carbon::parse('09:30:00'))
                ->whereYear('check_in', '=',  $year)->whereMonth('check_in', '=', $month);
        }]);
    }

    public function getUserRoleAttribute()
    {
        return $this->roles;
    }
}
