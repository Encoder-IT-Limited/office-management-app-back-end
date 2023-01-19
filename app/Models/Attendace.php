<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendace extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'check_in',
        'check_out',
        'date',
        'status'
    ];

    protected $appends = ['duration'];


    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function getDurationAttribute()
    {
        return Carbon::parse($this->check_in)->diff(Carbon::parse($this->check_out))->format('%h:%I');
    }
}
