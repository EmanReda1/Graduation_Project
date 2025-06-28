<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{

    protected $primaryKey = 'book_id';


    protected $fillable = [
        'book_name',
        'author',
        'isbn_no',
        'book_no',
        'price',
        'source',
        'summary',
        'department',
        'status',
        'place',
        'shelf_no',
        'size',
        'release_date',
        'library_date',
        'image',
    ];


    protected $casts = [
        'release_date' => 'date',
        'library_date' => 'date',
        'price' => 'decimal:2',
    ];


    /**
     * relation with requests
     **/
    public function requests()
    {
        return $this->hasMany(BookRequest::class, 'book_id', 'book_id');
    }

    /**
     * Alias for requests relation (for compatibility with StatisticsController)
     **/
    public function bookRequests()
    {
        return $this->hasMany(BookRequest::class, 'book_id', 'book_id');
    }

    /**
     * Get the students who favorited this book.
     */
    public function favorites()
    {
        return $this->hasMany(Favourite::class, 'book_id', 'book_id');
    }

    /**
     * Get the reviews for the book.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'book_id', 'book_id');
    }

    /** for the indirect relation between retrieve and book */
    public function retrieveRequests()
    {
        return $this->hasManyThrough(
            \App\Models\RetrieveRequest::class,   // النهاية
            \App\Models\BookRequest::class,       // الوسيط
            'book_id',       // المفتاح في BookRequest اللي بيربط بالـ Book
            'request_id',    // المفتاح في RetrieveRequest اللي بيربط بـ BookRequest
            'book_id',       // المفتاح في Book نفسه
            'request_id'     // المفتاح في BookRequest اللي بيربط بـ RetrieveRequest
        );
    }

    /**
     * Scope a query to filter books by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope a query to filter books by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to search books by name, author, or ISBN.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('book_name', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('isbn_no', 'like', "%{$search}%");
        });
    }
}

