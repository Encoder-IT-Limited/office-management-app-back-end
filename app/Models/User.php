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
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasPermissionsTrait;
    use LogsActivity;

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

//    protected $appends = ['isCheckedIn'];
//
//    public function getIsCheckedInAttribute()
//    {
//        return $this->todayAttendance();
//    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

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

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function employeeNotes()
    {
        return $this->hasMany(EmployeeNote::class, 'user_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'client_id');
    }

    public function userTeams()
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id');
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\MorphMany
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

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserNote::class, 'user_id');
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'user_users', 'parent_user_id');
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'user_users', 'user_id');
    }

    public function myTodoTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function myOwnedTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'author_id');
    }

    public function scopeDelaysCount($query, $year, $month)
    {
        return $query->withCount(['attendances AS delay_count' => function ($attendance) use ($year, $month) {
            return $attendance->whereYear('check_in', '=', $year)->whereMonth('check_in', '=', $month)->delay();
        }]);
    }

    public function scopeFilterByRoles($query, ...$roles)
    {
        return $query->whereHas('roles', function ($roleQ) use ($roles) {
            return $roleQ->whereIn('slug', $roles);
        });
    }

    public function scopeOnlyAdmin($query)
    {
        return $query->filterByRoles('admin');
    }

    public function scopeOnlyDeveloper($query)
    {
        return $query->filterByRoles('developer');
    }

    public function scopeOnlyClient($query)
    {
        return $query->filterByRoles('client');
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

    public function scopeWithData($queries)
    {
        return $queries->with('children', 'roles', 'skills', 'userTeams', 'todayAttendance', 'uploads');
    }

    public function scopeFilteredByPermissions($queries)
    {
        $user = auth()->user();

        if ($user->hasPermission('read-client-user')) {
            $queries->whereHas('userTeams', function ($teamQ) use ($user) {
                $teamQ->whereHas('project', function ($projectQ) use ($user) {
                    $projectQ->where('projects.client_id', $user->id);
                });
            });
        } else if ($user->hasPermission('read-my-user')) {
            $queries->whereHas('userTeams', function ($teamQ) use ($user) {
                return $teamQ->whereHas('teamUsers', function ($userQ) use ($user) {
                    return $userQ->where('users.id', $user->id);
                });
            });
        }
        return $queries;
    }
}
