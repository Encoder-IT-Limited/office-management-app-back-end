<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Team extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'teams';

    protected $fillable = ['title', 'status', 'project_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function teamUsers()
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id');
    }

    public function scopeFilter($queries, $request)
    {
        return $queries->when($request->has('title'), function ($query) use ($request) {
            $query->where('title', $request->title);
        })->when($request->has('project_id'), function ($query) use ($request) {
            $query->where('project_id', $request->project_id);
        });
    }

    public function scopeWithData($queries)
    {
        return $queries->with('project', 'teamUsers');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('FilteredByPermissions', function ($queries) {
            $user = User::findOrFail(Auth::id());

            if ($user->hasPermission('read-client-project')) {
                return $queries->whereHas('project', function ($projectQ) use ($user) {
                    $projectQ->where('client_id', $user->id);
                });
            } else if ($user->hasPermission('read-my-project')) {
                return $queries->whereHas('teamUsers', function ($userQ) use ($user) {
                    return $userQ->where('users.id', $user->id);
                });
            }
        });
    }
}
