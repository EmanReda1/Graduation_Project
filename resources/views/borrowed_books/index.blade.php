@extends("layouts.app")

@section("content")
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>الكتب المستعارة</h1>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="mb-4">
        <form action="{{ route("borrowed-books.index") }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث عن كتاب..." value="{{ request("search") }}">
            <select class="form-select ms-2" id="department" name="department">
                <option value="">جميع الأقسام</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" {{ request("department") == $department ? "selected" : "" }}>{{ $department }}</option>
                @endforeach
            </select>
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
                                <th>التفاصيل</th>
                                <th>اسم الكتاب</th>
                                <th>المؤلف</th>
                                <th>القسم</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowedBooks as $book)
                                <tr>
                                    <td>
                                        <a href="{{ route("borrowed-books.show", $book->book_id) }}" class="btn btn-sm btn-primary">عرض</a>
                                    </td>
                                    <td>{{ $book->book_name }}</td>
                                    <td>{{ $book->author }}</td>
                                    <td>{{ $book->department }}</td>
                                    <td>
                                        <span class="badge bg-success">مستعار</span>
                                    </td>
                                    <td>
                                        <!-- Librarian actions for return/extension requests -->
                                        @if($book->return_request_pending)
                                            <form action="{{ route("borrowed-books.approve-return", $book->book_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">قبول الإرجاع</button>
                                            </form>
                                            <form action="{{ route("borrowed-books.reject-return", $book->book_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">رفض الإرجاع</button>
                                            </form>
                                        @elseif($book->extension_request_pending)
                                            <form action="{{ route("borrowed-books.approve-extension", $book->book_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">قبول التمديد</button>
                                            </form>
                                            <form action="{{ route("borrowed-books.reject-extension", $book->book_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">رفض التمديد</button>
                                            </form>
                                        @else
                                            لا توجد طلبات معلقة
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




