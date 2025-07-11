<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookRequestController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\BookDepartmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix("v1")->group(function () {

    // Public routes (no authentication required)
    Route::prefix("auth")->group(function () {
        Route::post("register", [AuthController::class, "register"]);
        Route::post("login", [AuthController::class, "login"]);
        Route::post("refresh", [AuthController::class, "refresh"]);
    });

    // Protected routes (authentication required)
    //   Route::middleware(["jwt.auth"])->group(function () {



    // Authentication routes
    Route::prefix("auth")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::get("profile", [AuthController::class, "profile"]);
        Route::put("profile", [AuthController::class, "updateProfile"]);
        Route::post("change-password", [AuthController::class, "changePassword"]);
    });

    // Dashboard routes
    Route::prefix("dashboard")->group(function () {
        Route::get("/", [DashboardController::class, "index"]);
        Route::get("quick-stats", [DashboardController::class, "quickStats"]);
        Route::get("activity-summary", [DashboardController::class, "activitySummary"]);
        Route::get("overdue-alert", [DashboardController::class, "overdueAlert"]);
    });

    // Books routes
    Route::prefix("books")->group(function () {
        Route::get("/", [BookController::class, "index"]);
        Route::get("available", [BookController::class, "available"]);
        Route::get("popular", [BookController::class, "popular"]);
        Route::get("recent", [BookController::class, "recent"]);
        Route::get("search", [BookController::class, "search"]);
        Route::get("department/{department}", [BookController::class, "byDepartment"]);
         Route::get("{id}/status", [BookController::class, "status"]); // New route for book status
        Route::get("{id}", [BookController::class, "show"]);



        // Book reviews
        Route::get("{bookId}/reviews", [ReviewController::class, "index"]);
        Route::post("{bookId}/reviews", [ReviewController::class, "store"]);
        Route::get("{bookId}/reviews/statistics", [ReviewController::class, "statistics"]);
        Route::get("{bookId}/can-review", [ReviewController::class, "canReview"]);



    });


    // Book requests routes (Student-specific)
    Route::prefix("book-requests")->group(function () {
        Route::get("/", [BookRequestController::class, "index"]); // API for listing student's requests with filters
        Route::post("/", [BookRequestController::class, "store"]);
        Route::get("borrowed-books", [BookRequestController::class, "borrowedBooks"]);
        Route::get("{id}", [BookRequestController::class, "show"]);
        Route::post("{id}/return", [BookRequestController::class, "requestReturn"]);
        Route::post("{id}/extend", [BookRequestController::class, "requestExtension"]);
        Route::delete("{id}/cancel", [BookRequestController::class, "cancel"]);
    });

    // Favorites routes
    Route::prefix("favorites")->group(function () {
        Route::get("/", [FavouriteController::class, "index"]);
        Route::post("/", [FavouriteController::class, "store"]);
        Route::post("toggle", [FavouriteController::class, "toggle"]);
        Route::get("count", [FavouriteController::class, "count"]);
        Route::delete("clear", [FavouriteController::class, "clear"]);
        Route::get("check/{bookId}", [FavouriteController::class, "check"]);
        Route::delete("{bookId}", [FavouriteController::class, "destroy"]);
    });

    // Reviews routes
    Route::prefix("reviews")->group(function () {
        Route::get("my-reviews", [ReviewController::class, "myReviews"]);
        Route::put("{reviewId}", [ReviewController::class, "update"]);
        Route::delete("{reviewId}", [ReviewController::class, "destroy"]);
    });

    // Visits routes
    Route::prefix("visits")->group(function () {
        Route::get("/", [VisitController::class, "index"]);
        Route::post("/", [VisitController::class, "store"]);
        Route::post("scan", [VisitController::class, "scan"])->middleware("auth:api"); // New QR code scan route
        Route::get("statistics", [VisitController::class, "statistics"]);
        Route::get("can-visit-today", [VisitController::class, "canVisitToday"]);
        Route::get("recent", [VisitController::class, "recent"]);
        Route::get("monthly", [VisitController::class, "monthly"]);
    });
    // Projects routes
    Route::prefix("projects")->group(function () {
        Route::get("/", [ProjectController::class, "index"]);
        Route::get("available", [ProjectController::class, "available"]);
        Route::get("recent", [ProjectController::class, "recent"]);
        Route::get("search", [ProjectController::class, "search"]);
        Route::get("statistics", [ProjectController::class, "statistics"]);
        Route::get("department/{department}", [ProjectController::class, "byDepartment"]);
        Route::get("year/{year}", [ProjectController::class, "byYear"]);
        Route::get("{id}", [ProjectController::class, "show"]);
        Route::get('/projects/{id}/pdf', [ProjectController::class, 'viewPdf'])->name('api.projects.pdf');
       //Route::get('/projects/{id}/download-pdf', [ProjectController::class, 'downloadPdf'])->name('api.projects.download-pdf');
    });
    // Notifications Routes
    Route::prefix("notifications")->group(function () {
        Route::get("/", [BookRequestController::class, "getNotifications"]); // Get all notifications
        Route::get("/unread-count", [BookRequestController::class, "getUnreadNotificationsCount"]); // Get unread count
        Route::put("/{id}/read", [BookRequestController::class, "markNotificationAsRead"]); // Mark single as read
        Route::put("/mark-all-read", [BookRequestController::class, "markAllNotificationsAsRead"]); // Mark all as read
    });

    // Exams routes
    Route::prefix("exams")->group(function () {
        Route::get("/", [ExamController::class, "index"]);
        Route::get("recent", [ExamController::class, "recent"]);
        Route::get("search", [ExamController::class, "search"]);
        Route::get("statistics", [ExamController::class, "statistics"]);
        Route::get("filters", [ExamController::class, "filters"]);
        Route::get("department/{department}", [ExamController::class, "byDepartment"]);
        Route::get("level/{level}", [ExamController::class, "byLevel"]);
        Route::get("semester/{semester}", [ExamController::class, "bySemester"]);
        Route::get("type/{type}", [ExamController::class, "byType"]);
        Route::get("{id}", [ExamController::class, "show"]);
        Route::get("{id}/download", [ExamController::class, "download"]);
    });

    // Search routes
    Route::prefix("search")->group(function () {
        Route::get("/", function (Request $request) {
            $query = $request->get("query");
            $type = $request->get("type", "all");

            $results = [];

            if ($type === "all" || $type === "books") {
                $books = \App\Models\Book::where("book_name", "like", "%{$query}%")
                    ->orWhere("author", "like", "%{$query}%")
                    ->limit(5)
                    ->get(["book_id", "book_name", "author"])
                    ->map(function ($book) {
                        return [
                            "id" => $book->book_id,
                            "title" => $book->book_name,
                            "subtitle" => $book->author,
                            "type" => "book"
                        ];
                    });
                $results["books"] = $books;
            }

            if ($type === "all" || $type === "projects") {
                $projects = \App\Models\Project::where("project_name", "like", "%{$query}%")
                    ->orWhere("supervisor", "like", "%{$query}%")
                    ->limit(5)
                    ->get(["project_id", "project_name", "department"])
                    ->map(function ($project) {
                        return [
                            "id" => $project->project_id,
                            "title" => $project->project_name,
                            "subtitle" => $project->department,
                            "type" => "project"
                        ];
                    });
                $results["projects"] = $projects;
            }

            if ($type === "all" || $type === "exams") {
                $exams = \App\Models\Exam::where("course_name", "like", "%{$query}%")
                    ->orWhere("doctor", "like", "%{$query}%")
                    ->limit(5)
                    ->get(["exam_id", "course_name", "doctor"])
                    ->map(function ($exam) {
                        return [
                            "id" => $exam->exam_id,
                            "title" => $exam->course_name,
                            "subtitle" => $exam->doctor,
                            "type" => "exam"
                        ];
                    });
                $results["exams"] = $exams;
            }

            return response()->json([
                "status" => "success",
                "data" => $results
            ]);
        });
    });
});

// API للتنبؤ بالقسم (الطريقة المطلوبة)
Route::post('/predict-department', [BookDepartmentController::class, 'predictDepartmentCLI']);

// API للتنبؤ عبر Flask (بديل)
Route::post('/predict-department-api', [BookDepartmentController::class, 'predictDepartment']);

// اختبار إعدادات Python
Route::get('/check-python', [BookDepartmentController::class, 'checkPythonSetup']);

    // api.php

// ... (باقي الـ routes)


//});

// This route is typically handled by Laravel's default web routes or not needed for API
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
