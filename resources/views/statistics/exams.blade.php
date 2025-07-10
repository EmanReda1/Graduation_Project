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

    <!-- Enhanced Summary Cards -->
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
                        <h3 class="counter-number">{{ $count }}</h3>
                        <p>
                            @if($type == 'final')
                                <i class="fas fa-graduation-cap"></i> امتحانات نهائية
                            @elseif($type == 'midterm')
                                <i class="fas fa-clipboard-check"></i> امتحانات نصف الفصل
                            @elseif($type == 'quiz')
                                <i class="fas fa-question-circle"></i> اختبارات قصيرة
                            @elseif($type == 'assignment')
                                <i class="fas fa-tasks"></i> واجبات
                            @else
                                <i class="fas fa-file-alt"></i> {{ $type }}
                            @endif
                        </p>
                    </div>
                    <div class="icon">
                        @if($type == 'final')
                            <i class="fas fa-graduation-cap"></i>
                        @elseif($type == 'midterm')
                            <i class="fas fa-clipboard-check"></i>
                        @elseif($type == 'quiz')
                            <i class="fas fa-question-circle"></i>
                        @elseif($type == 'assignment')
                            <i class="fas fa-tasks"></i>
                        @else
                            <i class="fas fa-file-alt"></i>
                        @endif
                    </div>
                    <div class="small-box-footer">
                        <span class="progress-bar" style="width: {{ array_sum($examsByType->toArray()) > 0 ? round(($count / array_sum($examsByType->toArray())) * 100) : 0 }}%"></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row mb-4">
        <!-- Exams by Type with Modern Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        الامتحانات حسب النوع
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="type">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="examsByTypeChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        @foreach($examsByType as $type => $count)
                            <div class="legend-item">
                                <span class="legend-color" style="background:
                                    @if($type == 'final') #dc3545
                                    @elseif($type == 'midterm') #ffc107
                                    @elseif($type == 'quiz') #17a2b8
                                    @else #28a745
                                    @endif;"></span>
                                <span>
                                    @if($type == 'final') نهائي
                                    @elseif($type == 'midterm') نصف الفصل
                                    @elseif($type == 'quiz') اختبار قصير
                                    @elseif($type == 'assignment') واجب
                                    @else {{ $type }}
                                    @endif
                                    ({{ $count }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Exams by Semester with Enhanced Design -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-doughnut"></i>
                        الامتحانات حسب الفصل
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary chart-refresh" data-chart="semester">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="examsBySemesterChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-legend mt-3">
                        @foreach($examsBySemester as $semester => $count)
                            <div class="legend-item">
                                <span class="legend-color" style="background:
                                    @if($semester == 'first') #007bff
                                    @elseif($semester == 'second') #28a745
                                    @else #ffc107
                                    @endif;"></span>
                                <span>
                                    @if($semester == 'first') الفصل الأول
                                    @elseif($semester == 'second') الفصل الثاني
                                    @elseif($semester == 'summer') الفصل الصيفي
                                    @else {{ $semester }}
                                    @endif
                                    ({{ $count }})
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced More Charts -->
    <div class="row mb-4">
        <!-- Exams by Department -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الامتحانات حسب القسم
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-success export-chart" data-chart="department">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="examsByDepartmentChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-stats mt-3">
                        <div class="row">
                            @foreach($examsByDepartment->take(4) as $dept)
                                <div class="col-6 mb-2">
                                    <div class="stat-mini">
                                        <span class="stat-label">{{ $dept->dept }}</span>
                                        <span class="stat-value">{{ $dept->total }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exams by Level -->
        <div class="col-md-6">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الامتحانات حسب المستوى
                    </h3>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-warning export-chart" data-chart="level">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="examsByLevelChart" style="height: 300px;"></canvas>
                    </div>
                    <div class="chart-stats mt-3">
                        <div class="row">
                            @foreach($examsByLevel->take(4) as $level)
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
    </div>

    <!-- Enhanced Monthly Exams Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        إضافة الامتحانات شهرياً
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
                        <canvas id="monthlyExamsChart" style="height: 400px;"></canvas>
                    </div>
                    <div class="chart-summary mt-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">إجمالي الامتحانات:</span>
                                    <span class="summary-value">{{ array_sum($examsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">متوسط شهري:</span>
                                    <span class="summary-value">{{ round(array_sum($examsData) / max(count($examsData), 1), 1) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">أعلى شهر:</span>
                                    <span class="summary-value">{{ max($examsData) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="summary-item">
                                    <span class="summary-label">أقل شهر:</span>
                                    <span class="summary-value">{{ min($examsData) }}</span>
                                </div>
                            </div>
                        </div>
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
                        ملخص الامتحانات حسب النوع
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
                                    <th><i class="fas fa-tag"></i> نوع الامتحان</th>
                                    <th><i class="fas fa-hashtag"></i> العدد</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalExams = array_sum($examsByType->toArray());
                                @endphp
                                @foreach($examsByType as $type => $count)
                                    <tr class="table-row-animated">
                                        <td>
                                            @if($type == 'final')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-graduation-cap"></i> نهائي
                                                </span>
                                            @elseif($type == 'midterm')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clipboard-check"></i> نصف الفصل
                                                </span>
                                            @elseif($type == 'quiz')
                                                <span class="badge badge-info">
                                                    <i class="fas fa-question-circle"></i> اختبار قصير
                                                </span>
                                            @elseif($type == 'assignment')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-tasks"></i> واجب
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
                                                @if($totalExams > 0)
                                                    {{ round(($count / $totalExams) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar
                                                    @if($type == 'final') bg-danger
                                                    @elseif($type == 'midterm') bg-warning
                                                    @elseif($type == 'quiz') bg-info
                                                    @else bg-success
                                                    @endif"
                                                     style="width: {{ $totalExams > 0 ? round(($count / $totalExams) * 100, 1) : 0 }}%">
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
                        ملخص الامتحانات حسب الفصل
                    </h3>
                    <div class="table-controls">
                        <button class="btn btn-sm btn-outline-primary export-btn" data-table="semester">
                            <i class="fas fa-download"></i> تصدير
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped modern-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar"></i> الفصل الدراسي</th>
                                    <th><i class="fas fa-hashtag"></i> العدد</th>
                                    <th><i class="fas fa-percentage"></i> النسبة المئوية</th>
                                    <th><i class="fas fa-chart-bar"></i> التمثيل البصري</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalSemesterExams = array_sum($examsBySemester->toArray());
                                @endphp
                                @foreach($examsBySemester as $semester => $count)
                                    <tr class="table-row-animated">
                                        <td>
                                            @if($semester == 'first')
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-calendar-alt"></i> الفصل الأول
                                                </span>
                                            @elseif($semester == 'second')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-calendar-alt"></i> الفصل الثاني
                                                </span>
                                            @elseif($semester == 'summer')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-sun"></i> الفصل الصيفي
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">{{ $semester }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-primary count-badge">{{ $count }}</span>
                                        </td>
                                        <td>
                                            <strong>
                                                @if($totalSemesterExams > 0)
                                                    {{ round(($count / $totalSemesterExams) * 100, 1) }}%
                                                @else
                                                    0%
                                                @endif
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar
                                                    @if($semester == 'first') bg-primary
                                                    @elseif($semester == 'second') bg-success
                                                    @else bg-warning
                                                    @endif"
                                                     style="width: {{ $totalSemesterExams > 0 ? round(($count / $totalSemesterExams) * 100, 1) : 0 }}%">
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
<script>
$(function() {
    // Enhanced Exams by Type Pie Chart
    const typeCtx = document.getElementById('examsByTypeChart').getContext('2d');
    const typeChart = new Chart(typeCtx, {
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
                borderColor: ['#dc3545', '#ffc107', '#17a2b8', '#28a745'],
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
                    displayColors: true
                }
            },
            animation: {
                animateRotate: true,
                duration: 2000
            }
        }
    });

    // Enhanced Exams by Semester Doughnut Chart
    const semesterCtx = document.getElementById('examsBySemesterChart').getContext('2d');
    const semesterChart = new Chart(semesterCtx, {
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
                borderColor: ['#007bff', '#28a745', '#ffc107'],
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
                    displayColors: true
                }
            },
            animation: {
                animateRotate: true,
                duration: 2000
            }
        }
    });

    // Enhanced Exams by Department Bar Chart
    const departmentCtx = document.getElementById('examsByDepartmentChart').getContext('2d');
    const departmentChart = new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($examsByDepartment->pluck('dept')) !!},
            datasets: [{
                label: 'عدد الامتحانات',
                data: {!! json_encode($examsByDepartment->pluck('total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
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
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(54, 162, 235, 1)',
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

    // Enhanced Exams by Level Bar Chart
    const levelCtx = document.getElementById('examsByLevelChart').getContext('2d');
    const levelChart = new Chart(levelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($examsByLevel->pluck('level')) !!},
            datasets: [{
                label: 'عدد الامتحانات',
                data: {!! json_encode($examsByLevel->pluck('total')) !!},
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgba(255, 193, 7, 1)',
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
                    display: false
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

    // Enhanced Monthly Exams Line Chart
    const monthlyCtx = document.getElementById('monthlyExamsChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'امتحانات مضافة',
                data: {!! json_encode($examsData) !!},
                borderColor: '#6f42c1',
                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#6f42c1',
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
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(111, 66, 193, 1)',
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
            if (chartType === 'type') {
                typeChart.update();
            } else if (chartType === 'semester') {
                semesterChart.update();
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
});
</script>
@endsection
