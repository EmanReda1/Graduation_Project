@extends("layouts.app")
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section("content")
<div class="container">

    <div class="search-section-custom">
            <h1>تفاصيل سجل القراءة</h1>

        <div class="col-md-4 text-end">
            <a href="{{ route("reading-records.index") }}" class="btn btn-secondary">العودة إلى سجل القراءة</a>
        </div>
    </div>


    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات القراءة #{{ $readingRecord->request_id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الطالب:</h6>
                    <p>
                        <a href="{{ route("students.show", $readingRecord->student_id) }}">
                            {{ $readingRecord->student->fullname }}
                        </a>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكود الجامعي:</h6>
                    <p>{{ $readingRecord->student->student_id }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكتاب:</h6>
                    <p>{{ $readingRecord->book->book_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">المؤلف:</h6>
                    <p>{{ $readingRecord->book->author }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الطلب:</h6>
                    <p>{{ \Carbon\Carbon::parse($readingRecord->date_of_request)->format("d/m/Y") }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">حالة الطلب:</h6>
                    <p>
                        @if($readingRecord->status == "approved")
                            <span class="badge bg-success">موافق عليه</span>
                        @elseif($readingRecord->status == "pending")
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @elseif($readingRecord->status == "rejected")
                            <span class="badge bg-danger">مرفوض</span>
                        @elseif($readingRecord->status == "returned")
                            <span class="badge bg-info">تمت الاستعادة</span>
                        @else
                            <span class="badge bg-secondary">{{ $readingRecord->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($readingRecord->notes)
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="fw-bold">ملاحظات:</h6>
                    <p>{{ $readingRecord->notes }}</p>
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    @if($readingRecord->status == 'returned')
                        <span class="badge bg-success">تمت الاستعادة</span>
                    @elseif($readingRecord->retrieve_request && $readingRecord->retrieve_request->status == 'pending')
                        <form action="{{ route("reading-records.return", $readingRecord->request_id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">استعادة</button>
                        </form>
                        <form action="{{ route("reading-records.reject-return", $readingRecord->request_id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">رفض</button>
                        </form>
                    @elseif($readingRecord->retrieve_request && $readingRecord->retrieve_request->status == 'rejected')
                        <span class="badge bg-danger">تم رفض الإرجاع</span>
                    @else
                        <span class="badge bg-info">لا يوجد طلب إرجاع</span>
                    @endif
                </div>
                <div>
                    <a href="{{ route("reading-records.index") }}" class="btn btn-secondary">العودة</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


