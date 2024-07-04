<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Leave extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'leaves';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'reason',
        'start_date',
        'end_date',
        'accepted_start_date',
        'accepted_end_date',
        'user_id',
        'status',
        'accepted_by',
        'last_updated_by',
    ];

    protected $appends = ['message',];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    public function getMessageAttribute()
    {
        return $this->status ?? '';
    }
}
