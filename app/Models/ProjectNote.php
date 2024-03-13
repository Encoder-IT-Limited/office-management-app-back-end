<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'note'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
