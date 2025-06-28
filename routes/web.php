<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProjectController;

use App\Http\Controllers\VisitController;
use App\Http\Controllers\ReadingRecordController;
use App\Http\Controllers\BorrowingRecordController;
use App\Http\Controllers\BorrowedBooksController;
use App\Http\Controllers\ReadingRequestController;
use App\Http\Controllers\BorrowingRequestController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\ExamController;

Route::get('/', function () {
    return view('auth.login');
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Book routes
Route::resource('books', BookController::class);
Route::get('/books/search', [BookController::class, 'search'])->name('books.search');
Route::get('/books/department/{department}', [BookController::class, 'getByDepartment'])->name('books.department');
Route::get('/books/available', [BookController::class, 'getAvailable'])->name('books.available');



// Student routes
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
Route::get('/students/statistics', [StudentController::class, 'getStatistics'])->name('students.statistics');
Route::get('/students/department/{department}', [StudentController::class, 'getByDepartment'])->name('students.department');
Route::get('/students/level/{level}', [StudentController::class, 'getByLevel'])->name('students.level');
Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');

// Project Routes
Route::resource("projects", ProjectController::class);
Route::get("/projects/available", [ProjectController::class, "availableProjects"])->name("projects.available");
Route::get("/projects/archived", [ProjectController::class, "archivedProjects"])->name("projects.archived");



// Visit Routes
Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
Route::get('/visits/search', [VisitController::class, 'search'])->name('visits.search');
Route::get('/visits/today', [VisitController::class, 'getToday'])->name('visits.today');

// QR Code display route for library entrance
Route::get('/library/qr-code', [VisitController::class, 'showQrCode'])->name('library.qr-code');

// Alternative routes for different displays
Route::get('/qr', [VisitController::class, 'showQrCode'])->name('qr.display');
Route::get('/scan', [VisitController::class, 'showQrCode'])->name('scan.display');

// Reading Records Routes
Route::get("/reading-records", [ReadingRecordController::class, "index"])->name("reading-records.index");
Route::get("/reading-records/{id}", [ReadingRecordController::class, "show"])->name("reading-records.show");
Route::get("/reading-records/{id}/request-borrow", [ReadingRecordController::class, "requestBorrow"])->name("reading-records.request-borrow");
Route::get("/reading-records/{id}/return", [ReadingRecordController::class, "returnBook"])->name("reading-records.return");

// Borrowing Records Routes
Route::get("/borrowing-records", [BorrowingRecordController::class, "index"])->name("borrowing-records.index");
Route::get("/borrowing-records/{id}", [BorrowingRecordController::class, "show"])->name("borrowing-records.show");

// Borrowed Books Routes (Currently borrowed books)
Route::get("/borrowed-books", [BorrowedBooksController::class, "index"])->name("borrowed-books.index");
Route::get("/borrowed-books/{id}", [BorrowedBooksController::class, "show"])->name("borrowed-books.show");
Route::get("/borrowed-books/{id}/return", [BorrowedBooksController::class, "returnBook"])->name("borrowed-books.return");
Route::get("/borrowed-books/{id}/extend", [BorrowedBooksController::class, "extendBorrowing"])->name("borrowed-books.extend");
Route::get("/borrowed-books/search", [BorrowedBooksController::class, "search"])->name("borrowed-books.search");
Route::get("/borrowed-books/department/{department}", [BorrowedBooksController::class, "getByDepartment"])->name("borrowed-books.department");
Route::get("/borrowed-books/student/{id}", [BorrowedBooksController::class, "getByStudent"])->name("borrowed-books.student");

// Reading Requests Routes (New)
Route::get("/reading-requests", [ReadingRequestController::class, "index"])->name("reading-requests.index");
Route::get("/reading-requests/{id}", [ReadingRequestController::class, "show"])->name("reading-requests.show");
Route::get("/reading-requests/{id}/approve", [ReadingRequestController::class, "approve"])->name("reading-requests.approve");
Route::get("/reading-requests/{id}/reject", [ReadingRequestController::class, "reject"])->name("reading-requests.reject");
Route::get("/reading-requests/search", [ReadingRequestController::class, "search"])->name("reading-requests.search");
Route::get("/reading-requests/student/{id}", [ReadingRequestController::class, "getByStudent"])->name("reading-requests.student");

// Borrowing Requests Routes (New)
Route::get("/borrowing-requests", [BorrowingRequestController::class, "index"])->name("borrowing-requests.index");
Route::get("/borrowing-requests/{id}", [BorrowingRequestController::class, "show"])->name("borrowing-requests.show");
Route::get("/borrowing-requests/{id}/approve", [BorrowingRequestController::class, "approve"])->name("borrowing-requests.approve");
Route::get("/borrowing-requests/{id}/reject", [BorrowingRequestController::class, "reject"])->name("borrowing-requests.reject");
Route::get("/borrowing-requests/search", [BorrowingRequestController::class, "search"])->name("borrowing-requests.search");
Route::get("/borrowing-requests/student/{id}", [BorrowingRequestController::class, "getByStudent"])->name("borrowing-requests.student");

// Exam Resource Routes
Route::resource('exams', ExamController::class);

// Additional Exam Routes
Route::get('/exams/available', [ExamController::class, 'getAvailable'])->name('exams.available');
Route::get('/exams/archived', [ExamController::class, 'getArchived'])->name('exams.archived');
Route::get('/exams/{exam}/download', [ExamController::class, 'downloadPdf'])->name('exams.download');


// Statistics Routes
Route::prefix('statistics')->name('statistics.')->group(function () {

    // Main dashboard
    Route::get('/', [StatisticsController::class, 'index'])->name('index');

    // Detailed statistics pages
    Route::get('/books', [StatisticsController::class, 'books'])->name('books');
    Route::get('/students', [StatisticsController::class, 'students'])->name('students');
    Route::get('/visits', [StatisticsController::class, 'visits'])->name('visits');
    Route::get('/requests', [StatisticsController::class, 'requests'])->name('requests');
    Route::get('/projects', [StatisticsController::class, 'projects'])->name('projects');
    Route::get('/exams', [StatisticsController::class, 'exams'])->name('exams');

});
