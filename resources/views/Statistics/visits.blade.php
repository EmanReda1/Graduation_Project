@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ array_sum($visitsByDayOfWeek) }}</h3>
                    <p>إجمالي الزيارات</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ array_sum(array_slice($visitsData, -7)) }}</h3>
                    <p>زيارات آخر 7 أيام</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ end($visitsData) }}</h3>
                    <p>زيارات اليوم</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ round(array_sum($visitsData) / count($visitsData), 1) }}</h3>
                    <p>متوسط الزيارات اليومية</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Visits by Day of Week -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الزيارات حسب أيام الأسبوع
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="visitsByDayChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Visits by Hour -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        الزيارات حسب ساعات اليوم
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="visitsByHourChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Visits Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        الزيارات اليومية (آخر 30 يوم)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="dailyVisitsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Visits Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area"></i>
                        الزيارات الشهرية (آخر 12 شهر)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyVisitsChart" style="height: 300px;"></canvas>
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
    // Visits by Day of Week Chart
    const visitsByDayCtx = document.getElementById('visitsByDayChart').getContext('2d');
    new Chart(visitsByDayCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($visitsByDayOfWeek)) !!},
            datasets: [{
                label: 'عدد الزيارات',
                data: {!! json_encode(array_values($visitsByDayOfWeek)) !!},
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Visits by Hour Chart
    const visitsByHourCtx = document.getElementById('visitsByHourChart').getContext('2d');
    new Chart(visitsByHourCtx, {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: 'عدد الزيارات',
                data: {!! json_encode(array_values($visitsByHour)) !!},
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Daily Visits Chart
    const dailyVisitsCtx = document.getElementById('dailyVisitsChart').getContext('2d');
    new Chart(dailyVisitsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($days) !!},
            datasets: [{
                label: 'الزيارات اليومية',
                data: {!! json_encode($visitsData) !!},
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Monthly Visits Chart
    const monthlyVisitsCtx = document.getElementById('monthlyVisitsChart').getContext('2d');
    new Chart(monthlyVisitsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'الزيارات الشهرية',
                data: {!! json_encode($monthlyVisitsData) !!},
                backgroundColor: '#dc3545',
                borderColor: '#c82333',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection

