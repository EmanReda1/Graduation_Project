<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'projec_name',
        'department',
        'status',
        'place',
        'shelf_no',
        'supervisor',
        'project_date'
    ];
}
