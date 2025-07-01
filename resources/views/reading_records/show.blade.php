@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل سجل القراءة</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reading-records.index') }}" class="btn btn-secondary">العودة إلى سجل القراءة</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات القراءة #{{ $readingRecord->retrieve_id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الطالب:</h6>
                    <p>
                        <a href="{{ route('students.show', $readingRecord->request->student_id) }}">
                            {{ $readingRecord->request->student->fullname }}
                        </a>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكود الجامعي:</h6>
                    <p>{{ $readingRecord->request->student->student_id }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكتاب:</h6>
                    <p>{{ $readingRecord->request->book->book_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">المؤلف:</h6>
                    <p>{{ $readingRecord->request->book->author }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الطلب:</h6>
                    <p>{{ \Carbon\Carbon::parse($readingRecord->request->date_of_request)->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">حالة الطلب:</h6>
                    <p>
                        @if($readingRecord->request->status == 'approved')
                            <span class="badge bg-success">موافق عليه</span>
                        @elseif($readingRecord->request->status == 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @elseif($readingRecord->request->status == 'rejected')
                            <span class="badge bg-danger">مرفوض</span>
                        @elseif($readingRecord->request->status == 'completed')
                            <span class="badge bg-primary">مكتمل</span>
                        @else
                            <span class="badge bg-secondary">{{ $readingRecord->request->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($readingRecord->request->notes)
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="fw-bold">ملاحظات:</h6>
                    <p>{{ $readingRecord->request->notes }}</p>
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('reading-records.request-borrow', $readingRecord->retrieve_id) }}" class="btn btn-primary">طلب استعارة</a>
                    <a href="{{ route('reading-records.return', $readingRecord->retrieve_id) }}" class="btn btn-danger">إعادة الكتاب</a>
                </div>
                <div>
                    <a href="{{ route('reading-records.index') }}" class="btn btn-secondary">العودة</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
