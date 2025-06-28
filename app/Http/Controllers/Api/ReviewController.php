<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    /**
     * Get reviews for a specific book
     *
     * @param Request $request
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $bookId)
    {
        try {
            // Check if book exists
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود'
                ], 404);
            }

            $perPage = $request->get('per_page', 10);
            
            $reviews = Review::where('book_id', $bookId)
                ->join('students', 'reviews.student_id', '=', 'students.student_id')
                ->select('reviews.*', 'students.fullname as student_name')
                ->orderBy('reviews.created_at', 'desc')
                ->paginate($perPage);

            $reviewsData = $reviews->getCollection()->map(function($review) {
                return [
                    'review_id' => $review->review_id,
                    'student_name' => $review->student_name,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at
                ];
            });

            // Calculate average rating
            $averageRating = Review::where('book_id', $bookId)->avg('rating') ?? 0;
            $totalReviews = Review::where('book_id', $bookId)->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'reviews' => $reviewsData,
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $totalReviews,
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'total_pages' => $reviews->lastPage(),
                        'total_items' => $reviews->total(),
                        'per_page' => $reviews->perPage(),
                        'has_next' => $reviews->hasMorePages(),
                        'has_previous' => $reviews->currentPage() > 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب المراجعات'
            ], 500);
        }
    }

    /**
     * Add a review for a book
     *
     * @param Request $request
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $bookId)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            // Check if book exists
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|min:10|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if student already reviewed this book
            $existingReview = Review::where('student_id', $student->student_id)
                ->where('book_id', $bookId)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لقد قمت بمراجعة هذا الكتاب من قبل'
                ], 400);
            }

            // Create the review
            $review = Review::create([
                'student_id' => $student->student_id,
                'book_id' => $bookId,
                'rating' => $request->rating,
                'comment' => $request->comment
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إضافة المراجعة بنجاح',
                'data' => [
                    'review_id' => $review->review_id,
                    'student_name' => $student->fullname,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إضافة المراجعة'
            ], 500);
        }
    }

    /**
     * Update a review
     *
     * @param Request $request
     * @param int $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $reviewId)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $review = Review::where('review_id', $reviewId)
                ->where('student_id', $student->student_id)
                ->first();

            if (!$review) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المراجعة غير موجودة أو غير مصرح لك بتعديلها'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'rating' => 'sometimes|required|integer|min:1|max:5',
                'comment' => 'sometimes|required|string|min:10|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            if ($request->has('rating')) {
                $updateData['rating'] = $request->rating;
            }
            if ($request->has('comment')) {
                $updateData['comment'] = $request->comment;
            }

            $review->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث المراجعة بنجاح',
                'data' => [
                    'review_id' => $review->review_id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'updated_at' => $review->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تحديث المراجعة'
            ], 500);
        }
    }

    /**
     * Delete a review
     *
     * @param int $reviewId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($reviewId)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $review = Review::where('review_id', $reviewId)
                ->where('student_id', $student->student_id)
                ->first();

            if (!$review) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المراجعة غير موجودة أو غير مصرح لك بحذفها'
                ], 404);
            }

            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف المراجعة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في حذف المراجعة'
            ], 500);
        }
    }

    /**
     * Get student's own reviews
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myReviews(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $perPage = $request->get('per_page', 10);
            
            $reviews = Review::where('student_id', $student->student_id)
                ->with(['book:book_id,book_name,author,image'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $reviewsData = $reviews->getCollection()->map(function($review) {
                return [
                    'review_id' => $review->review_id,
                    'book' => [
                        'book_id' => $review->book->book_id,
                        'book_name' => $review->book->book_name,
                        'author' => $review->book->author,
                        'image' => $review->book->image ? asset($review->book->image) : null
                    ],
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'reviews' => $reviewsData,
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'total_pages' => $reviews->lastPage(),
                        'total_items' => $reviews->total(),
                        'per_page' => $reviews->perPage(),
                        'has_next' => $reviews->hasMorePages(),
                        'has_previous' => $reviews->currentPage() > 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب مراجعاتك'
            ], 500);
        }
    }

    /**
     * Get review statistics for a book
     *
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics($bookId)
    {
        try {
            // Check if book exists
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود'
                ], 404);
            }

            $reviews = Review::where('book_id', $bookId);
            
            $totalReviews = $reviews->count();
            $averageRating = $reviews->avg('rating') ?? 0;
            
            // Rating distribution
            $ratingDistribution = [];
            for ($i = 1; $i <= 5; $i++) {
                $count = Review::where('book_id', $bookId)->where('rating', $i)->count();
                $ratingDistribution[$i] = [
                    'count' => $count,
                    'percentage' => $totalReviews > 0 ? round(($count / $totalReviews) * 100, 1) : 0
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'book_id' => $bookId,
                    'total_reviews' => $totalReviews,
                    'average_rating' => round($averageRating, 1),
                    'rating_distribution' => $ratingDistribution
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب إحصائيات المراجعات'
            ], 500);
        }
    }

    /**
     * Check if current student can review a book
     *
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function canReview($bookId)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            // Check if book exists
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود'
                ], 404);
            }

            // Check if student already reviewed this book
            $hasReviewed = Review::where('student_id', $student->student_id)
                ->where('book_id', $bookId)
                ->exists();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'book_id' => $bookId,
                    'can_review' => !$hasReviewed,
                    'has_reviewed' => $hasReviewed
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في التحقق من إمكانية المراجعة'
            ], 500);
        }
    }
}

