@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="container">
        <!-- عنوان الصفحة مع السيرش - تحسين التصميم -->
        <div class="search-section-custom">
            <h1>كــل الكتــب</h1>

            <!-- Search and Filter Section - محسن ليشبه الصورة -->
            <div class="row g-2 mb-3">
                <!-- البحث -->
                <div class="col-md-6">
                    <form action="{{ route('books.index') }}" method="GET" class="custom-search-form">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="بحث عن كتاب..."
                                value="{{ request('search') }}">
                            <input type="hidden" name="department" value="{{ request('department') }}">
                            <input type="hidden" name="status" value="{{ request('status') }}">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                    </form>
                </div>

                <!-- فلتر القسم -->
                <div class="col-md-2">
                    <form action="{{ route('books.index') }}" method="GET" class="custom-search-form">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <select name="department" class="form-control" onchange="this.form.submit()">
                            <option value="">كل الأقسام</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- فلتر الحالة -->
                <div class="col-md-2">
                    <form action="{{ route('books.index') }}" method="GET" class="custom-search-form">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="department" value="{{ request('department') }}">
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">كل الحالات</option>
                            @foreach ($statuses as $stat)
                                <option value="{{ $stat }}" {{ request('status') == $stat ? 'selected' : '' }}>
                                    {{ $stat }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <!-- أزرار العمليات -->
                <div class="col-md-2">
                    <div class="d-flex gap-1">
                        @if (request('department') || request('status') || request('search'))
                            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary-custom">
                                <i class="fas fa-times"></i> إلغاء
                            </a>
                        @endif
                        <a href="{{ route('books.create') }}" class="btn btn-primary-custom">
                            <i class="fas fa-plus"></i> إضافة كتاب
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        @if (session('success'))
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Books Table -->
        <div class="custom-table-container">
            @if ($books->count() > 0)
                <div class="table-responsive">
                    <table class="table table-custom table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="80">الصورة</th>
                                <th>اسم الكتاب</th>
                                <th>اسم المؤلف</th>
                                <th>
                                    المسلسل
                                    <i class="fas fa-sort" style="cursor: pointer; margin-right: 5px;"></i>
                                </th>
                                <th>القسم</th>
                                <th>الحالة</th>
                                <th>التفاصيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($books as $book)
                                <tr>
                                    <td>
                                        @if ($book->image)
                                            <img src="{{ asset($book->image) }}" alt="{{ $book->book_name }}"
                                                class="img-thumbnail" width="50" height="50"
                                                style="object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                                style="width: 50px; height: 50px; border-radius: 4px; font-size: 14px;">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $book->book_name }}</strong>
                                            @if ($book->subtitle)
                                                <br><small class="text-muted">{{ $book->subtitle }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $book->author }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $book->book_no ?? 'غير محدد' }}</span>
                                    </td>
                                    <td>{{ $book->department }}</td>
                                    <td>
                                        @if ($book->status == 'available')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> متاح
                                            </span>
                                        @elseif($book->status == 'borrowed')
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> معار
                                            </span>
                                        @elseif($book->status == 'maintenance')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-tools"></i> صيانة
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">{{ $book->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('books.show', $book->book_id) }}"
                                                class="btn btn-primary-custom btn-sm">
                                                <i class="fas fa-eye"></i> عرض
                                            </a>
                                            <a href="{{ route('books.edit', $book->book_id) }}"
                                                class="btn btn-outline-primary-custom btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('books.destroy', $book->book_id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الكتاب؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-secondary-custom btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <div class="pagination-sm">
                        {{ $books->appends(request()->query())->links('custom-pagination') }}
                    </div>
                </div>

                <!-- Results Info -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        عرض {{ $books->count() }} من {{ $books->total() }} كتاب
                    </small>
                </div>
            @else
                <div class="alert alert-info-custom alert-custom text-center">
                    <i class="fas fa-info-circle"></i>
                    لا توجد كتب متاحة حالياً.
                    @if (request('search'))
                        <br><small>لم يتم العثور على نتائج للبحث: "{{ request('search') }}"</small>
                    @endif
                    @if (request('department'))
                        <br><small>لم يتم العثور على كتب في قسم: {{ request('department') }}</small>
                    @endif
                    @if (request('status'))
                        <br><small>لم يتم العثور على كتب بحالة: {{ request('status') }}</small>
                    @endif
                    <br>
                    <a href="{{ route('books.create') }}" class="btn btn-primary-custom mt-3">
                        <i class="fas fa-plus"></i> إضافة كتاب جديد
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>

        document.addEventListener('DOMContentLoaded', function() {
            // تحسين السيرش - البحث أثناء الكتابة
            const searchInput = document.querySelector('input[name="search"]');
            let searchTimeout;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        console.log('Searching for:', searchInput.value);
                        // يمكنك إضافة AJAX search هنا لاحقاً
                    }, 500);
                });
            }

            // تحسين السورت
            const sortIcon = document.querySelector('.fa-sort');
            if (sortIcon) {
                sortIcon.addEventListener('click', function() {
                    console.log('Sorting by serial number');
                    // يمكنك إضافة السورت هنا لاحقاً
                });
            }

            // تحسين UX - إضافة loading state للأزرار
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (this.type === 'submit') {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل...';
                        this.disabled = true;

                        // إعادة تفعيل الزر بعد 3 ثوانٍ (في حالة عدم إعادة تحميل الصفحة)
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }, 3000);
                    }
                });
            });
        });

    </script>
@endpush
