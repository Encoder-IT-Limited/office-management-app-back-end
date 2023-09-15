<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teams';

    protected $fillable = ['title', 'status', 'project_id'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function teamUsers()
    {
        return $this->belongsToMany(User::class, 'team_user', 'user_id', 'team_id');
    }

    public function scopeFilter($queries, $request)
    {
        return $queries->when($request->has('title'), function ($query) use ($request) {
            $query->where('title', $request->title);
        })->when($request->has('project_id'), function ($query) use ($request) {
            $query->where('project_id', $request->project_id);
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('FilterdByPermissions', function ($queries) {
            $user = User::findOrFail(Auth::id());
            $queries->with('project', 'teamUsers');
            if ($user->hasPermission('read-client-project')) {
                return $queries->whereHas('project', function ($projectQ) use ($user) {
                    $projectQ->where('client_id', $user->id);
                });
            } else if ($user->hasPermission('read-my-project')) {
                return $queries->whereHas('teamUsers', function ($userQ) use ($user) {
                    return $userQ->where('id', $user->id);
                });
            }
        });
    }
}
