@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i>
                        إحصائيات الامتحانات
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        @foreach($examsByType as $type => $count)
            <div class="col-lg-3 col-6">
                <div class="small-box
                    @if($type == 'final') bg-danger
                    @elseif($type == 'midterm') bg-warning
                    @elseif($type == 'quiz') bg-info
                    @else bg-success
                    @endif">
                    <div class="inner">
                        <h3>{{ $count }}</h3>
                        <p>
                            @if($type == 'final') امتحانات نهائية
                            @elseif($type == 'midterm') امتحانات نصف الفصل
                            @elseif($type == 'quiz') اختبارات قصيرة
                            @elseif($type == 'assignment') واجبات
                            @else {{ $type }}
                            @endif
                        </p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Exams by Type -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        الامتحانات حسب النوع
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="examsByTypeChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Exams by Semester -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-doughnut"></i>
                        الامتحانات حسب الفصل
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="examsBySemesterChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- More Charts -->
    <div class="row mb-4">
        <!-- Exams by Department -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الامتحانات حسب القسم
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="examsByDepartmentChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Exams by Level -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الامتحانات حسب المستوى
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="examsByLevelChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Exams Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        إضافة الامتحانات شهرياً
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyExamsChart" style="height: 300px;"></canvas>
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
    // Exams by Type Pie Chart
    const typeCtx = document.getElementById('examsByTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($examsByType->toArray())) !!}.map(type => {
                switch(type) {
                    case 'final': return 'نهائي';
                    case 'midterm': return 'نصف الفصل';
                    case 'quiz': return 'اختبار قصير';
                    case 'assignment': return 'واجب';
                    default: return type;
                }
            }),
            datasets: [{
                data: {!! json_encode(array_values($examsByType->toArray())) !!},
                backgroundColor: ['#dc3545', '#ffc107', '#17a2b8', '#28a745'],
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

    // Exams by Semester Doughnut Chart
    const semesterCtx = document.getElementById('examsBySemesterChart').getContext('2d');
    new Chart(semesterCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($examsBySemester->toArray())) !!}.map(semester => {
                switch(semester) {
                    case 'first': return 'الفصل الأول';
                    case 'second': return 'الفصل الثاني';
                    case 'summer': return 'الفصل الصيفي';
                    default: return semester;
                }
            }),
            datasets: [{
                data: {!! json_encode(array_values($examsBySemester->toArray())) !!},
                backgroundColor: ['#007bff', '#28a745', '#ffc107'],
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

    // Exams by Department Bar Chart
    const departmentCtx = document.getElementById('examsByDepartmentChart').getContext('2d');
    new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($examsByDepartment->pluck('dept')) !!},
            datasets: [{
                label: 'عدد الامتحانات',
                data: {!! json_encode($examsByDepartment->pluck('total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Exams by Level Bar Chart
    const levelCtx = document.getElementById('examsByLevelChart').getContext('2d');
    new Chart(levelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($examsByLevel->pluck('level')) !!},
            datasets: [{
                label: 'عدد الامتحانات',
                data: {!! json_encode($examsByLevel->pluck('total')) !!},
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Monthly Exams Line Chart
    const monthlyCtx = document.getElementById('monthlyExamsChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'امتحانات مضافة',
                data: {!! json_encode($examsData) !!},
                borderColor: '#6f42c1',
                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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

