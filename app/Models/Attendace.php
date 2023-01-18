<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
