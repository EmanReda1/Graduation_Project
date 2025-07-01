@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل الكتاب المستعار</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('borrowed-books.index') }}" class="btn btn-secondary">العودة إلى الكتب المستعارة</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات الكتاب #{{ $book->book_id }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">اسم الكتاب:</h6>
                    <p>{{ $book->book_name }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">المؤلف:</h6>
                    <p>{{ $book->author }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">القسم:</h6>
                    <p>{{ $book->department }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الحالة:</h6>
                    <p>
                        <span class="badge bg-warning">مستعار</span>
                    </p>
                </div>
            </div>

            @if($borrower)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">مستعار بواسطة:</h6>
                    <p>
                        <a href="{{ route('students.show', $borrower->student_id) }}">
                            {{ $borrower->fullname }}
                        </a>
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكود الجامعي:</h6>
                    <p>{{ $borrower->student_id }}</p>
                </div>
            </div>
            @endif

            @php
                $request = $book->requests()
                    ->where('type', 'borrowing')
                    ->where('status', 'approved')
                    ->orderBy('date_of_request', 'desc')
                    ->first();
            @endphp

            @if($request)
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الاستعارة:</h6>
                    <p>{{ \Carbon\Carbon::parse($request->date_of_request)->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">رقم الطلب:</h6>
                    <p>{{ $request->request_id }}</p>
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
            @endif

            <!-- Return Requests Section -->
            @php
                $returnRequests = $book->requests()
                    ->where('type', 'return')
                    ->where('status', 'pending')
                    ->orderBy('date_of_request', 'desc')
                    ->get();
            @endphp

            @if($returnRequests->count() > 0)
            <hr>
            <h5 class="mb-3">طلبات إرجاع معلقة</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الطالب</th>
                            <th>تاريخ الطلب</th>
                            <th>ملاحظات</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnRequests as $returnRequest)
                        <tr>
                            <td>{{ $returnRequest->request_id }}</td>
                            <td>{{ $returnRequest->student->fullname }}</td>
                            <td>{{ \Carbon\Carbon::parse($returnRequest->date_of_request)->format('d/m/Y') }}</td>
                            <td>{{ $returnRequest->notes }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('borrowed-books.approve-return', $returnRequest->request_id) }}" class="btn btn-sm btn-success me-2">موافقة</a>
                                    <a href="{{ route('borrowed-books.reject-return', $returnRequest->request_id) }}" class="btn btn-sm btn-danger">رفض</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Extension Requests Section -->
            @php
                $extensionRequests = $book->requests()
                    ->where('type', 'extension')
                    ->where('status', 'pending')
                    ->orderBy('date_of_request', 'desc')
                    ->get();
            @endphp

            @if($extensionRequests->count() > 0)
            <hr>
            <h5 class="mb-3">طلبات تمديد معلقة</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الطالب</th>
                            <th>تاريخ الطلب</th>
                            <th>ملاحظات</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($extensionRequests as $extensionRequest)
                        <tr>
                            <td>{{ $extensionRequest->request_id }}</td>
                            <td>{{ $extensionRequest->student->fullname }}</td>
                            <td>{{ \Carbon\Carbon::parse($extensionRequest->date_of_request)->format('d/m/Y') }}</td>
                            <td>{{ $extensionRequest->notes }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="{{ route('borrowed-books.approve-extension', $extensionRequest->request_id) }}" class="btn btn-sm btn-success me-2">موافقة</a>
                                    <a href="{{ route('borrowed-books.reject-extension', $extensionRequest->request_id) }}" class="btn btn-sm btn-danger">رفض</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('book-requests.index') }}" class="btn btn-primary">عرض جميع الطلبات</a>
                </div>
                <div>
                    <a href="{{ route('borrowed-books.index') }}" class="btn btn-secondary">العودة</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
