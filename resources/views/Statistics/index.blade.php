@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        لوحة الإحصائيات
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Books Statistics -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalBooks }}</h3>
                    <p>إجمالي الكتب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
                <a href="{{ route('statistics.books') }}" class="small-box-footer">
                    المزيد من التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>

        <!-- Students Statistics -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalStudents }}</h3>
                    <p>إجمالي الطلاب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('statistics.students') }}" class="small-box-footer">
                    المزيد من التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>

        <!-- Visits Statistics -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalVisits }}</h3>
                    <p>إجمالي الزيارات</p>
                </div>
                <div class="icon">
                    <i class="fas fa-door-open"></i>
                </div>
                <a href="{{ route('statistics.visits') }}" class="small-box-footer">
                    المزيد من التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>

        <!-- Book Requests Statistics -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalBookRequests }}</h3>
                    <p>إجمالي طلبات الكتب</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="{{ route('statistics.requests') }}" class="small-box-footer">
                    المزيد من التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Additional Summary Cards -->
    <div class="row mb-4">
        <!-- Projects Statistics -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalProjects }}</h3>
                    <p>إجمالي المشاريع</p>
                </div>
                <div class="icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <a href="{{ route('statistics.projects') }}" class="small-box-footer">
                    المزيد من التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>

        <!-- Exams Statistics -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $totalExams }}</h3>
                    <p>إجمالي الامتحانات</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <a href="{{ route('statistics.exams') }}" class="small-box-footer">
                    المزيد من التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>

        <!-- Today's Visits -->
        <div class="col-lg-4 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ $visitsToday }}</h3>
                    <p>زيارات اليوم</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Books Status Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        حالة الكتب
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="booksStatusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Activity Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        النشاط الشهري
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyActivityChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mt-4">
        <!-- Quick Stats Cards -->
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-book-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">كتب متاحة</span>
                    <span class="info-box-number">{{ $availableBooks }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-book-reader"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">كتب مستعارة</span>
                    <span class="info-box-number">{{ $borrowedBooks }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلاب لديهم مستوى</span>
                    <span class="info-box-number">{{ $studentsWithLevel }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات معلقة</span>
                    <span class="info-box-number">{{ $pendingBookRequests }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Types Row -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-eye"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات قراءة</span>
                    <span class="info-box-number">{{ $readingRequests }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-hand-holding"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">طلبات استعارة</span>
                    <span class="info-box-number">{{ $borrowingRequests }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Popular Books -->
    <div class="row mt-4">
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
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>اسم الكتاب</th>
                                    <th>المؤلف</th>
                                    <th>عدد القراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostReadBooks as $book)
                                    <tr>
                                        <td>{{ $book->book_name }}</td>
                                        <td>{{ $book->author }}</td>
                                        <td><span class="badge badge-info">{{ $book->read_count }}</span></td>
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
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>اسم الكتاب</th>
                                    <th>المؤلف</th>
                                    <th>عدد الاستعارات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostBorrowedBooks as $book)
                                    <tr>
                                        <td>{{ $book->book_name }}</td>
                                        <td>{{ $book->author }}</td>
                                        <td><span class="badge badge-warning">{{ $book->borrow_count }}</span></td>
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
    // Books Status Pie Chart
    const booksStatusCtx = document.getElementById('booksStatusChart').getContext('2d');
    new Chart(booksStatusCtx, {
        type: 'pie',
        data: {
            labels: ['متاح', 'مستعار', 'أخرى'],
            datasets: [{
                data: [{{ $booksStatusData['available'] }}, {{ $booksStatusData['borrowed'] }}, {{ $booksStatusData['other'] }}],
                backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
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

    // Monthly Activity Line Chart
    const monthlyActivityCtx = document.getElementById('monthlyActivityChart').getContext('2d');
    new Chart(monthlyActivityCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'الزيارات',
                    data: {!! json_encode($visitsData) !!},
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                },
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

