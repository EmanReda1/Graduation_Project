@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalProjects }}</h3>
                    <p>إجمالي المشاريع</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $projectsByStatus['available'] ?? 0 }}</h3>
                    <p>مشاريع متاحة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $projectsByStatus['borrowed'] ?? 0 }}</h3>
                    <p>مشاريع مستعارة</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ array_sum($projectsData) }}</h3>
                    <p>مشاريع مضافة هذا العام</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Projects by Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        المشاريع حسب الحالة
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="projectsByStatusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Projects by Department -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        المشاريع حسب القسم
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="projectsByDepartmentChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Projects Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        المشاريع المضافة شهرياً (آخر 12 شهر)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyProjectsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects by Department Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        تفاصيل المشاريع حسب الأقسام
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>القسم</th>
                                    <th>عدد المشاريع</th>
                                    <th>النسبة المئوية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalProjectsInTable = $projectsByDepartment->sum('total');
                                @endphp
                                @forelse($projectsByDepartment as $department)
                                    <tr>
                                        <td>{{ $department->department ?? 'غير محدد' }}</td>
                                        <td><span class="badge badge-primary">{{ $department->total }}</span></td>
                                        <td>
                                            @if($totalProjectsInTable > 0)
                                                {{ round(($department->total / $totalProjectsInTable) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Popular Projects Table -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i>
                        أكثر المشاريع شعبية (حسب الطلبات)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>اسم المشروع</th>
                                    <th>عدد الطلبات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostPopularProjects as $project)
                                    <tr>
                                        <td>{{ $project->project_name }}</td>
                                        <td><span class="badge badge-info">{{ $project->requests_count }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">لا توجد مشاريع شعبية حالياً</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Projects Table -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        أحدث المشاريع المضافة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>اسم المشروع</th>
                                    <th>تاريخ الإضافة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                    <tr>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">لا توجد مشاريع مضافة حديثاً</td>
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
<script>
$(function() {
    // Projects by Status Pie Chart
    const projectsByStatusCtx = document.getElementById('projectsByStatusChart').getContext('2d');
    new Chart(projectsByStatusCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($projectsByStatus->toArray())) !!},
            datasets: [{
                data: {!! json_encode(array_values($projectsByStatus->toArray())) !!},
                backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d'],
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

    // Projects by Department Bar Chart
    const projectsByDepartmentCtx = document.getElementById('projectsByDepartmentChart').getContext('2d');
    new Chart(projectsByDepartmentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($projectsByDepartment->pluck('department')->toArray()) !!},
            datasets: [{
                label: 'عدد المشاريع',
                data: {!! json_encode($projectsByDepartment->pluck('total')->toArray()) !!},
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

    // Monthly Projects Line Chart
    const monthlyProjectsCtx = document.getElementById('monthlyProjectsChart').getContext('2d');
    new Chart(monthlyProjectsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'المشاريع المضافة',
                data: {!! json_encode($projectsData) !!},
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
});
</script>
@endsection


