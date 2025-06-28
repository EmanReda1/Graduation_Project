<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Visit extends Model
{
    protected $table = 'visits';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'visit_id';

    // تفعيل timestamps (created_at و updated_at)
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'visit_time',
    ];

    protected $casts = [
        'visit_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Scope a query to filter visits by date.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('visit_time', $date);
    }

    /**
     * Scope a query to filter visits by student.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to get today's visits.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visit_time', today());
    }

    /**
     * Scope a query to get this month's visits.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visit_time', now()->month)
                    ->whereYear('visit_time', now()->year);
    }

    /**
     * Scope a query to get recent visits.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('visit_time', 'desc')->limit($limit);
    }
}

