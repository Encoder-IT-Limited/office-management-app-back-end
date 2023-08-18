<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = ['start_time', 'end_time', 'reason', 'employee_id'];

    protected $casts = [
        'start_time'   => 'datetime',
        'end_time'  => 'datetime',
        'duration' => 'datetime:"H:i"',
    ];

    protected $appends = ['duration'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function getDurationAttribute()
    {
        $breakEnd = Carbon::parse($this->end_time) ?? Carbon::now();
        return gmdate("H:i",  Carbon::parse($this->start_time)->diffInSeconds($breakEnd));
    }

    public function scopeBreak($query, $year, $month, $date)
    {
        return $query->whereYear('start_time', '=', $year)
            ->whereMonth('start_time', '=', $month)
            ->whereYear('start_time', '=', $date);
    }
}
