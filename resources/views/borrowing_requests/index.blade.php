@extends("layouts.app")

@section("content")
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>طلبـات الاستعـارة</h1>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route("borrowing-requests.index") }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request("search") }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Borrowing Requests Table -->
    <div class="card">
        <div class="card-body">
            @if(session("success"))
                <div class="alert alert-success">{{ session("success") }}</div>
            @endif
            @if(session("error"))
                <div class="alert alert-danger">{{ session("error") }}</div>
            @endif

            @if($borrowingRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>التفاصيل</th>
                                <th>رقم الطلب</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                                <th>حالة الطلب</th>
                                <th>حالة الكتاب</th>
                               <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowingRequests as $request)
                                <tr>
                                    <td>
                                        <a href="{{ route("borrowing-requests.show", $request->request_id) }}" class="btn btn-sm btn-primary">عرض</a>
                                    </td>
                                    <td>{{ $request->request_id }}</td>
                                    <td>{{ $request->book ? $request->book->book_name : "غير متوفر" }}</td>
                                    <td>
                                        <a href="{{ route("students.show", $request->student_id) }}">
                                            {{ $request->student ? $request->student->fullname : "غير متوفر" }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($request->status == "pending")
                                            <span class="badge bg-warning">معلق</span>
                                        @elseif($request->status == "approved")
                                            <span class="badge bg-success">مقبول</span>
                                        @elseif($request->status == "rejected")
                                            <span class="badge bg-danger">مرفوض</span>
                                        @elseif($request->status == "cancelled")
                                            <span class="badge bg-secondary">ملغي</span>
                                        @elseif($request->status == "completed")
                                            <span class="badge bg-info">مكتمل</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $request->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->book)
                                            @if($request->book->status == "available")
                                                <span class="badge bg-success">متاح</span>
                                            @elseif($request->book->status == "reserved")
                                                <span class="badge bg-warning">محجوز</span>
                                            @elseif($request->book->status == "borrowed")
                                                <span class="badge bg-info">مستعار</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $request->book->status }}</span>
                                            @endif
                                        @else
                                            <span class="badge bg-danger">غير متوفر</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->book && $request->book->reservation_date ? \Carbon\Carbon::parse($request->book->reservation_date)->format("d/m/Y H:i") : "-" }}</td>
                                    <td>
                                        @if($request->status == "pending")
                                            <form action="{{ route("borrowing-requests.approve", $request->request_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">قبول</button>
                                            </form>
                                            <form action="{{ route("borrowing-requests.reject", $request->request_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">رفض</button>
                                            </form>
                                        @elseif($request->status == "approved" && $request->book && $request->book->status == "reserved" && !$request->delivered_at)
                                            <form action="{{ route("borrowing-requests.deliver", $request->request_id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">تسليم</button>
                                            </form>

                                        @else
                                            لا توجد إجراءات
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $borrowingRequests->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد طلبات استعارة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


