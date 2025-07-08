@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <!-- عنوان الصفحة مع السيرش - تحسين التصميم -->
    <div class="search-section-custom">
        <h1>سجـل الزيـارات</h1>

        <!-- Search and Filter Section -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <form action="{{ route('visits.search') }}" method="GET" class="custom-search-form">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search"
                               placeholder="البحث عن طالب..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="fas fa-search"></i> بحث
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-3">
                <form action="{{ route('visits.index') }}" method="GET" class="custom-search-form">
                    <div class="input-group">
                        <input type="date" class="form-control" name="date" value="{{ request('date') }}">
                        <button type="submit" class="btn btn-outline-primary-custom">
                            <i class="fas fa-filter"></i> فلترة
                        </button>
                        @if(request('date') || request('search'))
                            <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary-custom">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Visits Table -->
    <div class="custom-table-container">
        @if($visits->count() > 0)
            <div class="table-responsive">
                <table class="table table-custom table-striped table-hover">
                    <thead>
                        <tr>
                            <th>اسم الطالب</th>
                            <th>الكود الجامعي</th>
                            <th>
                                تاريخ الزيارة
                                <i class="fas fa-sort" style="cursor: pointer; margin-right: 5px;"></i>
                            </th>
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
            <div class="d-flex pagination-custom mt-4">
                {{ $visits->appends(request()->query())->links() }}
            </div>
        @else
            <div class="alert alert-info-custom alert-custom">
                <i class="fas fa-info-circle"></i>
                لا توجد زيارات مسجلة حالياً.
                @if(request('search'))
                    <br><small>لم يتم العثور على نتائج للبحث: "{{ request('search') }}"</small>
                @endif
                @if(request('date'))
                    <br><small>لم يتم العثور على زيارات في تاريخ: {{ request('date') }}</small>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تحسين السيرش - البحث أثناء الكتابة
    const searchInput = document.getElementById('search');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // يمكنك إضافة AJAX search هنا لاحقاً
                console.log('Searching for:', searchInput.value);
            }, 500);
        });
    }

    // تحسين السورت
    const sortIcon = document.querySelector('.fa-sort');
    if (sortIcon) {
        sortIcon.addEventListener('click', function() {
            // يمكنك إضافة السورت هنا لاحقاً
            console.log('Sorting by date');
        });
    }

    // تحسين UX - إضافة loading state للأزرار
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.type === 'submit') {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
                this.disabled = true;
            }
        });
    });
});
</script>
@endpush
