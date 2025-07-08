<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
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

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($book->image && file_exists(public_path($book->image))) {
                unlink(public_path($book->image));
            }

            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images/books'), $imageName);
            $validated['image'] = 'images/books/' . $imageName;
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
