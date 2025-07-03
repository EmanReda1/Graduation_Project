@extends('layouts.app')
@section('content')
<div class="container"> <div class="row mb-4"> <div class="col-md-8"> <h1>تفاصيل طلب الاستعارة</h1> </div> <div class="col-md-4 text-end"> <a href="{{ route('borrowing-requests.index') }}" class="btn btn-secondary">العودة إلى طلبات الاستعارة</a> </div> </div>
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">معلومات الطلب #{{ $borrowingRequest->request_id }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">اسم الطالب:</h6>
                <p>{{ $borrowingRequest->student->fullname }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">الكود الجامعي:</h6>
                <p>{{ $borrowingRequest->student->student_id }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">رقم التليفون:</h6>
                <p>{{ $borrowingRequest->student->phone ?? 'غير متوفر' }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">اسم الكتاب:</h6>
                <p>{{ $borrowingRequest->book->book_name }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">رقم الطلب:</h6>
                <p>{{ $borrowingRequest->request_id }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">رقم الكتاب:</h6>
                <p>{{ $borrowingRequest->book->book_id }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">تاريخ الطلب:</h6>
                <p>{{ \Carbon\Carbon::parse($borrowingRequest->date_of_request)->format('d/m/Y') }}</p>
            </div>
            <div class="col-md-6 mb-3">
                <h6 class="fw-bold">الحالة:</h6>
                <p>
                    @if($borrowingRequest->status == 'pending')
                        <span class="badge bg-warning">قيد الانتظار</span>
                    @elseif($borrowingRequest->status == 'approved')
                        <span class="badge bg-success">تمت الموافقة</span>
                    @elseif($borrowingRequest->status == 'rejected')
                        <span class="badge bg-danger">مرفوض</span>
                    @elseif($borrowingRequest->status == 'completed')
                        <span class="badge bg-primary">مكتمل</span>
                    @else
                        <span class="badge bg-secondary">{{ $borrowingRequest->status }}</span>
                    @endif
                </p>
            </div>
        </div>

        @if($borrowingRequest->notes)
        <div class="row">
            <div class="col-12 mb-3">
                <h6 class="fw-bold">ملاحظات:</h6>
                <p>{{ $borrowingRequest->notes }}</p>
            </div>
        </div>
        @endif

        <!-- Borrowing Documents Section -->
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="fw-bold">الأوراق:</h6>
                <div class="mt-2">
                    @if($borrowingRequest->documents && count($borrowingRequest->documents) > 0)
                        @foreach($borrowingRequest->documents as $document)
                            <div class="mb-2">
                                <a href="{{ asset('storage/' . $document->path) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-file-pdf"></i> أوراق الاستعارة
                                </a>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">لا توجد أوراق مرفقة.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between">
            <div>
                @if($borrowingRequest->status == 'pending')
                    <form action="{{ route('borrowing-requests.approve', $borrowingRequest->request_id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success">قبول</button>
                    </form>
                    <form action="{{ route('borrowing-requests.reject', $borrowingRequest->request_id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger">رفض</button>
                    </form>
                @endif
            </div>
            <div>
                <a href="{{ route('borrowing-requests.index') }}" class="btn btn-secondary">العودة</a>
            </div>
        </div>
    </div>
</div>
</div> @endsection
