<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{

    use HasFactory, SoftDeletes;

    protected $table = 'reminders';

    protected $fillable = [
        'project_id',
        'user_id',
        'client_id',
        'date',
        'time',
        'reminder_at',
        'description',
        'status'
    ];

    protected $cast = [
        'time' => 'time',
        'date' => 'date:d/m/Y',
        'status' => 'boolean'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clients()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function projects()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
