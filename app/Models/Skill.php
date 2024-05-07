<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use HasFactory;

    protected $table = 'skills';

    protected $fillable = [
        'name',
        'slug'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'skill_user', 'skill_id', 'user_id')->withTimestamps();;
    }
}
