<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BookDepartmentController; // Import the ML controller
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{

    protected $bookDepartmentController;

    public function __construct(BookDepartmentController $bookDepartmentController)
    {
        $this->bookDepartmentController = $bookDepartmentController;
    }
    /**
     * Display a listing of the books.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get query parameters for filtering
        $department = $request->query('department');
        $status = $request->query('status');
        $search = $request->query('search');

        // Start with a base query
        $query = Book::query();

        // Apply filters if provided
        if ($department) {
            $query->byDepartment($department);
        }

        if ($status) {
            $query->byStatus($status);
        }

        // Apply search if provided
        if ($search) {
            $query->search($search);
        }

        // Get paginated results
        $books = $query->paginate(10);

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        // Get unique statuses for filter dropdown
        $statuses = Book::select('status')->distinct()->pluck('status');

        return view('books.index', compact('books', 'departments', 'statuses', 'search', 'department', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get unique departments for dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('books.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'book_name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn_no' => 'required|string|max:255',
            'book_no' => 'required|integer',
            'price' => 'required|numeric',
            'source' => 'required|string|max:255',
            'summary' => 'required|string',
            'department' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'shelf_no' => 'required|string|max:255',
            'size' => 'required|integer',
            'release_date' => 'required|date',
            'library_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Predict department using the CLI method
        $mlRequest = Request::create(
            '/predict-department-cli',
            'POST',
            [
                'book_name' => $request->input('book_name'),
                'book_summary' => $request->input('summary')
            ]
        );

        $mlResponse = $this->bookDepartmentController->predictDepartmentCLI($mlRequest);
        $mlResult = json_decode($mlResponse->getContent(), true);

        if (isset($mlResult['success']) && $mlResult['success']) {
            $validated['department'] = $mlResult['predicted_department'];
        } else {
            Log::error('ML Department CLI Prediction Failed: ' . ($mlResult['error'] ?? 'Unknown error'));
            $validated['department'] = $request->input('department', 'General');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Use Laravel Storage - أسهل وأأمن
            $imagePath = $request->file('image')->store('images/books', 'public');
            $validated['image'] = $imagePath;
        }

        // Create new book
        $book = Book::create($validated);

        return redirect()->route('books.show', $book->book_id)
            ->with('success', 'تم إضافة الكتاب بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find book with reviews
        $book = Book::with(['reviews', 'favorites'])->findOrFail($id);

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $book = Book::findOrFail($id);

        // Get unique departments for dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        return view('books.edit', compact('book', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Find book
        $book = Book::findOrFail($id);


        $validated = $request->validate([
            'book_name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn_no' => 'required|string|max:255',
            'book_no' => 'required|integer',
            'price' => 'required|numeric',
            'source' => 'required|string|max:255',
            'summary' => 'required|string',
            'department' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'shelf_no' => 'required|string|max:255',
            'size' => 'required|integer',
            'release_date' => 'required|date',
            'library_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

                if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $shouldPredict = false;
            if ($request->input('book_name') !== $book->book_name || $request->input('summary') !== $book->summary) {
                $shouldPredict = true;
            }

            if (!$request->has('department') && !$shouldPredict) {
                $shouldPredict = true;
            }

            if ($shouldPredict) {
                $mlRequest = Request::create(
                    '/predict-department-cli',
                    'POST',
                    [
                        'book_name' => $request->input('book_name'),
                        'book_summary' => $request->input('summary')
                    ]
                );

                $mlResponse = $this->bookDepartmentController->predictDepartmentCLI($mlRequest);
                $mlResult = json_decode($mlResponse->getContent(), true);

                if (isset($mlResult['success']) && $mlResult['success']) {
                    $validated['department'] = $mlResult['predicted_department'];
                } else {
                    Log::error('ML Department CLI Prediction Failed during update: ' . ($mlResult['error'] ?? 'Unknown error'));
                    $validated['department'] = $book->department;
                }
            } else {
                $validated['department'] = $request->input('department', $book->department);
            }
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($book->image && Storage::disk('public')->exists($book->image)) {
                Storage::disk('public')->delete($book->image);
            }

            // Store new image using Laravel Storage
            $imagePath = $request->file('image')->store('images/books', 'public');
            $validated['image'] = $imagePath;
        }

        // Update book
        $book->update($validated);

        return redirect()->route('books.show', $book->book_id)
            ->with('success', 'تم تحديث الكتاب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find book
        $book = Book::findOrFail($id);

        // Delete image if exists
        if ($book->image && file_exists(public_path($book->image))) {
            unlink(public_path($book->image));
        }

        // Delete book
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'تم حذف الكتاب بنجاح');
    }

    /**
     * Search for books.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        $books = Book::search($search)->paginate(10);

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        // Get unique statuses for filter dropdown
        $statuses = Book::select('status')->distinct()->pluck('status');

        return view('books.index', compact('books', 'departments', 'statuses', 'search'));
    }

    /**
     * Get books by department.
     *
     * @param  string  $department
     * @return \Illuminate\Http\Response
     */
    public function getByDepartment($department)
    {
        $books = Book::byDepartment($department)->paginate(10);

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        // Get unique statuses for filter dropdown
        $statuses = Book::select('status')->distinct()->pluck('status');

        return view('books.index', compact('books', 'departments', 'statuses', 'department'));
    }

    /**
     * Get available books.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAvailable()
    {
        $books = Book::byStatus('available')->paginate(10);
        $status = 'available';

        // Get unique departments for filter dropdown
        $departments = Book::select('department')->distinct()->pluck('department');

        // Get unique statuses for filter dropdown
        $statuses = Book::select('status')->distinct()->pluck('status');

        return view('books.index', compact('books', 'departments', 'statuses', 'status'));
    }
}
