<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NahidFerdous\Searchable\Searchable;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use HasFactory, Searchable;
}
