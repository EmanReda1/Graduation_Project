<!-- resources/views/students/index.blade.php - Updated to match HTML styling -->
@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <!-- Search Section -->
    <div class="search-section-custom">

                <h1 class="mb-0">كل الطلاب</h1>

           <div class="row g-2 mb-3">
                <!-- Search Form -->
                <form action="{{ route('students.index') }}" method="GET" class="custom-search-form justify-content-end">
                    <input type="text" class="form-control search-input" id="search" name="search"
                           placeholder="بحث عن طالب..." value="{{ $search ?? '' }}">

                    <select class="form-control select-field" id="level" name="level">
                        <option value="">كل المستويات</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ isset($level) && $level == $lvl ? 'selected' : '' }}>
                                المستوى {{ $lvl }}
                            </option>
                        @endforeach
                    </select>

                    <select class="form-control select-field" id="department" name="department">
                        <option value="">كل الأقسام</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ isset($department) && $department == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary-custom search-btn">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </form>
            </div>
        </div>


    <!-- Students Table -->
    <div class="custom-table-container">
        @if($students->count() > 0)
            <div class="card-custom">
                <div class="card-body">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>الاسم الكامل</th>
                                <th>الكود الجامعي</th>
                                <th>القسم</th>
                                <th>المستوى</th>
                                <th>تاريخ التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                <tr>
                                    <td>
                                        <strong>{{ $student->fullname }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $student->university_code ?? 'غير محدد' }}</span>
                                    </td>
                                    <td>{{ $student->department ?? 'غير محدد' }}</td>
                                    <td>
                                        <span class="badge bg-primary">المستوى {{ $student->level }}</span>
                                    </td>
                                    <td>{{ $student->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('students.show', $student->student_id) }}"
                                           class="btn btn-outline-primary-custom btn-sm" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i> عرض
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-custom">
                        {{ $students->appends(request()->query())->links() }}
                    </ul>
                </nav>
            </div>

            <!-- Statistics Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card-custom">
                        <div class="card-body" style="padding: 20px;">
                            <h6 class="card-title">إحصائيات سريعة</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary">{{ $students->total() }}</h4>
                                        <small class="text-muted">إجمالي الطلاب</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success">{{ $students->currentPage() }}</h4>
                                        <small class="text-muted">الصفحة الحالية</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info">{{ $students->lastPage() }}</h4>
                                        <small class="text-muted">إجمالي الصفحات</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning">{{ $students->count() }}</h4>
                                        <small class="text-muted">في هذه الصفحة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info-custom alert-custom">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <h5>لا يوجد طلاب</h5>
                <p>لا يوجد طلاب متاحين حالياً أو لا توجد نتائج تطابق البحث.</p>
                @if($search || $level || $department)
                    <a href="{{ route('students.index') }}" class="btn btn-primary-custom">
                        <i class="fas fa-refresh"></i> إعادة تعيين البحث
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
</div>

@push('styles')
<style>
    .badge {
        font-size: 0.75em;
        padding: 0.25em 0.6em;
        border-radius: 0.375rem;
    }

    .bg-info {
        background-color: #0dcaf0 !important;
    }

    .bg-primary {
        background-color: #0d6efd !important;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .text-primary {
        color: #0d6efd !important;
    }

    .text-success {
        color: #198754 !important;
    }

    .text-info {
        color: #0dcaf0 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }

    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding-right: 15px;
        padding-left: 15px;
    }

    .col-md-3 {
        flex: 0 0 25%;
        max-width: 25%;
        padding-right: 15px;
        padding-left: 15px;
    }

    .col-md-9 {
        flex: 0 0 75%;
        max-width: 75%;
        padding-right: 15px;
        padding-left: 15px;
    }

    .justify-content-end {
        justify-content: flex-end !important;
    }

    .search-input {
        max-width: 250px;
        margin-left: 10px;
    }

    .select-field {
        max-width: 150px;
        margin-left: 10px;
    }

    .search-btn {
        margin-left: 10px;
        white-space: nowrap;
    }

    .custom-search-form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .align-items-center {
        align-items: center !important;
    }

    .mb-1 {
        margin-bottom: 0.25rem !important;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    @media (max-width: 768px) {
        .col-md-3, .col-md-9 {
            flex: 0 0 100%;
            max-width: 100%;
            margin-bottom: 1rem;
        }

        .custom-search-form {
            flex-direction: column;
            align-items: stretch;
        }

        .search-input, .select-field {
            max-width: 100%;
            margin-left: 0;
            margin-bottom: 10px;
        }
    }
</style>
@endpush
@endsection
