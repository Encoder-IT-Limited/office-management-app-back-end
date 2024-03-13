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
        'user_id',
        'project_id',
        'title',
        'description',
        'remind_at',
        'message',
    ];

    protected $cast = [
        'remind_at' => 'date:d/m/Y time:H:i:s',
        'message' => 'boolean'
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
