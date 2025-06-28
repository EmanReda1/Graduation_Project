<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exam extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'exam_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'course_name',
        'dept',
        'semester',
        'level',
        'doctor',
        'pdf',
        'year',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to filter exams by department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $department
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('dept', $department);
    }

    /**
     * Scope a query to filter exams by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter exams by semester.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $semester
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope a query to filter exams by level.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope a query to filter exams by year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope a query to search exams by course name, doctor, or type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('course_name', 'like', "%{$search}%")
                ->orWhere('doctor', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
        });
    }

    /**
     * Get the PDF file path attribute.
     *
     * @return string
     */
    public function getPdfPathAttribute()
    {
        return $this->pdf ? asset('storage/exams/' . $this->pdf) : null;
    }

    /**
     * Get the formatted year attribute.
     *
     * @return string
     */
    public function getFormattedYearAttribute()
    {
        return $this->year ? $this->year . ' - ' . ($this->year + 1) : '';
    }

    /**
     * Get the type in Arabic.
     *
     * @return string
     */
    public function getTypeInArabicAttribute()
    {
        $types = [
            'midterm' => 'امتحان نصف الفصل',
            'final' => 'امتحان نهائي',
            'quiz' => 'كويز',
            'assignment' => 'تكليف',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get the semester in Arabic.
     *
     * @return string
     */
    public function getSemesterInArabicAttribute()
    {
        $semesters = [
            'first' => 'الفصل الأول',
            'second' => 'الفصل الثاني',
            'summer' => 'الفصل الصيفي',
        ];

        return $semesters[$this->semester] ?? $this->semester;
    }
}

