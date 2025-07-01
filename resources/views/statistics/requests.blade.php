@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ array_sum($requestsByType->toArray()) }}</h3>
                    <p>إجمالي الطلبات</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $requestsByType['reading'] ?? 0 }}</h3>
                    <p>طلبات قراءة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $requestsByType['borrowing'] ?? 0 }}</h3>
                    <p>طلبات استعارة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ round($avgProcessingTime, 1) }}</h3>
                    <p>متوسط وقت المعالجة (أيام)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fas fa-hourglass-half"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات معلقة</span>
                    <span class="info-box-number">{{ $requestsByStatus['pending'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fas fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات موافق عليها</span>
                    <span class="info-box-number">{{ $requestsByStatus['approved'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fas fa-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات مرفوضة</span>
                    <span class="info-box-number">{{ $requestsByStatus['rejected'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Requests by Type -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        الطلبات حسب النوع
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="requestsByTypeChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Requests by Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-doughnut"></i>
                        الطلبات حسب الحالة
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="requestsByStatusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Requests Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        الطلبات الشهرية (آخر 12 شهر)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyRequestsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Summary Table -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        ملخص الطلبات حسب النوع
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>نوع الطلب</th>
                                    <th>العدد</th>
                                    <th>النسبة المئوية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalRequests = array_sum($requestsByType->toArray());
                                @endphp
                                @foreach($requestsByType as $type => $count)
                                    <tr>
                                        <td>
                                            @if($type == 'reading')
                                                <span class="badge badge-success">قراءة</span>
                                            @elseif($type == 'borrowing')
                                                <span class="badge badge-warning">استعارة</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $type }}</span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-primary">{{ $count }}</span></td>
                                        <td>
                                            @if($totalRequests > 0)
                                                {{ round(($count / $totalRequests) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        ملخص الطلبات حسب الحالة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>حالة الطلب</th>
                                    <th>العدد</th>
                                    <th>النسبة المئوية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalStatusRequests = array_sum($requestsByStatus->toArray());
                                @endphp
                                @foreach($requestsByStatus as $status => $count)
                                    <tr>
                                        <td>
                                            @if($status == 'pending')
                                                <span class="badge badge-warning">معلق</span>
                                            @elseif($status == 'approved')
                                                <span class="badge badge-success">موافق عليه</span>
                                            @elseif($status == 'rejected')
                                                <span class="badge badge-danger">مرفوض</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $status }}</span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-primary">{{ $count }}</span></td>
                                        <td>
                                            @if($totalStatusRequests > 0)
                                                {{ round(($count / $totalStatusRequests) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
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
<script>
$(function() {
    // Requests by Type Pie Chart
    const requestsByTypeCtx = document.getElementById('requestsByTypeChart').getContext('2d');
    new Chart(requestsByTypeCtx, {
        type: 'pie',
        data: {
            labels: ['قراءة', 'استعارة'],
            datasets: [{
                data: [{{ $requestsByType['reading'] ?? 0 }}, {{ $requestsByType['borrowing'] ?? 0 }}],
                backgroundColor: ['#28a745', '#ffc107'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Requests by Status Doughnut Chart
    const requestsByStatusCtx = document.getElementById('requestsByStatusChart').getContext('2d');
    new Chart(requestsByStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['معلق', 'موافق عليه', 'مرفوض'],
            datasets: [{
                data: [
                    {{ $requestsByStatus['pending'] ?? 0 }},
                    {{ $requestsByStatus['approved'] ?? 0 }},
                    {{ $requestsByStatus['rejected'] ?? 0 }}
                ],
                backgroundColor: ['#ffc107', '#28a745', '#dc3545'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Monthly Requests Line Chart
    const monthlyRequestsCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
    new Chart(monthlyRequestsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'طلبات قراءة',
                    data: {!! json_encode($readingData) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'طلبات استعارة',
                    data: {!! json_encode($borrowingData) !!},
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }
            ]
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

