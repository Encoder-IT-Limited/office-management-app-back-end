<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillableTime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'site',
        'date',
        'time_spent',
        'comment',
        'screenshot',
        'given_time',
        'is_freelancer',
    ];

    protected $dates = [
        'date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Task::class);
    }


}
