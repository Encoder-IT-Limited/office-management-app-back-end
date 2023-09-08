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
        'status',
        'delay_time'
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
        return $this->hasMany(EmployeeNote::class, 'user_id');
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
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class, 'employee_id')->whereDate('check_in', Carbon::now());
    }

    public function scopeDelaysCount($query, $year, $month)
    {
        return $query->withCount(['attendances AS delay_count' => function ($attendance) use ($year, $month) {
            return $attendance->whereYear('check_in', '=',  $year)->whereMonth('check_in', '=', $month)->delay();
        }]);
    }

    public function getUserRoleAttribute()
    {
        return $this->roles;
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'employee_id');
    }

    public function getBreakDurationAttribute()
    {
        return $this->breakTimes->reduce(function ($total, $break) {
            return $total + Carbon::parse($break->start_time)->diffInSeconds($break->end_time);
        }, 0);
    }
}
