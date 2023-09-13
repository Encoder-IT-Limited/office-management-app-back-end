<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabelStatus extends Model
{
    use HasFactory;

    protected $table = 'label_statuses';
    protected $fillable = ['title', 'type', 'franchise', 'project_id', 'color'];

    public function tasks()
    {
        return $this->morphedByMany(Task::class, 'statusable');
    }

    public function projects()
    {
        return $this->morphedByMany(Project::class, 'statusable');
    }

    public function scopeGetTaskDefaultStatus($query)
    {
        return $query->taskOnly()->statusOnly()->where('title', 'Initialize')->first();
    }

    public function scopeTaskStatus($query)
    {
        return $query->taskOnly()->statusOnly();
    }

    public function scopeTaskOnly($query)
    {
        return $query->where('franchise', 'task');
    }

    public function scopeProjectOnly($query)
    {
        return $query->where('franchise', 'project');
    }

    public function scopeStatusOnly($query)
    {
        return $query->where('type', 'status');
    }

    public function scopeLabelOnly($query)
    {
        return $query->where('type', 'label');
    }

    public function scopeByProject($query, $project_id)
    {
        return $query->where('project_id', $project_id);
    }

    public function scopeByTitle($query, $title)
    {
        return $query->where('title', $title);
    }
}
