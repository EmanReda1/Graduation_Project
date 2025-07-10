<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Student;
use App\Models\Visit;
use App\Models\BookRequest;
use App\Models\RetrieveRequest;
use App\Models\Project;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Display the statistics dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Books statistics
        $totalBooks = Book::count();
        $availableBooks = Book::where('status', 'available')->count();
        $borrowedBooks = Book::where('status', 'borrowed')->count();

        // Students statistics
        $totalStudents = Student::count();
        $studentsWithLevel = Student::where('level', '>', 0)->count();

        // Visits statistics
        $totalVisits = Visit::count();
        $visitsToday = Visit::whereDate('visit_time', Carbon::today())->count();
        $visitsThisWeek = Visit::whereBetween('visit_time', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        $visitsThisMonth = Visit::whereMonth('visit_time', Carbon::now()->month)
            ->whereYear('visit_time', Carbon::now()->year)
            ->count();

        // Book Requests statistics (استخدام BookRequest Model)
        $totalBookRequests = BookRequest::count();
        $pendingBookRequests = BookRequest::whereHas('retrieveRequest', function ($query) {
            $query->where('status', 'pending');
        })->count();
        $approvedBookRequests = BookRequest::whereHas('retrieveRequest', function ($query) {
            $query->where('status', 'approved');
        })->count();

        // Reading vs Borrowing requests
        $readingRequests = BookRequest::where('type', 'reading')->count();
        $borrowingRequests = BookRequest::where('type', 'borrowing')->count();

        // Projects statistics
        $totalProjects = Project::count();
        $availableProjects = Project::where('status', 'available')->count();
        $borrowedProjects = Project::where('status', 'borrowed')->count();

        // Exams statistics
        $totalExams = Exam::count();
        $examsByType = Exam::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');

        // Monthly statistics for the past 6 months
        $months = [];
        $visitsData = [];
        $requestsData = [];
        $readingData = [];
        $borrowingData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $visitsData[] = Visit::whereMonth('visit_time', $month->month)
                ->whereYear('visit_time', $month->year)
                ->count();

            $requestsData[] = BookRequest::whereMonth('date_of_request', $month->month)
                ->whereYear('date_of_request', $month->year)
                ->count();

            $readingData[] = BookRequest::where('type', 'reading')
                ->whereMonth('date_of_request', $month->month)
                ->whereYear('date_of_request', $month->year)
                ->count();

            $borrowingData[] = BookRequest::where('type', 'borrowing')
                ->whereMonth('date_of_request', $month->month)
                ->whereYear('date_of_request', $month->year)
                ->count();
        }

        // Books status data for pie chart
        $booksStatusData = [
            'available' => $availableBooks,
            'borrowed' => $borrowedBooks,
            'other' => $totalBooks - $availableBooks - $borrowedBooks,
        ];

        // Students by level
        $studentsByLevel = Student::select('level', DB::raw('count(*) as count'))
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        // Most requested books (reading) - استخدام JOIN مباشر بدلاً من subquery مع LIMIT
        $mostReadBooks = DB::table('books')
            ->join('requests', 'books.book_id', '=', 'requests.book_id')
            ->where('requests.type', 'reading')
            ->select('books.*', DB::raw('count(requests.request_id) as read_count'))
            ->groupBy(
                'books.book_id',
                'books.book_name',
                'books.author',
                'books.isbn_no',
                'books.book_no',
                'books.price',
                'books.source',
                'books.summary',
                'books.department',
                'books.status',
                'reservation_date',
                'borrowed_date',
                'books.place',
                'books.shelf_no',
                'books.size',
                'books.release_date',
                'books.library_date',
                'books.image',
                'books.created_at',
                'books.updated_at'
            )
            ->orderBy('read_count', 'desc')
            ->limit(10)
            ->get();

        // Most borrowed books - استخدام JOIN مباشر بدلاً من subquery مع LIMIT
        $mostBorrowedBooks = DB::table('books')
            ->join('requests', 'books.book_id', '=', 'requests.book_id')
            ->where('requests.type', 'borrowing')
            ->select('books.*', DB::raw('count(requests.request_id) as borrow_count'))
            ->groupBy(
                'books.book_id',
                'books.book_name',
                'books.author',
                'books.isbn_no',
                'books.book_no',
                'books.price',
                'books.source',
                'books.summary',
                'reservation_date',
                'borrowed_date',
                'books.department',
                'books.status',
                'books.place',
                'books.shelf_no',
                'books.size',
                'books.release_date',
                'books.library_date',
                'books.image',
                'books.created_at',
                'books.updated_at'
            )
            ->orderBy('borrow_count', 'desc')
            ->limit(10)
            ->get();

        return view('statistics.index', compact(
            'totalBooks',
            'availableBooks',
            'borrowedBooks',
            'totalStudents',
            'studentsWithLevel',
            'totalVisits',
            'visitsToday',
            'visitsThisWeek',
            'visitsThisMonth',
            'totalBookRequests',
            'pendingBookRequests',
            'approvedBookRequests',
            'readingRequests',
            'borrowingRequests',
            'totalProjects',
            'availableProjects',
            'borrowedProjects',
            'totalExams',
            'examsByType',
            'months',
            'visitsData',
            'requestsData',
            'readingData',
            'borrowingData',
            'booksStatusData',
            'studentsByLevel',
            'mostReadBooks',

            'mostBorrowedBooks'
        ));
    }

    /**
     * Display detailed books statistics.
     *
     * @return \Illuminate\Http\Response
     */
   public function books()
    {
        // Books by status
        $booksByStatus = Book::select("status", DB::raw("count(*) as total"))
            ->groupBy("status")
            ->pluck("total", "status");

        // Books by department
        $booksByDepartment = Book::select("department", DB::raw("count(*) as total"))
            ->whereNotNull("department")
            ->groupBy("department")
            ->orderBy("total", "desc")
            ->get();

        // Most borrowed books - استخدام JOIN مباشر
        $mostBorrowedBooks = DB::table("books")
            ->join("requests", "books.book_id", "=", "requests.book_id")
            ->where("requests.type", "borrowing")
            ->select("books.book_id", "books.book_name", "books.author", DB::raw("count(requests.request_id) as borrow_count"))
            ->groupBy("books.book_id", "books.book_name", "books.author")
            ->orderBy("borrow_count", "desc")
            ->limit(15)
            ->get();

        // Most read books - استخدام JOIN مباشر
        $mostReadBooks = DB::table("books")
            ->join("requests", "books.book_id", "=", "requests.book_id")
            ->where("requests.type", "reading")
            ->select("books.book_id", "books.book_name", "books.author", DB::raw("count(requests.request_id) as read_count"))
            ->groupBy("books.book_id", "books.book_name", "books.author")
            ->orderBy("read_count", "desc")
            ->limit(15)
            ->get();

        // Monthly book requests for the past 12 months
        $months = [];
        $readingData = [];
        $borrowingData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format("M Y");

            $readingData[] = BookRequest::where("type", "reading")
                ->whereMonth("date_of_request", $month->month)
                ->whereYear("date_of_request", $month->year)
                ->count();

            $borrowingData[] = BookRequest::where("type", "borrowing")
                ->whereMonth("date_of_request", $month->month)
                ->whereYear("date_of_request", $month->year)
                ->count();
        }

        return view("statistics.books", compact(
            "booksByStatus",
            "booksByDepartment",
            "mostBorrowedBooks",
            "mostReadBooks",
            "months",
            "readingData",
            "borrowingData"
        ));
    }

    /**
     * Display detailed students statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function students()
    {
        // Students by level
        $studentsByLevel = Student::select('level', DB::raw('count(*) as total'))
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        // Most active students (by visits) - استخدام JOIN مباشر
        $mostActiveStudentsByVisits = DB::table('students')
            ->join('visits', 'students.student_id', '=', 'visits.student_id')
            ->select('students.*', DB::raw('count(visits.visit_id) as visits_count'))
            ->groupBy(
                'students.student_id',
                'students.username',
                'students.fullname',
                'students.email',
                'students.password',
                'students.phone_no',
                'students.level',
                'students.image',
                'students.university_code',
                'students.department',
                'students.borrow_docs',
                'students.created_at',
                'students.updated_at'
            )
            ->orderBy('visits_count', 'desc')
            ->limit(10)
            ->get();

        // Most active students (by book requests) - استخدام JOIN مباشر
        $mostActiveStudentsByRequests = DB::table('students')
            ->join('requests', 'students.student_id', '=', 'requests.student_id')
            ->select('students.*', DB::raw('count(requests.request_id) as requests_count'))
            ->groupBy(
                'students.student_id',
                'students.username',
                'students.fullname',
                'students.email',
                'students.password',
                'students.phone_no',
                'students.level',
                 'students.university_code',
                 'students.image',
                'students.department',
                'students.borrow_docs',
                'students.created_at',
                'students.updated_at'
            )
            ->orderBy('requests_count', 'desc')
            ->limit(10)
            ->get();

        // Students with overdue books - تبسيط الاستعلام
        $studentsWithOverdueBooks = Student::whereHas('bookRequests', function ($query) {
            $query->where('type', 'borrowing')
                ->whereHas('retrieveRequest', function ($subQuery) {
                    $subQuery->where('status', 'approved');
                })
                ->whereRaw('DATEDIFF(NOW(), date_of_request) > 14');
        })
            ->with(['bookRequests' => function ($query) {
                $query->where('type', 'borrowing')
                    ->whereHas('retrieveRequest', function ($subQuery) {
                        $subQuery->where('status', 'approved');
                    })
                    ->whereRaw('DATEDIFF(NOW(), date_of_request) > 14')
                    ->with('book');
            }])
            ->limit(20)
            ->get();

        // New students by month for the past 12 months
        $months = [];
        $newStudentsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $newStudentsData[] = Student::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        return view('statistics.students', compact(
            'studentsByLevel',
            'mostActiveStudentsByVisits',
            'mostActiveStudentsByRequests',
            'studentsWithOverdueBooks',
            'months',
            'newStudentsData'
        ));
    }

    /**
     * Display detailed visits statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function visits()
    {
        // Visits by day of week
        $visitsByDayOfWeek = [
            'الأحد' => Visit::whereRaw('DAYOFWEEK(visit_time) = 1')->count(),
            'الاثنين' => Visit::whereRaw('DAYOFWEEK(visit_time) = 2')->count(),
            'الثلاثاء' => Visit::whereRaw('DAYOFWEEK(visit_time) = 3')->count(),
            'الأربعاء' => Visit::whereRaw('DAYOFWEEK(visit_time) = 4')->count(),
            'الخميس' => Visit::whereRaw('DAYOFWEEK(visit_time) = 5')->count(),
            'الجمعة' => Visit::whereRaw('DAYOFWEEK(visit_time) = 6')->count(),
            'السبت' => Visit::whereRaw('DAYOFWEEK(visit_time) = 7')->count(),
        ];

        // Visits by hour of day
        $visitsByHour = [];
        for ($i = 0; $i < 24; $i++) {
            $visitsByHour[$i] = Visit::whereRaw('HOUR(visit_time) = ?', [$i])->count();
        }

        // Daily visits for the past 30 days
        $days = [];
        $visitsData = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $days[] = $day->format('M d');

            $visitsData[] = Visit::whereDate('visit_time', $day->toDateString())->count();
        }

        // Monthly visits for the past 12 months
        $months = [];
        $monthlyVisitsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $monthlyVisitsData[] = Visit::whereMonth('visit_time', $month->month)
                ->whereYear('visit_time', $month->year)
                ->count();
        }

        return view('statistics.visits', compact(
            'visitsByDayOfWeek',
            'visitsByHour',
            'days',
            'visitsData',
            'months',
            'monthlyVisitsData'
        ));
    }

    /**
     * Display detailed requests statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function requests()
    {
        // Requests by type - استخدام BookRequest Model
        $requestsByType = BookRequest::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        // Requests by status (through retrieve_requests)
        $requestsByStatus = RetrieveRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Reading vs Borrowing requests by month for the past 12 months
        $months = [];
        $readingData = [];
        $borrowingData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $readingData[] = BookRequest::where('type', 'reading')
                ->whereMonth('date_of_request', $month->month)
                ->whereYear('date_of_request', $month->year)
                ->count();

            $borrowingData[] = BookRequest::where('type', 'borrowing')
                ->whereMonth('date_of_request', $month->month)
                ->whereYear('date_of_request', $month->year)
                ->count();
        }

        // Average request processing time (in days)
        $avgProcessingTime = RetrieveRequest::whereNotNull('request_date')
            ->whereIn('status', ['approved', 'rejected'])
            ->select(DB::raw('AVG(DATEDIFF(updated_at, request_date)) as avg_days'))
            ->first()->avg_days ?? 0;

        return view('statistics.requests', compact(
            'requestsByType',
            'requestsByStatus',
            'months',
            'readingData',
            'borrowingData',
            'avgProcessingTime'
        ));
    }

    /**
     * Display detailed projects statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function projects()
    {
        $totalProjects = Project::count();
        // Projects by status
        $projectsByStatus = Project::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Projects by department
        $projectsByDepartment = Project::select('department', DB::raw('count(*) as total'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderBy('total', 'desc')
            ->get();

        // Monthly project additions for the past 12 months
        $months = [];
        $projectsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $projectsData[] = Project::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        // Most popular projects (by requests) - استخدام JOIN مباشر
        $mostPopularProjects = DB::table("projects")
            ->join("requests", "projects.project_id", "=", "requests.project_id")
            ->select("projects.*", DB::raw("count(requests.request_id) as requests_count"))
            ->groupBy(
                "projects.project_id",
                "projects.project_name",
                "projects.department",
                "projects.status",
                "projects.place",
                "projects.shelf_no",
                "projects.supervisor",
                "projects.project_date",
                'projects.image',
                "projects.created_at",
                "projects.updated_at"
            )
            ->orderBy("requests_count", "desc")
            ->limit(10)
            ->get();

        $recentProjects = Project::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('statistics.projects', compact(
            'projectsByStatus',
            'projectsByDepartment',
            'months',
            'projectsData',
            'totalProjects',
            'mostPopularProjects',
            'recentProjects'
        ));
    }

    /**
     * Display detailed exams statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function exams()
    {
        // Exams by type
        $examsByType = Exam::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        // Exams by department
        $examsByDepartment = Exam::select('dept', DB::raw('count(*) as total'))
            ->whereNotNull('dept')
            ->groupBy('dept')
            ->orderBy('total', 'desc')
            ->get();

        // Exams by semester
        $examsBySemester = Exam::select('semester', DB::raw('count(*) as total'))
            ->groupBy('semester')
            ->pluck('total', 'semester');

        // Exams by level
        $examsByLevel = Exam::select('level', DB::raw('count(*) as total'))
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        // Monthly exam additions for the past 12 months
        $months = [];
        $examsData = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            $examsData[] = Exam::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        return view('statistics.exams', compact(
            'examsByType',
            'examsByDepartment',
            'examsBySemester',
            'examsByLevel',
            'months',
            'examsData'
        ));
    }
}
