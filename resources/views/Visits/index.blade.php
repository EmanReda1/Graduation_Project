<!-- resources/views/visits/index.blade.php - Simple Table Only -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>سجـل الزيـارات</h1>
        </div>
        <div class="col-md-4 text-end">
            <!-- إزالة جميع الأزرار -->
        </div>
    </div>

    <!-- Search -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form action="{{ route('visits.search') }}" method="GET" class="d-flex">
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="بحث..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fas fa-search"></i> بحث
                </button>
            </form>
        </div>
        <div class="col-md-6">
            <form action="{{ route('visits.index') }}" method="GET" class="d-flex">
                <input type="date" class="form-control me-2" name="date" value="{{ request('date') }}">
                <button type="submit" class="btn btn-outline-primary">فلترة</button>
                @if(request('date') || request('search'))
                    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary ms-2">إلغاء</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Visits Table -->
    <div class="card">
        <div class="card-body">
            @if($visits->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>اسم الطالب</th>
                                <th>الكود الجامعي</th>
                                <th>تاريخ الزيارة</th>
                                <th>ساعة الزيارة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($visits as $visit)
                                <tr>
                                    <td>{{ $visit->student->fullname }}</td>
                                    <td>{{ $visit->student->university_code ?? $visit->student_id }}</td>
                                    <td>{{ $visit->visit_time ? \Carbon\Carbon::parse($visit->visit_time)->format('Y-m-d') : 'غير محدد' }}</td>
                                    <td>{{ $visit->visit_time ? \Carbon\Carbon::parse($visit->visit_time)->format('H:i') : 'غير محدد' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $visits->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    لا توجد زيارات مسجلة حالياً.
                    @if(request('search'))
                        <br><small>لم يتم العثور على نتائج للبحث: "{{ request('search') }}"</small>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

