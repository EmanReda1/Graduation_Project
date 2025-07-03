<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $primaryKey = 'notification_id';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'librarian_id',
        'type',
        'message',
        'is_read',
        'date_time',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function librarian()
    {
        return $this->belongsTo(Librarian::class, 'librarian_id', 'librarian_id');
    }
}


