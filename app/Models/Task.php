<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Fidum\EloquentMorphToOne\MorphToOne;
use Fidum\EloquentMorphToOne\HasMorphToOne;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory, HasMorphToOne, SoftDeletes;


    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'reference',
        'project_id',
        'author_id',
        'assignee_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function labels()
    {
        return $this->morphToMany(LabelStatus::class, 'statusable')->where('label_statuses.type', 'label')->withPivot(['color', 'label_status_id']);
    }

    public function status(): MorphToOne
    {
        return $this->morphToOne(LabelStatus::class, 'statusable')->where(['label_statuses.franchise' => 'task', 'label_statuses.type' => 'status'])->withPivot(['color', 'label_status_id']);
    }

    private $userId;
    public function scopeFilterAccessable($queries)
    {
        $this->userId = Auth::id();
        $user = User::findOrFail($this->userId);

        if ($user->hasPermission('read-task')) {
            if ($user->hasRole('client')) {
                $queries->whereHas('project', function ($projectQ) {
                    $projectQ->where('client_id', $this->userId);
                });
            }
        } else if ($user->hasPermission('show-task')) {
            $queries->whereHas('project', function ($projectQ) {
                return $projectQ->whereHas('teams', function ($teamQ) {
                    return $teamQ->whereHas('teamUsers', function ($userQ) {
                        return $userQ->where('id', $this->userId);
                    });
                });
            })->orWhere(function ($query) {
                return $query->wehre('author_id', $this->userId)->orWhere('assignee_id', $this->userId);
            });
        }

        return $queries;
    }
}
