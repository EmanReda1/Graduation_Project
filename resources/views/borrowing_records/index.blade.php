@extends("layouts.app")

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section("content")
<div class="container">
    <div class="search-section-custom">
            <h1>سجـل الاستعـارة</h1>


    <!-- Search and Filter -->
    <div class="mb-4">
        <form action="{{ route("borrowing-records.index") }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request("search") }}">
            <select class="form-select ms-2" id="status" name="status">
                <option value="">كل الحالات</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلق
                            </option>
                            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>محجوز</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض
                            </option>
            </select>
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    </div>

    <!-- Borrowing Records Table -->
    <div class="card">
        <div class="card-body">
            @if($borrowingRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>التفاصيل</th>
                                <th>رقم طلب الإرجاع</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                                <th>تاريخ الطلب</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowingRecords as $record)
                                <tr>
                                    <td>
                                        <a href="{{ route("borrowing-records.show", $record->retrieve_id) }}" class="btn btn-sm btn-primary">عرض</a>
                                    </td>
                                    <td>{{ $record->retrieve_id }}</td>
                                    <td>{{ $record->request->book->book_name }}</td>
                                    <td>
                                        <a href="{{ route("students.show", $record->request->student_id) }}">
                                            {{ $record->request->student->fullname }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($record->date_of_request)->format("d/m/Y") }}</td>
                                    <td>
                                        @if($record->status == "pending")
                                            <span class="badge bg-warning">معلق</span>
                                        @elseif($record->status == "approved")
                                            <span class="badge bg-success">مقبول</span>
                                        @else
                                            <span class="badge bg-danger">مرفوض</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->status == "pending")
                                            <form action="{{ route("borrowing-records.approve-return", $record->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">قبول الإرجاع</button>
                                            </form>
                                            <form action="{{ route("borrowing-records.reject-return", $record->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger">رفض الإرجاع</button>
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
                    <div class="pagination-sm">
                        {{ $borrowingRecords->appends(request()->query())->links('custom-pagination') }}
                    </div>

            @else
                <div class="alert alert-info text-center">
                    لا توجد سجلات استعارة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


