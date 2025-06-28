@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-book"></i>
                        إحصائيات الكتب
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

    <!-- Books Status Cards -->
    <div class="row mb-4">
        @foreach($booksByStatus as $status => $count)
            <div class="col-lg-3 col-6">
                <div class="small-box
                    @if($status == 'available') bg-success
                    @elseif($status == 'borrowed') bg-warning
                    @elseif($status == 'reserved') bg-info
                    @else bg-danger
                    @endif">
                    <div class="inner">
                        <h3>{{ $count }}</h3>
                        <p>
                            @if($status == 'available') كتب متاحة
                            @elseif($status == 'borrowed') كتب مستعارة
                            @elseif($status == 'reserved') كتب محجوزة
                            @elseif($status == 'lost') كتب مفقودة
                            @elseif($status == 'damaged') كتب تالفة
                            @else {{ $status }}
                            @endif
                        </p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Books by Department -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        الكتب حسب القسم
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="booksByDepartmentChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Requests Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        طلبات الكتب الشهرية
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyRequestsChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Popular Books -->
    <div class="row">
        <!-- Most Read Books -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-star"></i>
                        أكثر الكتب قراءة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الكتاب</th>
                                    <th>المؤلف</th>
                                    <th>القسم</th>
                                    <th>عدد القراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostReadBooks as $index => $book)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $book->book_name }}</td>
                                        <td>{{ $book->author }}</td>
                                        <td>{{ $book->department }}</td>
                                        <td>
                                            <span class="badge badge-success">{{ $book->read_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Borrowed Books -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy"></i>
                        أكثر الكتب استعارة
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الكتاب</th>
                                    <th>المؤلف</th>
                                    <th>القسم</th>
                                    <th>عدد الاستعارات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostBorrowedBooks as $index => $book)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $book->book_name }}</td>
                                        <td>{{ $book->author }}</td>
                                        <td>{{ $book->department }}</td>
                                        <td>
                                            <span class="badge badge-warning">{{ $book->borrow_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">لا توجد بيانات</td>
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
    // Books by Department Chart
    const departmentCtx = document.getElementById('booksByDepartmentChart').getContext('2d');
    new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($booksByDepartment->pluck('department')) !!},
            datasets: [{
                label: 'عدد الكتب',
                data: {!! json_encode($booksByDepartment->pluck('total')) !!},
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

    // Monthly Requests Chart
    const monthlyCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'طلبات القراءة',
                    data: {!! json_encode($readingData) !!},
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'طلبات الاستعارة',
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

