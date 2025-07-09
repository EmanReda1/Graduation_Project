@extends("layouts.app")
@section("content")
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل طلب الاستعارة</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route("borrowing-requests.index") }}" class="btn btn-secondary">العودة إلى طلبات الاستعارة</a>
        </div>
    </div>

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
                    <p>{{ $borrowingRequest->student->phone ?? "غير متوفر" }}</p>
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
                    <p>{{ \Carbon\Carbon::parse($borrowingRequest->date_of_request)->format("d/m/Y") }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">الحالة:</h6>
                    <p>
                        @if($borrowingRequest->status == "pending")
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @elseif($borrowingRequest->status == "approved")
                            <span class="badge bg-success">تمت الموافقة</span>
                        @elseif($borrowingRequest->status == "rejected")
                            <span class="badge bg-danger">مرفوض</span>
                        @elseif($borrowingRequest->status == "completed")
                            <span class="badge bg-primary">مكتمل</span>
                        @else
                            <span class="badge bg-secondary">{{ $borrowingRequest->status }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">حالة الكتاب:</h6>
                    <p>
                        @if($borrowingRequest->book)
                            @if($borrowingRequest->book->status == "available")
                                <span class="badge bg-success">متاح</span>
                            @elseif($borrowingRequest->book->status == "reserved")
                                <span class="badge bg-warning">محجوز</span>
                            @elseif($borrowingRequest->book->status == "borrowed")
                                <span class="badge bg-info">مستعار</span>
                            @else
                                <span class="badge bg-secondary">{{ $borrowingRequest->book->status }}</span>
                            @endif
                        @else
                            <span class="badge bg-danger">غير متوفر</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الحجز:</h6>
                    <p>{{ $borrowingRequest->book && $borrowingRequest->book->reservation_date ? \Carbon\Carbon::parse($borrowingRequest->book->reservation_date)->format("d/m/Y H:i") : "-" }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ التسليم:</h6>
                    <p>{{ $borrowingRequest->delivered_at ? \Carbon\Carbon::parse($borrowingRequest->delivered_at)->format("d/m/Y H:i") : "-" }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="fw-bold">تاريخ الإرجاع:</h6>
                    <p>{{ $borrowingRequest->returned_at ? \Carbon\Carbon::parse($borrowingRequest->returned_at)->format("d/m/Y H:i") : "-" }}</p>
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
                    <h6 class="fw-bold">الأوراق المطلوبة:</h6>
                    <div class="mt-2">
                        @php
                            // الحصول على أوراق الطالب من حقل borrow_docs
                            $studentDocuments = [];
                            if ($borrowingRequest->student && $borrowingRequest->student->borrow_docs) {
                                $studentDocuments = json_decode($borrowingRequest->student->borrow_docs, true);
                            }
                        @endphp

                        @if(!empty($studentDocuments) && is_array($studentDocuments))
                            <div class="row">
                                {{-- صورة البطاقة --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-id-card text-primary"></i>
                                                صورة البطاقة
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($studentDocuments["id_card"]) && !empty($studentDocuments["id_card"]))
                                                <a href="{{ asset("storage/" . $studentDocuments["id_card"]) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                    عرض صورة البطاقة
                                                </a>
                                                <small class="text-muted d-block mt-1">
                                                    {{ basename($studentDocuments["id_card"]) }}
                                                </small>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                    غير متوفرة
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- ورقة ختم النسر --}}
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <i class="fas fa-stamp text-warning"></i>
                                                ورقة ختم النسر
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($studentDocuments["eagle_seal"]) && !empty($studentDocuments["eagle_seal"]))
                                                <a href="{{ asset("storage/" . $studentDocuments["eagle_seal"]) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                    عرض ورقة ختم النسر
                                                </a>
                                                <small class="text-muted d-block mt-1">
                                                    {{ basename($studentDocuments["eagle_seal"]) }}
                                                </small>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-times-circle text-danger"></i>
                                                    غير متوفرة
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- حالة الأوراق --}}
                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle"></i>
                                    حالة الأوراق:
                                </h6>
                                @php
                                    $hasIdCard = isset($studentDocuments["id_card"]) && !empty($studentDocuments["id_card"]);
                                    $hasEagleSeal = isset($studentDocuments["eagle_seal"]) && !empty($studentDocuments["eagle_seal"]);
                                    $allComplete = $hasIdCard && $hasEagleSeal;
                                @endphp

                                @if($allComplete)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i>
                                        جميع الأوراق متوفرة
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        أوراق ناقصة
                                    </span>
                                    <small class="d-block mt-1">
                                        الأوراق المفقودة:
                                        @if(!$hasIdCard) صورة البطاقة @endif
                                        @if(!$hasIdCard && !$hasEagleSeal) ، @endif
                                        @if(!$hasEagleSeal) ورقة ختم النسر @endif
                                    </small>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    لا توجد أوراق مرفقة
                                </h6>
                                <p class="mb-0">لم يقم الطالب برفع أي أوراق مطلوبة للاستعارة.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div>
                        @if($borrowingRequest->status == "pending")
                            <form action="{{ route("borrowing-requests.approve", $borrowingRequest->request_id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">قبول</button>
                            </form>
                            <form action="{{ route("borrowing-requests.reject", $borrowingRequest->request_id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-danger">رفض</button>
                            </form>
                        @elseif($borrowingRequest->status == "approved" && $borrowingRequest->book && $borrowingRequest->book->status == "reserved" && !$borrowingRequest->delivered_at)
                            <form action="{{ route("borrowing-requests.deliver", $borrowingRequest->request_id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary">تسليم</button>
                            </form>
                        @elseif($borrowingRequest->status == "approved" && $borrowingRequest->book && $borrowingRequest->book->status == "borrowed" && $borrowingRequest->delivered_at && !$borrowingRequest->returned_at)
                            <form action="{{ route("borrowing-requests.return", $borrowingRequest->request_id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning">استلام</button>
                            </form>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route("borrowing-requests.index") }}" class="btn btn-secondary">العودة</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
