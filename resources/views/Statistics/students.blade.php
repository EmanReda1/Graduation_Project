@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $studentsByLevel->sum('total') }}</h3>
                    <p>إجمالي الطلاب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $mostActiveStudentsByVisits->count() }}</h3>
                    <p>طلاب نشطون (زيارات)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $mostActiveStudentsByRequests->count() }}</h3>
                    <p>طلاب نشطون (طلبات)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $studentsWithOverdueBooks->count() }}</h3>
                    <p>طلاب متأخرون</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Students by Level -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الطلاب حسب المستوى
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="studentsByLevelChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- New Students by Month -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        الطلاب الجدد شهرياً
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="newStudentsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Students Tables -->
    <div class="row mt-4">
        <!-- Most Active by Visits -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i>
                        أكثر الطلاب نشاطاً (الزيارات)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>المستوى</th>
                                    <th>عدد الزيارات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostActiveStudentsByVisits as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->fullname ?? $student->username }}</td>
                                        <td>{{ $student->level }}</td>
                                        <td>
                                            <span class="badge badge-success">{{ $student->visits_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد بيانات</td>
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i>
                        أكثر الطلاب نشاطاً (الطلبات)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>المستوى</th>
                                    <th>عدد الطلبات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostActiveStudentsByRequests as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->fullname ?? $student->username }}</td>
                                        <td>{{ $student->level }}</td>
                                        <td>
                                            <span class="badge badge-warning">{{ $student->requests_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students with Overdue Books -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        الطلاب المتأخرون في إرجاع الكتب
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>المستوى</th>
                                    <th>الكتب المتأخرة</th>
                                    <th>أيام التأخير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studentsWithOverdueBooks as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student->fullname ?? $student->username }}</td>
                                        <td>{{ $student->level }}</td>
                                        <td>
                                            @if($student->bookRequests)
                                                @foreach($student->bookRequests as $request)
                                                    <span class="badge badge-danger mb-1">
                                                        {{ $request->book->book_name ?? 'كتاب غير محدد' }}
                                                    </span><br>
                                                @endforeach
                                            @else
                                                <span class="text-muted">لا توجد بيانات</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($student->bookRequests && $student->bookRequests->first())
                                                @php
                                                    $daysDiff = \Carbon\Carbon::parse($student->bookRequests->first()->date_of_request)->diffInDays(\Carbon\Carbon::now());
                                                @endphp
                                                <span class="badge badge-danger">{{ $daysDiff }} يوم</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i>
                                            لا يوجد طلاب متأخرون
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

    <!-- Students by Level Table -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table"></i>
                        تفاصيل الطلاب حسب المستوى
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>المستوى</th>
                                    <th>عدد الطلاب</th>
                                    <th>النسبة المئوية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalStudents = $studentsByLevel->sum('total');
                                @endphp
                                @forelse($studentsByLevel as $level)
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary">المستوى {{ $level->level }}</span>
                                        </td>
                                        <td><span class="badge badge-success">{{ $level->total }}</span></td>
                                        <td>
                                            @if($totalStudents > 0)
                                                {{ round(($level->total / $totalStudents) * 100, 1) }}%
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
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Students by Level Chart
    const studentsByLevelCtx = document.getElementById('studentsByLevelChart').getContext('2d');
    new Chart(studentsByLevelCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($studentsByLevel->pluck('level')->map(function($level) { return 'المستوى ' . $level; })->toArray()) !!},
            datasets: [{
                label: 'عدد الطلاب',
                data: {!! json_encode($studentsByLevel->pluck('total')->toArray()) !!},
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

    // New Students Chart
    const newStudentsCtx = document.getElementById('newStudentsChart').getContext('2d');
    new Chart(newStudentsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [{
                label: 'الطلاب الجدد',
                data: {!! json_encode($newStudentsData) !!},
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

