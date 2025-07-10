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
                        <i class="fas fa-clipboard-list"></i>
                        إحصائيات الطلبات
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
                    <h3 class="counter-number">{{ array_sum($requestsByType->toArray()) }}</h3>
                    <p><i class="fas fa-clipboard-list"></i> إجمالي الطلبات</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: 100%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 class="counter-number">{{ $requestsByType['reading'] ?? 0 }}</h3>
                    <p><i class="fas fa-eye"></i> طلبات قراءة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ array_sum($requestsByType->toArray()) > 0 ? round((($requestsByType['reading'] ?? 0) / array_sum($requestsByType->toArray())) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="counter-number">{{ $requestsByType['borrowing'] ?? 0 }}</h3>
                    <p><i class="fas fa-hand-holding"></i> طلبات استعارة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ array_sum($requestsByType->toArray()) > 0 ? round((($requestsByType['borrowing'] ?? 0) / array_sum($requestsByType->toArray())) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 class="counter-number">{{ round($avgProcessingTime, 1) }}</h3>
                    <p><i class="fas fa-clock"></i> متوسط وقت المعالجة (أيام)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ min(round($avgProcessingTime * 10), 100) }}%"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-6">
            <div class="info-box status-box">
                <span class="info-box-icon bg-yellow">
                    <i class="fas fa-hourglass-half"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات معلقة</span>
                    <span class="info-box-number counter-number">{{ $requestsByStatus['pending'] ?? 0 }}</span>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-warning" style="width: {{ array_sum($requestsByStatus->toArray()) > 0 ? round((($requestsByStatus['pending'] ?? 0) / array_sum($requestsByStatus->toArray())) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="info-box status-box">
                <span class="info-box-icon bg-green">
                    <i class="fas fa-check"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات موافق عليها</span>
                    <span class="info-box-number counter-number">{{ $requestsByStatus['approved'] ?? 0 }}</span>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: {{ array_sum($requestsByStatus->toArray()) > 0 ? round((($requestsByStatus['approved'] ?? 0) / array_sum($requestsByStatus->toArray())) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="info-box status-box">
                <span class="info-box-icon bg-red">
                    <i class="fas fa-times"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات مرفوضة</span>
                    <span class="info-box-number counter-number">{{ $requestsByStatus['rejected'] ?? 0 }}</span>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-danger" style="width: {{ array_sum($requestsByStatus->toArray()) > 0 ? round((($requestsByStatus['rejected'] ?? 0) / array_sum($requestsByStatus->toArray())) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row">
        <!-- Requests by Type with Modern Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        الطلبات حسب النوع
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="type">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary chart-fullscreen" data-chart="type">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="requestsByTypeChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        <div class="legend-item">
                            <span class="legend-color" style="background: #10b981;"></span>
                            <span>طلبات القراءة ({{ $requestsByType['reading'] ?? 0 }})</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #f59e0b;"></span>
                            <span>طلبات الاستعارة ({{ $requestsByType['borrowing'] ?? 0 }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Requests by Status with Enhanced Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-doughnut"></i>
                        الطلبات حسب الحالة
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
                        <canvas id="requestsByStatusChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        <div class="legend-item">
                            <span class="legend-color" style="background: #f59e0b;"></span>
                            <span>معلق ({{ $requestsByStatus['pending'] ?? 0 }})</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #10b981;"></span>
                            <span>موافق عليه ({{ $requestsByStatus['approved'] ?? 0 }})</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background: #ef4444;"></span>
                            <span>مرفوض ({{ $requestsByStatus['rejected'] ?? 0 }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Monthly Requests Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        الطلبات الشهرية (آخر 12 شهر)
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
                        <canvas id="monthlyRequestsChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Summary Tables -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        ملخص الطلبات حسب النوع
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-primary export-btn" data-table="type">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-tag"></i> نوع الطلب</th>
                                    <th><i class="fas fa-hashtag"></i> العدد</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalRequests = array_sum($requestsByType->toArray());
                                @endphp
                                @foreach($requestsByType as $type => $count)
                                    <tr class="table-row-animated">
                                        <td>
                                            @if($type == 'reading')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-eye"></i> قراءة
                                                </span>
                                            @elseif($type == 'borrowing')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-hand-holding"></i> استعارة
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">{{ $type }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-primary count-badge">{{ $count }}</span>
                                        </td>
                                        <td>
                                            <strong>
                                                @if($totalRequests > 0)
                                                    {{ round(($count / $totalRequests) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $type == 'reading' ? 'bg-success' : 'bg-warning' }}"
                                                     style="width: {{ $totalRequests > 0 ? round(($count / $totalRequests) * 100, 1) : 0 }}%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        ملخص الطلبات حسب الحالة
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-primary export-btn" data-table="status">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-flag"></i> حالة الطلب</th>
                                    <th><i class="fas fa-hashtag"></i> العدد</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalStatusRequests = array_sum($requestsByStatus->toArray());
                                @endphp
                                @foreach($requestsByStatus as $status => $count)
                                    <tr class="table-row-animated">
                                        <td>
                                            @if($status == 'pending')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-hourglass-half"></i> معلق
                                                </span>
                                            @elseif($status == 'approved')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> موافق عليه
                                                </span>
                                            @elseif($status == 'rejected')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times"></i> مرفوض
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">{{ $status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-primary count-badge">{{ $count }}</span>
                                        </td>
                                        <td>
                                            <strong>
                                                @if($totalStatusRequests > 0)
                                                    {{ round(($count / $totalStatusRequests) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar
                                                    @if($status == 'pending') bg-warning
                                                    @elseif($status == 'approved') bg-success
                                                    @elseif($status == 'rejected') bg-danger
                                                    @else bg-secondary
                                                    @endif"
                                                     style="width: {{ $totalStatusRequests > 0 ? round(($count / $totalStatusRequests) * 100, 1) : 0 }}%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
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
    // Enhanced Requests by Type Pie Chart
    const requestsByTypeCtx = document.getElementById('requestsByTypeChart').getContext('2d');
    const typeChart = new Chart(requestsByTypeCtx, {
        type: 'pie',
        data: {
            labels: ['قراءة', 'استعارة'],
            datasets: [{
                data: [{{ $requestsByType['reading'] ?? 0 }}, {{ $requestsByType['borrowing'] ?? 0 }}],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderColor: [
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)'
                ],
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

    // Enhanced Requests by Status Doughnut Chart
    const requestsByStatusCtx = document.getElementById('requestsByStatusChart').getContext('2d');
    const statusChart = new Chart(requestsByStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['معلق', 'موافق عليه', 'مرفوض'],
            datasets: [{
                data: [
                    {{ $requestsByStatus['pending'] ?? 0 }},
                    {{ $requestsByStatus['approved'] ?? 0 }},
                    {{ $requestsByStatus['rejected'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgba(245, 158, 11, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
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

    // Enhanced Monthly Requests Line Chart
    const monthlyRequestsCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
    const monthlyChart = new Chart(monthlyRequestsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'طلبات قراءة',
                    data: {!! json_encode($readingData) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 10
                },
                {
                    label: 'طلبات استعارة',
                    data: {!! json_encode($borrowingData) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 10
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
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
                    displayColors: true
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
            if (chartType === 'type') {
                typeChart.update();
            } else if (chartType === 'status') {
                statusChart.update();
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

    // Fullscreen functionality
    $('.chart-fullscreen').on('click', function() {
        const chartContainer = $(this).closest('.card');
        chartContainer.toggleClass('fullscreen-chart');
    });
});
</script>
@endsection
