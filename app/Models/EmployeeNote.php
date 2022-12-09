<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeNote extends Model
{
    use HasFactory;

    protected $table = 'employee_notes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'note',
        'user_id',
        'is_positive'
    ];
}
