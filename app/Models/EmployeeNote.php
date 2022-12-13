<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_notes';

    // protected $primaryKey = 'id';

    protected $fillable = [
        'note',
        'user_id',
        'is_positive'
    ];

    protected $casts = [
        'is_positive' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
