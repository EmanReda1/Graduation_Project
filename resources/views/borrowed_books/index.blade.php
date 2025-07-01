@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>الكتب المستعارة</h1>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route('borrowed-books.search') }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Borrowed Books Table -->
    <div class="card">
        <div class="card-body">
            @if($borrowedBooks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>استعادة الكتاب</th>
                                <th>رقم الطلب</th>
                                <th>تاريخ الاستعارة</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowedBooks as $book)
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('borrowed-books.extend', $book->book_id) }}" class="btn btn-sm btn-primary me-2">استعارة</a>
                                            <a href="{{ route('borrowed-books.return', $book->book_id) }}" class="btn btn-sm btn-danger">رفض</a>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $request = $book->requests()
                                                ->where('type', 'borrowing')
                                                ->where('status', 'approved')
                                                ->orderBy('date_of_request', 'desc')
                                                ->first();
                                        @endphp
                                        {{ $request ? $request->request_id : 'غير متوفر' }}
                                    </td>
                                    <td>
                                        @if($request)
                                            {{ \Carbon\Carbon::parse($request->date_of_request)->format('d/m/Y') }}
                                        @else
                                            غير متوفر
                                        @endif
                                    </td>
                                    <td>{{ $book->book_name }}</td>
                                    <td>
                                        @if($request && $request->student)
                                            <a href="{{ route('students.show', $request->student_id) }}">
                                                {{ $request->student->fullname }}
                                            </a>
                                        @else
                                            غير متوفر
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $borrowedBooks->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد كتب مستعارة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
