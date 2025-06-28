<!-- resources/views/books/index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>كل الكتب</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('books.create') }}" class="btn btn-primary">إضافة كتاب جديد</a>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('books.index') }}" method="GET" class="row g-3">
                <!-- Search Input -->
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="بحث عن كتاب..." value="{{ request('search') }}">
                </div>

                <!-- Department Filter -->
                <div class="col-md-3">
                    <select name="department" class="form-select">
                        <option value="">كل الأقسام</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">كل الحالات</option>
                        @foreach($statuses as $stat)
                            <option value="{{ $stat }}" {{ request('status') == $stat ? 'selected' : '' }}>{{ $stat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Flash Message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Books Table -->
    <div class="card">
        <div class="card-body">
            @if($books->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>اسم الكتاب</th>
                                <th>المؤلف</th>
                                <th>القسم</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($books as $book)
                                <tr>
                                    <td>
                                        @if($book->image)
                                            <img src="{{ asset($book->image) }}" alt="{{ $book->book_name }}" width="50">
                                        @else
                                            <img src="{{ asset('images/no-image.png') }}" alt="No Image" width="50">
                                        @endif
                                    </td>
                                    <td>{{ $book->book_name }}</td>
                                    <td>{{ $book->author }}</td>
                                    <td>{{ $book->department }}</td>
                                    <td>
                                        @if($book->status == 'available')
                                            <span class="badge bg-success">متاح</span>
                                        @elseif($book->status == 'borrowed')
                                            <span class="badge bg-warning">معار</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $book->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('books.show', $book->book_id) }}" class="btn btn-sm btn-info">عرض</a>
                                            <a href="{{ route('books.edit', $book->book_id) }}" class="btn btn-sm btn-primary">تعديل</a>
                                            <form action="{{ route('books.destroy', $book->book_id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكتاب؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $books->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    لا توجد كتب متاحة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
