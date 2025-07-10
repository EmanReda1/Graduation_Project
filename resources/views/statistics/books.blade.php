@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modern-dashboard.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header with Modern Design -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card header-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book"></i>
                        إحصائيات الكتب
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('statistics.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للوحة الرئيسية
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Books Status Cards -->
    <div class="row mb-4">
        @foreach($booksByStatus as $status => $count)
            <div class="col-lg-3 col-6">
                <div class="small-box
                    @if($status == 'available') bg-success
                    @elseif($status == 'borrowed') bg-warning
                    @elseif($status == 'reserved') bg-info
                    @else bg-danger
                    @endif">
                    <div class="inner">
                        <h3 class="counter-number">{{ $count }}</h3>
                        <p>
                            @if($status == 'available')
                                <i class="fas fa-check-circle"></i> كتب متاحة
                            @elseif($status == 'borrowed')
                                <i class="fas fa-hand-holding"></i> كتب مستعارة
                            @elseif($status == 'reserved')
                                <i class="fas fa-bookmark"></i> كتب محجوزة
                            @else
                                <i class="fas fa-exclamation-triangle"></i> {{ $status }}
                            @endif
                        </p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="small-box-footer">
                        <span class="status-indicator"></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row mb-4">
        <!-- Books by Department with Modern Chart -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الكتب حسب القسم
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="department">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="booksByDepartmentChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Requests Chart with Enhanced Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        طلبات الكتب الشهرية
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="monthly">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyRequestsChart" style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Popular Books Section -->
    <div class="row">
        <!-- Most Read Books with Modern Table -->
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i>
                        أكثر الكتب قراءة
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-success export-btn" data-table="read">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> #</th>
                                    <th><i class="fas fa-book-open"></i> اسم الكتاب</th>
                                    <th><i class="fas fa-user-edit"></i> المؤلف</th>
                                    <th><i class="fas fa-eye"></i> عدد القراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostReadBooks as $index => $book)
                                    <tr class="table-row-animated" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>
                                            <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="book-info">
                                                <strong>{{ $book->book_name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="author-name">{{ $book->author }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success count-badge">
                                                <i class="fas fa-eye"></i> {{ $book->read_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد بيانات متاحة</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Borrowed Books with Enhanced Design -->
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i>
                        أكثر الكتب استعارة
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-warning export-btn" data-table="borrowed">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> #</th>
                                    <th><i class="fas fa-book-open"></i> اسم الكتاب</th>
                                    <th><i class="fas fa-user-edit"></i> المؤلف</th>
                                    <th><i class="fas fa-hand-holding"></i> عدد الاستعارات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostBorrowedBooks as $index => $book)
                                    <tr class="table-row-animated" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>
                                            <span class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <div class="book-info">
                                                <strong>{{ $book->book_name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="author-name">{{ $book->author }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning count-badge">
                                                <i class="fas fa-hand-holding"></i> {{ $book->borrow_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fas fa-hand-holding fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد بيانات متاحة</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary Card -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card summary-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        ملخص إحصائيات الكتب
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-books"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ array_sum($booksByStatus->toArray()) }}</h4>
                                    <p>إجمالي الكتب</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ round(($booksByStatus['available'] ?? 0) / max(array_sum($booksByStatus->toArray()), 1) * 100, 1) }}%</h4>
                                    <p>نسبة الكتب المتاحة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ $booksByDepartment->count() }}</h4>
                                    <p>عدد الأقسام</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-trending-up"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ $mostReadBooks->sum('read_count') + $mostBorrowedBooks->sum('borrow_count') }}</h4>
                                    <p>إجمالي النشاط</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

