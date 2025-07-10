@extends("layouts.app")

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section("content")
<div class="container">

    <div class="search-section-custom">
            <h1>سجـل القـراءة</h1>


    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route("reading-records.index") }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request("search") }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    </div>

    <!-- Reading Records Table -->
    <div class="card">
        <div class="card-body">
            @if($readingRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>الإجراءات / الحالة</th>
                                <th>رقم الطلب</th>
                                <th>التاريخ</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readingRecords as $record)
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            @if($record->status == 'returned')
                                                <span class="badge bg-success">تمت الاستعادة</span>
                                            @elseif($record->retrieve_request && $record->retrieve_request->status == 'pending')
                                                <form action="{{ route("reading-records.return", $record->request_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary me-2">استعادة</button>
                                                </form>
                                                <form action="{{ route("reading-records.reject-return", $record->request_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">رفض</button>
                                                </form>
                                            @elseif($record->retrieve_request && $record->retrieve_request->status == 'rejected')
                                                <span class="badge bg-danger">تم رفض الإرجاع</span>
                                            @else
                                                <span class="badge bg-info">لا يوجد طلب إرجاع</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $record->request_id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($record->date_of_request)->format("d/m/Y") }}</td>
                                    <td>{{ $record->book->book_name }}</td>
                                    <td>
                                        <a href="{{ route("students.show", $record->student_id) }}">
                                            {{ $record->student->fullname }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $readingRecords->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد سجلات قراءة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


