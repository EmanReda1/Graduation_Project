@extends("layouts.app")
@section("content")
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل الكتاب المستعار</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route("borrowed-books.index") }}" class="btn btn-secondary">العودة إلى الكتب المستعارة</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات الكتاب: {{ $book->book_name }}</h5>
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
                    <h6 class="fw-bold">رقم الكتاب (ISBN):</h6>
                    <p>{{ $book->isbn }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">حالة الكتاب:</h6>
                    <p><span class="badge bg-success">مستعار</span></p>
                </div>
            </div>

            @if($borrower)
            <hr>
            <h5 class="mb-3">معلومات الطالب المستعير:</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">اسم الطالب:</h6>
                    <p>{{ $borrower->fullname }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الكود الجامعي:</h6>
                    <p>{{ $borrower->student_id }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">رقم التليفون:</h6>
                    <p>{{ $borrower->phone ?? "غير متوفر" }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الاستعارة:</h6>
                    <p>{{ \Carbon\Carbon::parse($borrowRequest->date_of_request)->format("d/m/Y") }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ التسليم:</h6>
                    <p>{{ $borrowRequest->delivered_at ? \Carbon\Carbon::parse($borrowRequest->delivered_at)->format("d/m/Y H:i") : "-" }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الإرجاع:</h6>
                    <p>{{ $borrowRequest->returned_at ? \Carbon\Carbon::parse($borrowRequest->returned_at)->format("d/m/Y H:i") : "-" }}</p>
                </div>
            </div>
            @endif

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div>
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
                    </div>
                    <div>
                        <a href="{{ route("borrowed-books.index") }}" class="btn btn-secondary">العودة</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

