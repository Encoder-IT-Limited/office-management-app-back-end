<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'leaves';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'message',
        'reason',
        'accepted_start_date',
        'accepted_end_date',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }
}
