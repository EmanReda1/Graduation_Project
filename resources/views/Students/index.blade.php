<!-- resources/views/students/index.blade.php - Librarian View Only (No Image, No Username) -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>كل الطلاب</h1>
            <p class="text-muted">عرض بيانات الطلاب المسجلين في النظام</p>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('students.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="بحث عن طالب..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="level" name="level">
                        <option value="">كل المستويات</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ isset($level) && $level == $lvl ? 'selected' : '' }}>
                                المستوى {{ $lvl }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="department" name="department">
                        <option value="">كل الأقسام</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ isset($department) && $department == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <div class="card">
        <div class="card-body">
            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                                           class="btn btn-sm btn-info" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i> عرض
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $students->appends(request()->query())->links() }}
                </div>

                <!-- Statistics Summary -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
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
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>لا يوجد طلاب</h5>
                    <p>لا يوجد طلاب متاحين حالياً أو لا توجد نتائج تطابق البحث.</p>
                    @if($search || $level || $department)
                        <a href="{{ route('students.index') }}" class="btn btn-primary">
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
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75em;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }
</style>
@endpush
@endsection

