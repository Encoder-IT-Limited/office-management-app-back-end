<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'name',
        'budget',
        'start_date',
        'end_date',
        'status',
        'client_id',
        'is_kpi_filled'
    ];

    protected $cast = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_kpi_filled' => 'boolean'
    ];

    public function projectTasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    public function getTasksAttribute()
    {
        return $this->projectTasks()->get();
    }
}
