<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetrieveRequest extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'retrieve_requests';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'retrieve_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request_id',
        'request_date',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request_date' => 'datetime',
    ];

    /**
     * Get the request that owns the retrieve request.
     */
    public function request()
    {
        return $this->belongsTo(BookRequest::class, 'request_id', 'request_id');
    }

    /**
     * Get the student through the request.
     */
    public function student()
    {
        return $this->hasOneThrough(
            Student::class,
            BookRequest::class,
            'request_id', // Foreign key on BookRequest table
            'student_id', // Foreign key on Student table
            'request_id', // Local key on RetrieveRequest table
            'student_id'  // Local key on BookRequest table
        );
    }

    /**
     * Get the book through the request.
     */
    public function book()
    {
        return $this->hasOneThrough(
            Book::class,
            BookRequest::class,
            'request_id', // Foreign key on BookRequest table
            'book_id',    // Foreign key on Book table
            'request_id', // Local key on RetrieveRequest table
            'book_id'     // Local key on BookRequest table
        );
    }

    /**
     * Scope a query to only include reading records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReading($query)
    {
        return $query->whereHas('request', function($q) {
            $q->where('type', 'reading');
        });
    }

    /**
     * Scope a query to only include borrowing records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBorrowing($query)
    {
        return $query->whereHas('request', function($q) {
            $q->where('type', 'borrowing');
        });
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
        return $query->whereHas('request', function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
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
        return $query->whereHas('request', function($q) use ($bookId) {
            $q->where('book_id', $bookId);
        });
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
     * Scope a query to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereDate('request_date', '>=', $startDate)
                     ->whereDate('request_date', '<=', $endDate);
    }

    /**
     * Scope a query to search retrieve requests.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($query) use ($search) {
            $query->whereHas('student', function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%");
            })->orWhereHas('book', function($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('ISBN_No', 'like', "%{$search}%");
            });
        });
    }
}
