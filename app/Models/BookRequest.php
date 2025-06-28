<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookRequest extends Model
{
    use HasFactory;

      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'requests';
    protected $fillable = [
        'student_id',
        'book_id',
        'project_id',
        'date_of_request',
        'notes',
        'status',
        'type',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'request_id';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_of_request' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the student that owns the request.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Get the book associated with the request.
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }

    /**
     * Get the project associated with the request.
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * Get the retrieve request associated with this request.
     */
    public function retrieveRequest()
    {
        return $this->hasOne(RetrieveRequest::class, 'request_id', 'request_id');
    }

    /**
     * Scope a query to only include reading requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReading($query)
    {
        return $query->where('type', 'reading');
    }

    /**
     * Scope a query to only include borrowing requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBorrowing($query)
    {
        return $query->where('type', 'borrowing');
    }

    /**
     * Scope a query to filter by student.
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
     * Scope a query to filter by book.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $bookId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    /**
     * Scope a query to filter by status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to search requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($query) use ($search) {
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%");
            })->orWhereHas('book', function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        });
    }
}
