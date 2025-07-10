@extends("layouts.app")
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section("content")
<div class="container">

    <div class="search-section-custom">
            <h1>الكتب المستعارة</h1>

    <!-- Search and Filter -->
    <div class="mb-4">
        <form action="{{ route("borrowed-books.index") }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث عن كتاب..." value="{{ request("search") }}">
            <select class="form-select ms-2" id="department" name="department">

                <option value="">كل الأقسام</option>
                            <option value="cs" {{ request('department') == 'cs' ? 'selected' : '' }}>علوم الحاسب
                            </option>
                            <option value="ds" {{ request('department') == 'ds' ? 'selected' : '' }}>دعم القرار
                            </option>
                            <option value="it" {{ request('department') == 'it' ? 'selected' : '' }}>تكنولوجيا المعلومات
                            </option>
                            <option value="is" {{ request('department') == 'is' ? 'selected' : '' }}>نظم المعلومات
                            </option>
                            <option value="ai" {{ request('department') == 'ai' ? 'selected' : '' }}>الذكاء الاصطناعي
                            </option>
                            <option value="general" {{ request('department') == 'ce' ? 'selected' : '' }}>كتب عامة
                            </option>
            </select>
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
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
                                <th>اسم الطالب</th>
                                <th>تاريخ التسليم</th>
                                <th>تاريخ الإرجاع</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowedBooks as $book)
                                @php
                                    $latestBorrowRequest = App\Models\BookRequest::where("book_id", $book->id)
                                                                ->where("type", "borrowing")
                                                                ->where("status", "approved")
                                                                ->latest("date_of_request")
                                                                ->first();
                                    $borrower = $latestBorrowRequest ? $latestBorrowRequest->student : null;
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route("borrowed-books.show", $book->book_id) }}" class="btn btn-sm btn-primary">عرض</a>
                                    </td>
                                    <td>{{ $book->book_name }}</td>
                                    <td>
                                        @if($borrower)
                                            <a href="{{ route("students.show", $borrower->id) }}">
                                                {{ $borrower->fullname }}
                                            </a>
                                        @else
                                            غير متوفر
                                        @endif
                                    </td>
                                    <td>{{ $latestBorrowRequest && $latestBorrowRequest->delivered_at ? \Carbon\Carbon::parse($latestBorrowRequest->delivered_at)->format("d/m/Y H:i") : "-" }}</td>
                                    <td>{{ $latestBorrowRequest && $latestBorrowRequest->returned_at ? \Carbon\Carbon::parse($latestBorrowRequest->returned_at)->format("d/m/Y H:i") : "-" }}</td>
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

