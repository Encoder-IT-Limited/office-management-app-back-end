<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reminder extends Model
{

    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'reminders';

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'remind_at',
        'message',
        'status',
    ];

    protected $casts = [
        'message' => 'boolean',
        'status' => 'boolean',
        'remind_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clients()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
