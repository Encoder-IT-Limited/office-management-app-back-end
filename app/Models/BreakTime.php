<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BreakTime extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = ['start_time', 'end_time', 'reason', 'employee_id'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
//        'duration' => 'datetime:"H:i"',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    protected $appends = ['duration'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function getDurationAttribute()
    {
        $breakEnd = Carbon::parse($this->end_time) ?? Carbon::now();
        return gmdate("H:i", Carbon::parse($this->start_time)->diffInSeconds($breakEnd));
    }

    public function scopeBreakFilter($query, $year, $month, $date)
    {
        return $query->whereYear('start_time', '=', $year)
            ->whereMonth('start_time', '=', $month)
            ->whereDay('start_time', '=', $date);
    }

//    public function getStartTimeAttribute($value)
//    {
//        return Carbon::parse($value)->format('Y-m-d H:i:s');
//    }
//
//    public function getEndTimeAttribute($value)
//    {
//        return Carbon::parse($value)->format('Y-m-d H:i:s');
//    }
}
