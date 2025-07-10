@extends('layouts.app')
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل طلب القراءة</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reading-requests.index') }}" class="btn btn-secondary">العودة إلى طلبات القراءة</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات الطلب #{{ $request->request_id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">اسم الطالب:</h6>
                    <p>{{ $request->student->fullname }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكود الجامعي:</h6>
                    <p>{{ $request->student->student_id }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">رقم التليفون:</h6>
                    <p>{{ $request->student->phone ?? 'غير متوفر' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">اسم الكتاب:</h6>
                    <p>{{ $request->book->book_name }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">رقم الطلب:</h6>
                    <p>{{ $request->request_id }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">رقم الكتاب:</h6>
                    <p>{{ $request->book->book_id }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الطلب:</h6>
                    <p>{{ \Carbon\Carbon::parse($request->date_of_request)->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الحالة:</h6>
                    <p>
                        @if($request->status == 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @elseif($request->status == 'approved')
                            <span class="badge bg-success">تمت الموافقة</span>
                        @elseif($request->status == 'rejected')
                            <span class="badge bg-danger">مرفوض</span>
                        @elseif($request->status == 'completed')
                            <span class="badge bg-primary">مكتمل</span>
                        @else
                            <span class="badge bg-secondary">{{ $request->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($request->notes)
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="fw-bold">ملاحظات:</h6>
                    <p>{{ $request->notes }}</p>
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    @if($request->status == 'pending')
                        <form action="{{ route('reading-requests.approve', $request->request_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">قبول</button>
                        </form>
                        <form action="{{ route('reading-requests.reject', $request->request_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger">رفض</button>
                        </form>
                    @endif
                </div>
                <div>
                    <a href="{{ route('reading-requests.index') }}" class="btn btn-secondary">العودة</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


