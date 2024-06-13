<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NahidFerdous\Searchable\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BillableTime extends Model
{
    use HasFactory, SoftDeletes, Searchable;
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'project_id',
        'task_id',
        'task',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function getGivenTimeAttribute($value): array
    {
        if (!$value) return ['hours' => 0, 'minutes' => 0];
        if (str_contains($value, ':')) {
            $hours = explode(':', $value)[0];
            $minutes = explode(':', $value)[1];
            return [
                'hours' => $hours,
                'minutes' => $minutes,
            ];
        }
        return ['hours' => 0, 'minutes' => 0];
    }

    public function setGivenTimeAttribute($value): void
    {
        $this->attributes['given_time'] = $value['hours'] . ':' . $value['minutes'];
    }

    public function getTimeSpentAttribute($value): array
    {
        if (!$value) return ['hours' => 0, 'minutes' => 0];
        if (str_contains($value, ':')) {
            $hours = explode(':', $value)[0];
            $minutes = explode(':', $value)[1];
            return [
                'hours' => $hours,
                'minutes' => $minutes,
            ];
        }
        return ['hours' => 0, 'minutes' => 0];
    }

    public function setTimeSpentAttribute($value): void
    {
        $this->attributes['time_spent'] = $value['hours'] . ':' . $value['minutes'];
    }

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
