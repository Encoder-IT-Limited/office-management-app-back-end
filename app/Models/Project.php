<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Fidum\EloquentMorphToOne\MorphToOne;
use Fidum\EloquentMorphToOne\HasMorphToOne;
use Illuminate\Support\Facades\Auth;

class Project extends Model
{
    use HasFactory, HasMorphToOne, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'budget',
        'start_date',
        'end_date',
        'message',
        'client_id',
        'status_id'
    ];

    protected $cast = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_kpi_filled' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'project_id');
    }

    public function status(): MorphToOne
    {
        return $this->morphToOne(LabelStatus::class, 'statusable')->where(['label_statuses.franchise' => 'project', 'label_statuses.type' => 'status'])->withPivot(['color', 'label_status_id']);
    }

    public function taskStatuses()
    {
        return $this->hasMany(LabelStatus::class, 'project_id');
    }

    public function labels()
    {
        return $this->morphToMany(LabelStatus::class, 'statusable')->where('label_statuses.type', 'label')->withPivot(['color', 'label_status_id']);
    }

    public function scopeWithData($queries, ...$data)
    {
        if (count($data) > 0) return $queries->with($data);
        return $queries->with([
            'client', 'labels', 'status', 'tasks' => function ($data) {
                $data->filterAccessable()->with('assignee', 'status', 'labels');
            }, 'teams' => function ($data) {
                $data->with('teamUsers');
            },
        ]);
    }

    public function scopeFilterdByPermissions($queries)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->hasPermission('read-client-project')) {
            $queries->where('client_id', $user->id);
        } else if ($user->hasPermission('read-my-project')) {
            $queries->whereHas('teams', function ($teamQ) {
                $teamQ->whereHas('teamUsers', function ($userQ) {
                    $userQ->where('users.id', Auth::id());
                });
            });
        }

        return $queries;
    }
}
