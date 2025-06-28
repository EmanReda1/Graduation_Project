<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Student extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'student_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'phone_no',
        'level',
        'department',
        'university_code',
        'borrow_docs',
        'image'
    ];

    /**
     * The attributes that should be hidden for serialization (Privacy Protection).
     *
     * @var array
     */
  protected $hidden = [
    // 'password',
    // 'email',
    // 'phone_no',
    // 'remember_token',
];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'level' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get public attributes only (for librarian access).
     *
     * @return array
     */
    public function getPublicAttributes()
    {
        return [
            'student_id' => $this->student_id,
            'username' => $this->username,
            'fullname' => $this->fullname,
            'level' => $this->level,
            'department' => $this->department,
            'university_code' => $this->university_code,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get the visits for the student.
     */
    public function visits()
    {
        return $this->hasMany(Visit::class, 'student_id', 'student_id');
    }

    /**
     * Get the book requests for the student.
     */
    public function bookRequests()
    {
        return $this->hasMany(BookRequest::class, 'student_id', 'student_id');
    }

    /**
     * Get the reading records for the student.
     */
    public function readingRecords()
    {
        return $this->hasManyThrough(
            RetrieveRequest::class,
            BookRequest::class,
            'student_id', // Foreign key on BookRequest table
            'request_id', // Foreign key on RetrieveRequest table
            'student_id', // Local key on Student table
            'request_id'  // Local key on BookRequest table
        )->whereHas('request', function($query) {
            $query->where('type', 'reading');
        });
    }

    /**
     * Get the borrowing records for the student.
     */
    public function borrowingRecords()
    {
        return $this->hasManyThrough(
            RetrieveRequest::class,
            BookRequest::class,
            'student_id', // Foreign key on BookRequest table
            'request_id', // Foreign key on RetrieveRequest table
            'student_id', // Local key on Student table
            'request_id'  // Local key on BookRequest table
        )->whereHas('request', function($query) {
            $query->where('type', 'borrowing');
        });
    }

    /**
     * Get the projects for the student.
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'student_id', 'student_id');
    }

    /**
     * Get the exams for the student.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'student_id', 'student_id');
    }

    /**
     * Get the favorites for the student.
     */
    public function favorites()
    {
        return $this->hasMany(Favourite::class, 'student_id', 'student_id');
    }

    /**
     * Scope a query to filter students by level.
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
     * Scope a query to filter students by department.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $department
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope a query to search students (public fields only).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($query) use ($search) {
            $query->where('username', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%")
                  ->orWhere('university_code', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to get only public information.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublicInfo($query)
    {
        return $query->select([
            'student_id',
            'username',
            'fullname',
            'level',
            'department',
            'university_code',
            'image',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * Get student's library activity summary.
     *
     * @return array
     */
    public function getLibraryActivitySummary()
    {
        return [
            'total_book_requests' => $this->bookRequests()->count(),
            'approved_requests' => $this->bookRequests()->where('status', 'approved')->count(),
            'pending_requests' => $this->bookRequests()->where('status', 'pending')->count(),
            'total_visits' => $this->visits()->count(),
            'recent_visits' => $this->visits()->orderBy('visit_date', 'desc')->limit(5)->get(),
            'total_projects' => $this->projects()->count(),
            'completed_projects' => $this->projects()->whereNotNull('grade')->count(),
        ];
    }

    /**
     * Check if student has any library activity.
     *
     * @return bool
     */
    public function hasLibraryActivity()
    {
        return $this->bookRequests()->exists() ||
               $this->visits()->exists() ||
               $this->projects()->exists();
    }
}


