@extends("layouts.app")

@section("content")
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل سجل الاستعارة</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route("borrowing-records.index") }}" class="btn btn-secondary">العودة إلى سجل الاستعارة</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات طلب الإرجاع #{{ $borrowingRecord->retrieve_id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الطالب:</h6>
                    <p>
                        <a href="{{ route("students.show", $borrowingRecord->request->student_id) }}">
                            {{ $borrowingRecord->request->student->fullname }}
                        </a>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكود الجامعي:</h6>
                    <p>{{ $borrowingRecord->request->student->student_id }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكتاب:</h6>
                    <p>{{ $borrowingRecord->request->book->book_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">المؤلف:</h6>
                    <p>{{ $borrowingRecord->request->book->author }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ طلب الإرجاع:</h6>
                    <p>{{ \Carbon\Carbon::parse($borrowingRecord->date_of_request)->format("d/m/Y") }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الحالة:</h6>
                    <p>
                        @if($borrowingRecord->status == "pending")
                            <span class="badge bg-warning">معلق</span>
                        @elseif($borrowingRecord->status == "approved")
                            <span class="badge bg-success">مقبول</span>
                        @else
                            <span class="badge bg-danger">مرفوض</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($borrowingRecord->notes)
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="fw-bold">ملاحظات:</h6>
                    <p>{{ $borrowingRecord->notes }}</p>
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    @if($borrowingRecord->status == "pending")
                        <form action="{{ route("borrowing-records.approve-return", $borrowingRecord->retrieve_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">قبول الإرجاع</button>
                        </form>
                        <form action="{{ route("borrowing-records.reject-return", $borrowingRecord->retrieve_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">رفض الإرجاع</button>
                        </form>
                    @endif
                </div>
                <div>
                    <a href="{{ route("borrowing-records.index") }}" class="btn btn-secondary">العودة</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


