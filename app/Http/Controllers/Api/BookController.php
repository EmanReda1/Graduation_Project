<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Favourite;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class BookController extends Controller
{
    /**
     * Get list of books with search and filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $query = Book::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->has('department')) {
            $query->where('department', $request->get('department'));
        }
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('book_name', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->paginate($perPage);

        try {
            $student = JWTAuth::parseToken()->authenticate();
            $booksData = $books->getCollection()->map(function($book) use ($student) {
                return [
                    'book_id' => $book->book_id,
                    'book_name' => $book->book_name,
                    'author' => $book->author,
                    'department' => $book->department,
                    'status' => $book->status,
                    'image' => $book->image ? asset($book->image) : null,
                    'is_favorited' => $this->isBookFavorited($book->book_id, $student->student_id),
                    'average_rating' => $this->getAverageRating($book->book_id),
                    'reviews_count' => $this->getReviewsCount($book->book_id)
                ];
            });
        } catch (\Exception $e) {
            // If JWTAuth fails (e.g., no token), return books without favorite status
            $booksData = $books->getCollection()->map(function($book) {
                return [
                    'book_id' => $book->book_id,
                    'book_name' => $book->book_name,
                    'author' => $book->author,
                    'department' => $book->department,
                    'status' => $book->status,
                    'image' => $book->image ? asset($book->image) : null,
                    'is_favorited' => false, // Default to false if user is not authenticated
                    'average_rating' => $this->getAverageRating($book->book_id),
                    'reviews_count' => $this->getReviewsCount($book->book_id)
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'books' => $booksData,
                'pagination' => [
                    'current_page' => $books->currentPage(),
                    'total_pages' => $books->lastPage(),
                    'total_items' => $books->total(),
                    'per_page' => $books->perPage()
                ]
            ]
        ]);
    }

    /**
     * Get book details
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
         try {
            // Attempt to get authenticated student for favorited status, but don\'t require it
            $student = null;
            try {
                $student = JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                // Token not provided or invalid, continue without student context
            }

            //$student = JWTAuth::parseToken()->authenticate();

            $book = Book::find($id);

            if (!$book) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود'
                ], 404);
            }

            // Get reviews with student names
            $reviews = Review::where('book_id', $id)
                ->join('students', 'reviews.student_id', '=', 'students.student_id')
                ->select('reviews.*', 'students.fullname as student_name')
                ->orderBy('reviews.created_at', 'desc')
                ->get()
                ->map(function($review) {
                    return [
                        'review_id' => $review->review_id,
                        'student_name' => $review->student_name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'book_id' => $book->book_id,
                    'book_name' => $book->book_name,
                    'author' => $book->author,
                    'isbn_no' => $book->isbn_no,
                    'book_no' => $book->book_no,
                    'price' => $book->price,
                    'source' => $book->source,
                    'summary' => $book->summary,
                    'department' => $book->department,
                    'status' => $book->status,
                    'place' => $book->place,
                    'shelf_no' => $book->shelf_no,
                    'size' => $book->size,
                    'release_date' => $book->release_date,
                    'library_date' => $book->library_date,
                    'image' => $book->image ? asset($book->image) : null,
                    'is_favorited' => $this->isBookFavorited($book->book_id, $student->student_id),
                    'average_rating' => $this->getAverageRating($book->book_id),
                    'reviews_count' => $this->getReviewsCount($book->book_id),
                    'reviews' => $reviews
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب تفاصيل الكتاب'
            ], 500);
        }
    }

    /**
     * Get available books only
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        $request->merge(['status' => 'available']);
        return $this->index($request);
    }

    /**
     * Get books by department
     *
     * @param Request $request
     * @param string $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function byDepartment(Request $request, $department)
    {
        $request->merge(['department' => $department]);
        return $this->index($request);
    }

    /**
     * Search books
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'يجب أن يكون النص المراد البحث عنه أكثر من حرفين',
                'errors' => $validator->errors()
            ], 422);
        }

        $request->merge(['search' => $request->query]);
        return $this->index($request);
    }

    /**
     * Get popular books (most requested)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $perPage = $request->get('per_page', 10);

            $books = Book::select('books.*')
                ->join('requests', 'books.book_id', '=', 'requests.book_id')
                ->selectRaw('books.*, COUNT(requests.request_id) as request_count')
                ->groupBy('books.book_id')
                ->orderBy('request_count', 'desc')
                ->paginate($perPage);

            $booksData = $books->getCollection()->map(function($book) use ($student) {
                return [
                    'book_id' => $book->book_id,
                    'book_name' => $book->book_name,
                    'author' => $book->author,
                    'department' => $book->department,
                    'status' => $book->status,
                    'image' => $book->image ? asset($book->image) : null,
                    'is_favorited' => $this->isBookFavorited($book->book_id, $student->student_id),
                    'average_rating' => $this->getAverageRating($book->book_id),
                    'request_count' => $book->request_count ?? 0
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'books' => $booksData,
                    'pagination' => [
                        'current_page' => $books->currentPage(),
                        'total_pages' => $books->lastPage(),
                        'total_items' => $books->total(),
                        'per_page' => $books->perPage()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الكتب الشائعة'
            ], 500);
        }
    }

    /**
     * Get recently added books
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $perPage = $request->get('per_page', 10);

            $books = Book::orderBy('created_at', 'desc')->paginate($perPage);

            $booksData = $books->getCollection()->map(function($book) use ($student) {
                return [
                    'book_id' => $book->book_id,
                    'book_name' => $book->book_name,
                    'author' => $book->author,
                    'department' => $book->department,
                    'status' => $book->status,
                    'image' => $book->image ? asset($book->image) : null,
                    'is_favorited' => $this->isBookFavorited($book->book_id, $student->student_id),
                    'average_rating' => $this->getAverageRating($book->book_id),
                    'created_at' => $book->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'books' => $booksData,
                    'pagination' => [
                        'current_page' => $books->currentPage(),
                        'total_pages' => $books->lastPage(),
                        'total_items' => $books->total(),
                        'per_page' => $books->perPage()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الكتب الحديثة'
            ], 500);
        }
    }

    /**
     * Check if book is favorited by student
     *
     * @param int $bookId
     * @param int $studentId
     * @return bool
     */
    private function isBookFavorited($bookId, $studentId)
    {
        return Favourite::where('book_id', $bookId)
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * Get average rating for book
     *
     * @param int $bookId
     * @return float
     */
    private function getAverageRating($bookId)
    {
        return Review::where('book_id', $bookId)->avg('rating') ?? 0;
    }

    /**
     * Get reviews count for book
     *
     * @param int $bookId
     * @return int
     */
    private function getReviewsCount($bookId)
    {
        return Review::where('book_id', $bookId)->count();
    }
}

