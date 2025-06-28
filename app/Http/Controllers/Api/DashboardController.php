<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Visit;
use App\Models\Favourite;
use App\Models\Review;
use App\Models\Book;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get student dashboard data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            // Get student statistics
            $studentStats = $this->getStudentStatistics($student->student_id);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($student->student_id);
            
            // Get borrowed books
            $borrowedBooks = $this->getBorrowedBooks($student->student_id);
            
            // Get pending requests
            $pendingRequests = $this->getPendingRequests($student->student_id);
            
            // Get favorite books count
            $favoriteBooksCount = Favourite::where('student_id', $student->student_id)->count();
            
            // Get reviews written count
            $reviewsWritten = Review::where('student_id', $student->student_id)->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'student_stats' => $studentStats,
                    'recent_activities' => $recentActivities,
                    'borrowed_books' => $borrowedBooks,
                    'pending_requests' => $pendingRequests,
                    'favorite_books_count' => $favoriteBooksCount,
                    'reviews_written' => $reviewsWritten
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب بيانات لوحة التحكم'
            ], 500);
        }
    }

    /**
     * Get student statistics
     *
     * @param int $studentId
     * @return array
     */
    private function getStudentStatistics($studentId)
    {
        $totalBookRequests = BookRequest::where('student_id', $studentId)->count();
        $approvedRequests = BookRequest::where('student_id', $studentId)
            ->where('status', 'approved')->count();
        $pendingRequests = BookRequest::where('student_id', $studentId)
            ->where('status', 'pending')->count();
        $rejectedRequests = BookRequest::where('student_id', $studentId)
            ->where('status', 'rejected')->count();

        $totalVisits = Visit::where('student_id', $studentId)->count();
        $thisMonthVisits = Visit::where('student_id', $studentId)
            ->whereMonth('visit_time', Carbon::now()->month)
            ->whereYear('visit_time', Carbon::now()->year)
            ->count();

        return [
            'total_book_requests' => $totalBookRequests,
            'approved_requests' => $approvedRequests,
            'pending_requests' => $pendingRequests,
            'rejected_requests' => $rejectedRequests,
            'total_visits' => $totalVisits,
            'this_month_visits' => $thisMonthVisits
        ];
    }

    /**
     * Get recent activities
     *
     * @param int $studentId
     * @return array
     */
    private function getRecentActivities($studentId)
    {
        $activities = [];

        // Recent book requests
        $recentRequests = BookRequest::where('student_id', $studentId)
            ->with('book:book_id,book_name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentRequests as $request) {
            $activities[] = [
                'type' => 'book_request',
                'description' => 'طلب ' . ($request->type == 'reading' ? 'قراءة' : 'استعارة') . ' كتاب: ' . $request->book->book_name,
                'date' => $request->created_at,
                'status' => $request->status,
                'icon' => 'book'
            ];
        }

        // Recent visits
        $recentVisits = Visit::where('student_id', $studentId)
            ->orderBy('visit_time', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentVisits as $visit) {
            $activities[] = [
                'type' => 'visit',
                'description' => 'زيارة المكتبة',
                'date' => $visit->visit_time,
                'status' => 'completed',
                'icon' => 'location'
            ];
        }

        // Recent reviews
        $recentReviews = Review::where('student_id', $studentId)
            ->with('book:book_id,book_name')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentReviews as $review) {
            $activities[] = [
                'type' => 'review',
                'description' => 'مراجعة كتاب: ' . $review->book->book_name,
                'date' => $review->created_at,
                'status' => 'completed',
                'icon' => 'star',
                'rating' => $review->rating
            ];
        }

        // Sort activities by date
        usort($activities, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get borrowed books
     *
     * @param int $studentId
     * @return array
     */
    private function getBorrowedBooks($studentId)
    {
        $borrowedBooks = BookRequest::where('student_id', $studentId)
            ->where('type', 'borrowing')
            ->where('status', 'approved')
            ->with(['book:book_id,book_name,author,image,status'])
            ->whereHas('book', function($query) {
                $query->where('status', 'borrowed');
            })
            ->orderBy('date_of_request', 'desc')
            ->get();

        return $borrowedBooks->map(function($request) {
            $borrowDate = $request->date_of_request;
            $dueDate = $borrowDate->copy()->addDays(14); // Assuming 14 days borrowing period
            $daysRemaining = now()->diffInDays($dueDate, false);
            
            return [
                'request_id' => $request->request_id,
                'book_id' => $request->book->book_id,
                'book_name' => $request->book->book_name,
                'author' => $request->book->author,
                'image' => $request->book->image ? asset($request->book->image) : null,
                'borrowed_date' => $request->date_of_request,
                'due_date' => $dueDate,
                'days_remaining' => $daysRemaining,
                'is_overdue' => $daysRemaining < 0,
                'status' => $daysRemaining < 0 ? 'overdue' : ($daysRemaining <= 3 ? 'due_soon' : 'active')
            ];
        })->toArray();
    }

    /**
     * Get pending requests
     *
     * @param int $studentId
     * @return array
     */
    private function getPendingRequests($studentId)
    {
        return BookRequest::where('student_id', $studentId)
            ->where('status', 'pending')
            ->with(['book:book_id,book_name,author,image'])
            ->orderBy('date_of_request', 'desc')
            ->get()
            ->map(function($request) {
                return [
                    'request_id' => $request->request_id,
                    'book' => [
                        'book_id' => $request->book->book_id,
                        'book_name' => $request->book->book_name,
                        'author' => $request->book->author,
                        'image' => $request->book->image ? asset($request->book->image) : null
                    ],
                    'type' => $request->type,
                    'date_of_request' => $request->date_of_request,
                    'days_waiting' => now()->diffInDays($request->date_of_request)
                ];
            })
            ->toArray();
    }

    /**
     * Get quick statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStats()
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $stats = [
                'pending_requests' => BookRequest::where('student_id', $student->student_id)
                    ->where('status', 'pending')->count(),
                'borrowed_books' => BookRequest::where('student_id', $student->student_id)
                    ->where('type', 'borrowing')
                    ->where('status', 'approved')
                    ->whereHas('book', function($query) {
                        $query->where('status', 'borrowed');
                    })->count(),
                'favorite_books' => Favourite::where('student_id', $student->student_id)->count(),
                'this_month_visits' => Visit::where('student_id', $student->student_id)
                    ->whereMonth('visit_time', Carbon::now()->month)
                    ->whereYear('visit_time', Carbon::now()->year)
                    ->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب الإحصائيات السريعة'
            ], 500);
        }
    }

    /**
     * Get library activity summary
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activitySummary()
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            // Monthly activity for the past 6 months
            $monthlyActivity = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                
                $requests = BookRequest::where('student_id', $student->student_id)
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count();
                    
                $visits = Visit::where('student_id', $student->student_id)
                    ->whereMonth('visit_time', $month->month)
                    ->whereYear('visit_time', $month->year)
                    ->count();
                    
                $monthlyActivity[] = [
                    'month' => $month->format('M Y'),
                    'requests' => $requests,
                    'visits' => $visits,
                    'total_activity' => $requests + $visits
                ];
            }

            // Request types distribution
            $requestTypes = BookRequest::where('student_id', $student->student_id)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->map(function($item) {
                    return [
                        'type' => $item->type,
                        'count' => $item->count
                    ];
                });

            // Request status distribution
            $requestStatus = BookRequest::where('student_id', $student->student_id)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status,
                        'count' => $item->count
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'monthly_activity' => $monthlyActivity,
                    'request_types' => $requestTypes,
                    'request_status' => $requestStatus
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب ملخص النشاط'
            ], 500);
        }
    }

    /**
     * Get overdue books alert
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function overdueAlert()
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();
            
            $borrowedBooks = BookRequest::where('student_id', $student->student_id)
                ->where('type', 'borrowing')
                ->where('status', 'approved')
                ->with(['book:book_id,book_name,author'])
                ->whereHas('book', function($query) {
                    $query->where('status', 'borrowed');
                })
                ->get();

            $overdueBooks = [];
            $dueSoonBooks = [];

            foreach ($borrowedBooks as $request) {
                $borrowDate = $request->date_of_request;
                $dueDate = $borrowDate->copy()->addDays(14);
                $daysRemaining = now()->diffInDays($dueDate, false);
                
                $bookData = [
                    'request_id' => $request->request_id,
                    'book_name' => $request->book->book_name,
                    'author' => $request->book->author,
                    'due_date' => $dueDate,
                    'days_remaining' => $daysRemaining
                ];
                
                if ($daysRemaining < 0) {
                    $overdueBooks[] = $bookData;
                } elseif ($daysRemaining <= 3) {
                    $dueSoonBooks[] = $bookData;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'overdue_books' => $overdueBooks,
                    'due_soon_books' => $dueSoonBooks,
                    'has_alerts' => count($overdueBooks) > 0 || count($dueSoonBooks) > 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب تنبيهات الكتب المتأخرة'
            ], 500);
        }
    }
}

