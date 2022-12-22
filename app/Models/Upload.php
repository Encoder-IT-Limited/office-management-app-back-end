<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'uploads';
    // protected $primaryKey = 'id';

    protected $fillable = [
        'path',
        'uploadable_id',
        'uploadable_type'
    ];

    public function uploadable()
    {
        return $this->morphTo();
    }
}
