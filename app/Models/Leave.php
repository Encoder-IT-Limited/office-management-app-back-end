<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $table = 'leaves';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'status',
        'reason',
        'accepted_start_date',
        'accepted_end_date',
        'user_id'
    ];
}
