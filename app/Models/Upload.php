<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Upload extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'uploads';
    // protected $primaryKey = 'id';

    protected $fillable = [
        'path',
        'uploadable_id',
        'uploadable_type'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([...self::getFillable()])
            ->logOnlyDirty();
        // Chain fluent methods for configuration options
    }

    public function uploadable()
    {
        return $this->morphTo();
    }
}
