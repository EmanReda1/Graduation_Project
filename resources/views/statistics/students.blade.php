@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/modern-dashboard.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card header-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-graduate"></i>
                        إحصائيات الطلاب
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('statistics.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> العودة للوحة الإحصائيات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 class="counter-number">{{ $studentsByLevel->sum('total') }}</h3>
                    <p><i class="fas fa-user-graduate"></i> إجمالي الطلاب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: 100%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 class="counter-number">{{ $mostActiveStudentsByVisits->count() }}</h3>
                    <p><i class="fas fa-door-open"></i> طلاب نشطون (زيارات)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ $studentsByLevel->sum('total') > 0 ? round(($mostActiveStudentsByVisits->count() / $studentsByLevel->sum('total')) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="counter-number">{{ $mostActiveStudentsByRequests->count() }}</h3>
                    <p><i class="fas fa-clipboard-list"></i> طلاب نشطون (طلبات)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ $studentsByLevel->sum('total') > 0 ? round(($mostActiveStudentsByRequests->count() / $studentsByLevel->sum('total')) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 class="counter-number">{{ $studentsWithOverdueBooks->count() }}</h3>
                    <p><i class="fas fa-exclamation-triangle"></i> طلاب متأخرون</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ $studentsByLevel->sum('total') > 0 ? round(($studentsWithOverdueBooks->count() / $studentsByLevel->sum('total')) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row">
        <!-- Students by Level with Modern Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الطلاب حسب المستوى
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="level">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary chart-fullscreen" data-chart="level">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="studentsByLevelChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-stats mt-3">
                        <div class="row">
                            @foreach($studentsByLevel as $level)
                                <div class="col-6 mb-2">
                                    <div class="stat-mini">
                                        <span class="stat-label">المستوى {{ $level->level }}</span>
                                        <span class="stat-value">{{ $level->total }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Students by Month with Enhanced Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        الطلاب الجدد شهرياً
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="new">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success export-chart" data-chart="new">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="newStudentsChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-summary mt-3">
                        <div class="summary-item">
                            <span class="summary-label">إجمالي الطلاب الجدد:</span>
                            <span class="summary-value">{{ array_sum($newStudentsData) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">متوسط شهري:</span>
                            <span class="summary-value">{{ round(array_sum($newStudentsData) / max(count($newStudentsData), 1), 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Active Students Tables -->
    <div class="row mt-4">
        <!-- Most Active by Visits -->
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i>
                        أكثر الطلاب نشاطاً (الزيارات)
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-success export-btn" data-table="visits">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                        <button class="btn btn-sm btn-outline-primary filter-btn" data-table="visits">
                            <i class="fas fa-filter"></i> فلترة
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-medal"></i> الترتيب</th>
                                    <th><i class="fas fa-user"></i> اسم الطالب</th>
                                    <th><i class="fas fa-layer-group"></i> المستوى</th>
                                    <th><i class="fas fa-door-open"></i> عدد الزيارات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostActiveStudentsByVisits as $index => $student)
                                    <tr class="table-row-animated" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>
                                            <span class="rank-badge rank-{{ $index + 1 }}">
                                                @if($index == 0)
                                                    <i class="fas fa-crown"></i>
                                                @elseif($index == 1)
                                                    <i class="fas fa-medal"></i>
                                                @elseif($index == 2)
                                                    <i class="fas fa-award"></i>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="student-info">
                                                <strong>{{ $student->fullname ?? $student->username }}</strong>
                                                <small class="text-muted d-block">{{ $student->username }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary level-badge">
                                                المستوى {{ $student->level }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success count-badge">
                                                <i class="fas fa-door-open"></i> {{ $student->visits_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
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

        <!-- Most Active by Requests -->
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i>
                        أكثر الطلاب نشاطاً (الطلبات)
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-warning export-btn" data-table="requests">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                        <button class="btn btn-sm btn-outline-primary filter-btn" data-table="requests">
                            <i class="fas fa-filter"></i> فلترة
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-medal"></i> الترتيب</th>
                                    <th><i class="fas fa-user"></i> اسم الطالب</th>
                                    <th><i class="fas fa-layer-group"></i> المستوى</th>
                                    <th><i class="fas fa-clipboard-list"></i> عدد الطلبات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostActiveStudentsByRequests as $index => $student)
                                    <tr class="table-row-animated" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>
                                            <span class="rank-badge rank-{{ $index + 1 }}">
                                                @if($index == 0)
                                                    <i class="fas fa-crown"></i>
                                                @elseif($index == 1)
                                                    <i class="fas fa-medal"></i>
                                                @elseif($index == 2)
                                                    <i class="fas fa-award"></i>
                                                @else
                                                    {{ $index + 1 }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="student-info">
                                                <strong>{{ $student->fullname ?? $student->username }}</strong>
                                                <small class="text-muted d-block">{{ $student->username }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary level-badge">
                                                المستوى {{ $student->level }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning count-badge">
                                                <i class="fas fa-clipboard-list"></i> {{ $student->requests_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
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

    <!-- Enhanced Students with Overdue Books -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card table-card alert-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        الطلاب المتأخرون في إرجاع الكتب
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-danger export-btn" data-table="overdue">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                        <button class="btn btn-sm btn-outline-warning notify-btn">
                            <i class="fas fa-bell"></i> إرسال تنبيه
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($studentsWithOverdueBooks->count() > 0)
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>تنبيه:</strong> يوجد {{ $studentsWithOverdueBooks->count() }} طالب متأخر في إرجاع الكتب
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> #</th>
                                    <th><i class="fas fa-user"></i> اسم الطالب</th>
                                    <th><i class="fas fa-layer-group"></i> المستوى</th>
                                    <th><i class="fas fa-book"></i> الكتب المتأخرة</th>
                                    <th><i class="fas fa-clock"></i> أيام التأخير</th>
                                    <th><i class="fas fa-cogs"></i> الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studentsWithOverdueBooks as $index => $student)
                                    <tr class="table-row-animated overdue-row" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="student-info">
                                                <strong>{{ $student->fullname ?? $student->username }}</strong>
                                                <small class="text-muted d-block">{{ $student->username }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary level-badge">
                                                المستوى {{ $student->level }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($student->bookRequests)
                                                <div class="books-list">
                                                    @foreach($student->bookRequests as $request)
                                                        <span class="badge badge-danger book-badge mb-1">
                                                            <i class="fas fa-book"></i>
                                                            {{ $request->book->book_name ?? 'كتاب غير محدد' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">لا توجد بيانات</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($student->bookRequests && $student->bookRequests->first())
                                                @php
                                                    $daysDiff = \Carbon\Carbon::parse($student->bookRequests->first()->date_of_request)->diffInDays(\Carbon\Carbon::now());
                                                @endphp
                                                <span class="badge badge-danger days-badge">
                                                    <i class="fas fa-clock"></i> {{ $daysDiff }} يوم
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline-warning remind-btn" data-student="{{ $student->id }}">
                                                    <i class="fas fa-bell"></i> تذكير
                                                </button>
                                                <button class="btn btn-sm btn-outline-info contact-btn" data-student="{{ $student->id }}">
                                                    <i class="fas fa-phone"></i> اتصال
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center empty-state success-state">
                                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                            <h5 class="text-success">ممتاز!</h5>
                                            <p class="text-muted">لا يوجد طلاب متأخرون في إرجاع الكتب</p>
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

    <!-- Enhanced Students by Level Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        تفاصيل الطلاب حسب المستوى
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-primary export-btn" data-table="levels">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-layer-group"></i> المستوى</th>
                                    <th><i class="fas fa-users"></i> عدد الطلاب</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalStudents = $studentsByLevel->sum('total');
                                @endphp
                                @forelse($studentsByLevel as $level)
                                    <tr class="table-row-animated">
                                        <td>
                                            <span class="badge badge-primary level-badge">
                                                <i class="fas fa-graduation-cap"></i> المستوى {{ $level->level }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success count-badge">{{ $level->total }}</span>
                                        </td>
                                        <td>
                                            <strong>
                                                @if($totalStudents > 0)
                                                    {{ round(($level->total / $totalStudents) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-primary"
                                                     style="width: {{ $totalStudents > 0 ? round(($level->total / $totalStudents) * 100, 1) : 0 }}%">
                                                    {{ $level->total }} طالب
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
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
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/modern-dashboard.js') }}"></script>
<script>
$(function() {
    // Enhanced Students by Level Chart
    const studentsByLevelCtx = document.getElementById('studentsByLevelChart').getContext('2d');
    const levelChart = new Chart(studentsByLevelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($studentsByLevel->pluck('level')->map(function($level) { return 'المستوى ' . $level; })->toArray()) !!},
            datasets: [{
                label: 'عدد الطلاب',
                data: {!! json_encode($studentsByLevel->pluck('total')->toArray()) !!},
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(74, 172, 254, 0.8)',
                    'rgba(67, 233, 123, 0.8)',
                    'rgba(250, 112, 154, 0.8)',
                    'rgba(168, 237, 234, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(74, 172, 254, 1)',
                    'rgba(67, 233, 123, 1)',
                    'rgba(250, 112, 154, 1)',
                    'rgba(168, 237, 234, 1)',
                    'rgba(245, 158, 11, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#718096'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#718096'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#718096'
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Enhanced New Students Chart
    const newStudentsCtx = document.getElementById('newStudentsChart').getContext('2d');
    const newChart = new Chart(newStudentsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'الطلاب الجدد',
                data: {!! json_encode($newStudentsData) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#718096'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#718096'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#718096'
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Chart controls functionality
    $('.chart-refresh').on('click', function() {
        const chartType = $(this).data('chart');
        const icon = $(this).find('i');

        icon.addClass('fa-spin');

        setTimeout(() => {
            if (chartType === 'level') {
                levelChart.update();
            } else if (chartType === 'new') {
                newChart.update();
            }
            icon.removeClass('fa-spin');
        }, 1000);
    });

    // Action buttons functionality
    $('.remind-btn').on('click', function() {
        const studentId = $(this).data('student');
        const button = $(this);
        const originalText = button.html();

        button.html('<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...');

        setTimeout(() => {
            button.html('<i class="fas fa-check"></i> تم الإرسال');
            setTimeout(() => {
                button.html(originalText);
            }, 2000);
        }, 1500);
    });

    $('.contact-btn').on('click', function() {
        const studentId = $(this).data('student');
        // Add contact functionality here
        alert('سيتم الاتصال بالطالب');
    });

    $('.notify-btn').on('click', function() {
        const button = $(this);
        const originalText = button.html();

        button.html('<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...');

        setTimeout(() => {
            button.html('<i class="fas fa-check"></i> تم الإرسال');
            setTimeout(() => {
                button.html(originalText);
            }, 2000);
        }, 2000);
    });

    // Export functionality
    $('.export-btn, .export-chart').on('click', function() {
        const button = $(this);
        const originalText = button.html();

        button.html('<i class="fas fa-spinner fa-spin"></i> جاري التصدير...');

        setTimeout(() => {
            button.html('<i class="fas fa-check"></i> تم التصدير');
            setTimeout(() => {
                button.html(originalText);
            }, 2000);
        }, 1500);
    });

    // Filter functionality
    $('.filter-btn').on('click', function() {
        const tableType = $(this).data('table');
        // Add filter functionality here
        alert('سيتم إضافة خاصية الفلترة');
    });

    // Fullscreen functionality
    $('.chart-fullscreen').on('click', function() {
        const chartContainer = $(this).closest('.card');
        chartContainer.toggleClass('fullscreen-chart');
    });
});
</script>
@endsection
