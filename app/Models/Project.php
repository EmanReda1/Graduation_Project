<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'project_name',
        'department',
        'status',
        'place',
        'shelf_no',
        'supervisor',
        'project_date',
        'summary',
        'image',
        'pdf',
    ];

    // file paths
    public function getImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getPdfAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
