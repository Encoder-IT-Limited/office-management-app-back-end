<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

class Attendance extends Model
{
    use HasFactory;

    private $checkedIn = '08:30:00';

    protected $fillable = [
        'employee_id',
        'check_in',
        'check_out',
        'date',
        'status',
        'delay_time'
    ];

    protected $casts = [
        'check_in'   => 'datetime',
        'check_out'  => 'datetime',
        'delay_time'  => 'datetime:H:i:s',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
        'is_delay'   => 'boolean',
        'duration' => 'datetime:H:i',
        'break_time' => 'datetime:H:i',
    ];

    protected $appends = ['duration', 'is_delay', 'break_time'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function getDurationAttribute()
    {
        $checkOut = Carbon::parse($this->check_out) ?? Carbon::now();
        return gmdate("H:i", Carbon::parse($this->check_in)->diffInSeconds($checkOut));
    }

    public function scopeDelay($query)
    {
        return $query->whereRaw('attendances.check_in > attendances.delay_time');
    }

    public function getIsDelayAttribute()
    {
        $checkedInTime = $this->delay_time ?? $this->checkedIn;
        return Carbon::parse($this->check_in) > Carbon::parse($checkedInTime);
    }

    public function getBreakTimeAttribute()
    {
        $breakTimes = BreakTime::where('employee_id', $this->employee_id)->whereDate('start_time', Carbon::now())->get();
        $breakTimeDuration = 0;
        foreach ($breakTimes as $break) {
            $breakEnd = Carbon::parse($break->end_time) ?? Carbon::now();
            $breakTimeDuration += Carbon::parse($break->start_time)->diffInSeconds($breakEnd);
        }
        return gmdate("H:i", $breakTimeDuration);
    }
}
