<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'teams';

    protected $fillable = ['title', 'status', 'project_id'];

    public function project(){
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function teamUsers(){
        return $this->belongsToMany(User::class, 'team_user', 'user_id', 'team_id');
    }
}
