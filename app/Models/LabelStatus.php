<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabelStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'label_statuses';

    public function tasks()
    {
        return $this->morphedByMany(Task::class, 'taggable');
    }

    public function projects()
    {
        return $this->morphedByMany(Project::class, 'taggable');
    }
}
