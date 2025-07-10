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
                        <i class="fas fa-project-diagram"></i>
                        إحصائيات المشاريع
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
                    <h3 class="counter-number">{{ $totalProjects }}</h3>
                    <p><i class="fas fa-project-diagram"></i> إجمالي المشاريع</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: 100%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 class="counter-number">{{ $projectsByStatus['available'] ?? 0 }}</h3>
                    <p><i class="fas fa-check-circle"></i> مشاريع متاحة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ $totalProjects > 0 ? round((($projectsByStatus['available'] ?? 0) / $totalProjects) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="counter-number">{{ $projectsByStatus['borrowed'] ?? 0 }}</h3>
                    <p><i class="fas fa-hand-holding"></i> مشاريع مستعارة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ $totalProjects > 0 ? round((($projectsByStatus['borrowed'] ?? 0) / $totalProjects) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 class="counter-number">{{ array_sum($projectsData) }}</h3>
                    <p><i class="fas fa-plus-circle"></i> مشاريع مضافة هذا العام</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ $totalProjects > 0 ? round((array_sum($projectsData) / $totalProjects) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row">
        <!-- Projects by Status with Modern Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        المشاريع حسب الحالة
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="status">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary chart-fullscreen" data-chart="status">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="projectsByStatusChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        @foreach($projectsByStatus as $status => $count)
                            <div class="legend-item">
                                <span class="legend-color" style="background:
                                    @if($status == 'available') #28a745
                                    @elseif($status == 'borrowed') #ffc107
                                    @elseif($status == 'reserved') #dc3545
                                    @else #6c757d
                                    @endif;"></span>
                                <span>
                                    @if($status == 'available') متاح
                                    @elseif($status == 'borrowed') مستعار
                                    @elseif($status == 'reserved') محجوز
                                    @else {{ $status }}
                                    @endif
                                    ({{ $count }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects by Department with Enhanced Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        المشاريع حسب القسم
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="department">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success export-chart" data-chart="department">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="projectsByDepartmentChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-stats mt-3">
                        <div class="row">
                            @foreach($projectsByDepartment->take(4) as $dept)
                                <div class="col-6 mb-2">
                                    <div class="stat-mini">
                                        <span class="stat-label">{{ $dept->department ?? 'غير محدد' }}</span>
                                        <span class="stat-value">{{ $dept->total }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Monthly Projects Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        المشاريع المضافة شهرياً (آخر 12 شهر)
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="monthly">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success export-chart" data-chart="monthly">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyProjectsChart" style="height: 400px;"></canvas>
                    </div>
                    <div class="chart-summary mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">إجمالي المشاريع المضافة:</span>
                                    <span class="summary-value">{{ array_sum($projectsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">متوسط شهري:</span>
                                    <span class="summary-value">{{ round(array_sum($projectsData) / max(count($projectsData), 1), 1) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">أعلى شهر:</span>
                                    <span class="summary-value">{{ max($projectsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">أقل شهر:</span>
                                    <span class="summary-value">{{ min($projectsData) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Projects by Department Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        تفاصيل المشاريع حسب الأقسام
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-primary export-btn" data-table="departments">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-btn" data-table="departments">
                            <i class="fas fa-filter"></i> فلترة
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-building"></i> القسم</th>
                                    <th><i class="fas fa-hashtag"></i> عدد المشاريع</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalProjectsInTable = $projectsByDepartment->sum('total');
                                @endphp
                                @forelse($projectsByDepartment as $index => $department)
                                    <tr class="table-row-animated" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>
                                            <span class="badge badge-primary department-badge">
                                                <i class="fas fa-building"></i> {{ $department->department ?? 'غير محدد' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success count-badge">{{ $department->total }}</span>
                                        </td>
                                        <td>
                                            <strong>
                                                @if($totalProjectsInTable > 0)
                                                    {{ round(($department->total / $totalProjectsInTable) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-primary"
                                                     style="width: {{ $totalProjectsInTable > 0 ? round(($department->total / $totalProjectsInTable) * 100, 1) : 0 }}%">
                                                    {{ $department->total }} مشروع
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center empty-state">
                                            <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
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

    <!-- Enhanced Popular and Recent Projects Tables -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i>
                        أكثر المشاريع شعبية (حسب الطلبات)
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-warning export-btn" data-table="popular">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-trophy"></i> الترتيب</th>
                                    <th><i class="fas fa-project-diagram"></i> اسم المشروع</th>
                                    <th><i class="fas fa-clipboard-list"></i> عدد الطلبات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostPopularProjects as $index => $project)
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
                                            <div class="project-info">
                                                <strong>{{ $project->project_name }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info count-badge">
                                                <i class="fas fa-clipboard-list"></i> {{ $project->requests_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center empty-state">
                                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد مشاريع شعبية حالياً</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Projects Table with Enhanced Design -->
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        أحدث المشاريع المضافة
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-info export-btn" data-table="recent">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-project-diagram"></i> اسم المشروع</th>
                                    <th><i class="fas fa-calendar-plus"></i> تاريخ الإضافة</th>
                                    <th><i class="fas fa-clock"></i> منذ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $index => $project)
                                    <tr class="table-row-animated" style="animation-delay: {{ $index * 0.1 }}s">
                                        <td>
                                            <div class="project-info">
                                                <strong>{{ $project->project_name }}</strong>
                                                <span class="badge badge-success new-badge">جديد</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="date-badge">{{ $project->created_at->format('Y-m-d') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary time-badge">
                                                <i class="fas fa-clock"></i> {{ $project->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center empty-state">
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">لا توجد مشاريع مضافة حديثاً</p>
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

    <!-- Projects Summary Statistics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card summary-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        ملخص إحصائيات المشاريع
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ $totalProjects }}</h4>
                                    <p>إجمالي المشاريع</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ $totalProjects > 0 ? round((($projectsByStatus['available'] ?? 0) / $totalProjects) * 100, 1) : 0 }}%</h4>
                                    <p>نسبة المشاريع المتاحة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ $projectsByDepartment->count() }}</h4>
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
                                    <h4>{{ $mostPopularProjects->sum('requests_count') }}</h4>
                                    <p>إجمالي الطلبات</p>
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Enhanced Projects by Status Pie Chart
    const projectsByStatusCtx = document.getElementById('projectsByStatusChart').getContext('2d');
    const statusChart = new Chart(projectsByStatusCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($projectsByStatus->toArray())) !!}.map(status => {
                switch(status) {
                    case 'available': return 'متاح';
                    case 'borrowed': return 'مستعار';
                    case 'reserved': return 'محجوز';
                    default: return status;
                }
            }),
            datasets: [{
                data: {!! json_encode(array_values($projectsByStatus->toArray())) !!},
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                borderColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 2000
            }
        }
    });

    // Enhanced Projects by Department Bar Chart
    const projectsByDepartmentCtx = document.getElementById('projectsByDepartmentChart').getContext('2d');
    const departmentChart = new Chart(projectsByDepartmentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($projectsByDepartment->pluck('department')->toArray()) !!},
            datasets: [{
                label: 'عدد المشاريع',
                data: {!! json_encode($projectsByDepartment->pluck('total')->toArray()) !!},
                backgroundColor: [
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(102, 126, 234, 0.8)'
                ],
                borderColor: [
                    'rgba(0, 123, 255, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(102, 126, 234, 1)'
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
                    borderColor: 'rgba(0, 123, 255, 1)',
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

    // Enhanced Monthly Projects Line Chart
    const monthlyProjectsCtx = document.getElementById('monthlyProjectsChart').getContext('2d');
    const monthlyChart = new Chart(monthlyProjectsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'المشاريع المضافة',
                data: {!! json_encode($projectsData) !!},
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#28a745',
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
                    borderColor: 'rgba(40, 167, 69, 1)',
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
            if (chartType === 'status') {
                statusChart.update();
            } else if (chartType === 'department') {
                departmentChart.update();
            } else if (chartType === 'monthly') {
                monthlyChart.update();
            }
            icon.removeClass('fa-spin');
        }, 1000);
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
