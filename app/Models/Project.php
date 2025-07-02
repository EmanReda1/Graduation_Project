<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects'; // Assuming your table name is 'projects'
    protected $primaryKey = 'project_id'; // Assuming your primary key is 'project_id'
    public $incrementing = true; // Assuming your primary key is auto-incrementing
    protected $keyType = 'int'; // Assuming your primary key is an integer

    // If you have timestamps (created_at, updated_at) and want Laravel to manage them
    public $timestamps = true;

    // If you want to allow mass assignment for certain fields
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
        // Add other fillable fields here
    ];

    // If you want to guard against mass assignment for all fields except some
    // protected $guarded = [];
}
