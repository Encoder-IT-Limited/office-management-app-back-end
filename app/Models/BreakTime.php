<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = ['start_time', 'end_time', 'reason', 'employee_id'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
