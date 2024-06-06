<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Fidum\EloquentMorphToOne\MorphToOne;
use Fidum\EloquentMorphToOne\HasMorphToOne;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, HasMorphToOne, SoftDeletes;
    use LogsActivity;

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


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')->withTimestamps();
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
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

    public function labels(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(LabelStatus::class, 'statusable')->where('label_statuses.type', 'label')->withPivot(['color', 'label_status_id']);
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectNote::class, 'project_id');
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reminder::class, 'project_id');
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

    public function scopeFilteredByPermissions($queries)
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
