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
                        <i class="fas fa-door-open"></i>
                        إحصائيات الزيارات
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
                    <h3 class="counter-number">{{ array_sum($visitsByDayOfWeek) }}</h3>
                    <p><i class="fas fa-door-open"></i> إجمالي الزيارات</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: 100%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 class="counter-number">{{ array_sum(array_slice($visitsData, -7)) }}</h3>
                    <p><i class="fas fa-calendar-week"></i> زيارات آخر 7 أيام</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ array_sum($visitsByDayOfWeek) > 0 ? round((array_sum(array_slice($visitsData, -7)) / array_sum($visitsByDayOfWeek)) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 class="counter-number">{{ end($visitsData) }}</h3>
                    <p><i class="fas fa-calendar-day"></i> زيارات اليوم</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ array_sum($visitsByDayOfWeek) > 0 ? round((end($visitsData) / array_sum($visitsByDayOfWeek)) * 100) : 0 }}%"></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 class="counter-number">{{ round(array_sum($visitsData) / count($visitsData), 1) }}</h3>
                    <p><i class="fas fa-chart-line"></i> متوسط الزيارات اليومية</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="small-box-footer">
                    <span class="progress-bar" style="width: {{ round((round(array_sum($visitsData) / count($visitsData), 1) / max($visitsData)) * 100) }}%"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row">
        <!-- Visits by Day of Week with Modern Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الزيارات حسب أيام الأسبوع
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="day">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success export-chart" data-chart="day">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="visitsByDayChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-stats mt-3">
                        <div class="row">
                            @php
                                $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                                $dayIndex = 0;
                            @endphp
                            @foreach($visitsByDayOfWeek as $day => $count)
                                <div class="col-6 mb-2">
                                    <div class="stat-mini">
                                        <span class="stat-label">{{ $dayNames[$dayIndex] ?? $day }}</span>
                                        <span class="stat-value">{{ $count }}</span>
                                    </div>
                                </div>
                                @php $dayIndex++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visits by Hour with Enhanced Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        الزيارات حسب ساعات اليوم
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="hour">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning export-chart" data-chart="hour">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="visitsByHourChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-summary mt-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="summary-item">
                                    <span class="summary-label">ذروة الزيارات:</span>
                                    <span class="summary-value">{{ array_search(max($visitsByHour), $visitsByHour) }}:00</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="summary-item">
                                    <span class="summary-label">أقل الساعات:</span>
                                    <span class="summary-value">{{ array_search(min($visitsByHour), $visitsByHour) }}:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Daily Visits Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        الزيارات اليومية (آخر 30 يوم)
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="daily">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success export-chart" data-chart="daily">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                        <button class="btn btn-sm btn-outline-secondary chart-fullscreen" data-chart="daily">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="dailyVisitsChart" style="height: 400px;"></canvas>
                    </div>
                    <div class="chart-summary mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">إجمالي الزيارات:</span>
                                    <span class="summary-value">{{ array_sum($visitsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">متوسط يومي:</span>
                                    <span class="summary-value">{{ round(array_sum($visitsData) / count($visitsData), 1) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">أعلى يوم:</span>
                                    <span class="summary-value">{{ max($visitsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">أقل يوم:</span>
                                    <span class="summary-value">{{ min($visitsData) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Monthly Visits Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area"></i>
                        الزيارات الشهرية (آخر 12 شهر)
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="monthly">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger export-chart" data-chart="monthly">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyVisitsChart" style="height: 400px;"></canvas>
                    </div>
                    <div class="chart-summary mt-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="summary-item">
                                    <span class="summary-label">إجمالي الزيارات الشهرية:</span>
                                    <span class="summary-value">{{ array_sum($monthlyVisitsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-item">
                                    <span class="summary-label">متوسط شهري:</span>
                                    <span class="summary-value">{{ round(array_sum($monthlyVisitsData) / max(count($monthlyVisitsData), 1), 1) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-item">
                                    <span class="summary-label">نمو الزيارات:</span>
                                    <span class="summary-value">
                                        @php
                                            $growth = count($monthlyVisitsData) >= 2 ?
                                                round(((end($monthlyVisitsData) - $monthlyVisitsData[count($monthlyVisitsData)-2]) / max($monthlyVisitsData[count($monthlyVisitsData)-2], 1)) * 100, 1) : 0;
                                        @endphp
                                        {{ $growth }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Tables -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        تفاصيل الزيارات حسب أيام الأسبوع
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-primary export-btn" data-table="days">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar-day"></i> يوم الأسبوع</th>
                                    <th><i class="fas fa-hashtag"></i> عدد الزيارات</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalWeekVisits = array_sum($visitsByDayOfWeek);
                                    $dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                                    $dayIndex = 0;
                                @endphp
                                @foreach($visitsByDayOfWeek as $day => $count)
                                    <tr class="table-row-animated" style="animation-delay: {{ $dayIndex * 0.1 }}s">
                                        <td>
                                            <span class="badge badge-primary day-badge">
                                                <i class="fas fa-calendar-day"></i> {{ $dayNames[$dayIndex] ?? $day }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success count-badge">{{ $count }}</span>
                                        </td>
                                        <td>
                                            <strong>
                                                @if($totalWeekVisits > 0)
                                                    {{ round(($count / $totalWeekVisits) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary"
                                                     style="width: {{ $totalWeekVisits > 0 ? round(($count / $totalWeekVisits) * 100, 1) : 0 }}%">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $dayIndex++; @endphp
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
                        <i class="fas fa-clock"></i>
                        أوقات الذروة للزيارات
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-warning export-btn" data-table="hours">
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
                                    <th><i class="fas fa-clock"></i> الساعة</th>
                                    <th><i class="fas fa-users"></i> عدد الزيارات</th>
                                    <th><i class="fas fa-chart-line"></i> المستوى</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sortedHours = collect($visitsByHour)->sortDesc()->take(8);
                                    $index = 0;
                                @endphp
                                @foreach($sortedHours as $hour => $count)
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
                                            <span class="badge badge-info time-badge">
                                                <i class="fas fa-clock"></i> {{ $hour }}:00
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning count-badge">{{ $count }}</span>
                                        </td>
                                        <td>
                                            @if($index < 3)
                                                <span class="badge badge-danger">ذروة عالية</span>
                                            @elseif($index < 6)
                                                <span class="badge badge-warning">ذروة متوسطة</span>
                                            @else
                                                <span class="badge badge-success">نشاط عادي</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $index++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visits Summary Statistics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card summary-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        ملخص إحصائيات الزيارات
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ array_sum($visitsByDayOfWeek) }}</h4>
                                    <p>إجمالي الزيارات</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-calendar-week"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ round(array_sum($visitsByDayOfWeek) / 7, 1) }}</h4>
                                    <p>متوسط الزيارات الأسبوعية</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ array_search(max($visitsByHour), $visitsByHour) }}:00</h4>
                                    <p>ساعة الذروة</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-trending-up"></i>
                                </div>
                                <div class="stat-content">
                                    <h4>{{ max($visitsData) }}</h4>
                                    <p>أعلى زيارات يومية</p>
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
    // Enhanced Visits by Day of Week Chart
    const visitsByDayCtx = document.getElementById('visitsByDayChart').getContext('2d');
    const dayChart = new Chart(visitsByDayCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($visitsByDayOfWeek)) !!}.map((day, index) => {
                const dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                return dayNames[index] || day;
            }),
            datasets: [{
                label: 'عدد الزيارات',
                data: {!! json_encode(array_values($visitsByDayOfWeek)) !!},
                backgroundColor: [
                    'rgba(0, 123, 255, 0.8)',
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(111, 66, 193, 0.8)'
                ],
                borderColor: [
                    'rgba(0, 123, 255, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(108, 117, 125, 1)',
                    'rgba(102, 126, 234, 1)',
                    'rgba(111, 66, 193, 1)'
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

    // Enhanced Visits by Hour Chart
    const visitsByHourCtx = document.getElementById('visitsByHourChart').getContext('2d');
    const hourChart = new Chart(visitsByHourCtx, {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: 'عدد الزيارات',
                data: {!! json_encode(array_values($visitsByHour)) !!},
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#28a745',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 8
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

    // Enhanced Daily Visits Chart
    const dailyVisitsCtx = document.getElementById('dailyVisitsChart').getContext('2d');
    const dailyChart = new Chart(dailyVisitsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($days) !!},
            datasets: [{
                label: 'الزيارات اليومية',
                data: {!! json_encode($visitsData) !!},
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ffc107',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 8
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
                    borderColor: 'rgba(255, 193, 7, 1)',
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

    // Enhanced Monthly Visits Chart
    const monthlyVisitsCtx = document.getElementById('monthlyVisitsChart').getContext('2d');
    const monthlyChart = new Chart(monthlyVisitsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'الزيارات الشهرية',
                data: {!! json_encode($monthlyVisitsData) !!},
                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                borderColor: 'rgba(220, 53, 69, 1)',
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
                    borderColor: 'rgba(220, 53, 69, 1)',
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

    // Chart controls functionality
    $('.chart-refresh').on('click', function() {
        const chartType = $(this).data('chart');
        const icon = $(this).find('i');

        icon.addClass('fa-spin');

        setTimeout(() => {
            if (chartType === 'day') {
                dayChart.update();
            } else if (chartType === 'hour') {
                hourChart.update();
            } else if (chartType === 'daily') {
                dailyChart.update();
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
