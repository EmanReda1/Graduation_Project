@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="container-fluid">
    <!-- Page Header with Gradient -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #6779d3 0%, #799ac7 100%);">
                <div class="card-body text-white text-center py-5">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-chart-line me-3"></i>
                        لوحة الإحصائيات
                    </h1>
                    <p class="lead mb-0">نظرة شاملة على أداء النظام والبيانات الإحصائية</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="row mb-4">
        <!-- Books Statistics -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-primary mb-3 mx-auto">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <h3 class="display-6 text-primary fw-bold mb-2">{{ $totalBooks }}</h3>
                    <p class="text-muted mb-3">إجمالي الكتب</p>
                    <a href="{{ route('statistics.books') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <!-- Students Statistics -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-success mb-3 mx-auto">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <h3 class="display-6 text-success fw-bold mb-2">{{ $totalStudents }}</h3>
                    <p class="text-muted mb-3">إجمالي الطلاب</p>
                    <a href="{{ route('statistics.students') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <!-- Visits Statistics -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-warning mb-3 mx-auto">
                        <i class="fas fa-door-open text-white"></i>
                    </div>
                    <h3 class="display-6 text-warning fw-bold mb-2">{{ $totalVisits }}</h3>
                    <p class="text-muted mb-3">إجمالي الزيارات</p>
                    <a href="{{ route('statistics.visits') }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <!-- Book Requests Statistics -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-danger mb-3 mx-auto">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                    <h3 class="display-6 text-danger fw-bold mb-2">{{ $totalBookRequests }}</h3>
                    <p class="text-muted mb-3">إجمالي طلبات الكتب</p>
                    <a href="{{ route('statistics.requests') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics Cards -->
    <div class="row mb-4">
        <!-- Projects Statistics -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-info mb-3 mx-auto">
                        <i class="fas fa-project-diagram text-white"></i>
                    </div>
                    <h3 class="display-6 text-info fw-bold mb-2">{{ $totalProjects }}</h3>
                    <p class="text-muted mb-3">إجمالي المشاريع</p>
                    <a href="{{ route('statistics.projects') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <!-- Exams Statistics -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-secondary mb-3 mx-auto">
                        <i class="fas fa-file-alt text-white"></i>
                    </div>
                    <h3 class="display-6 text-secondary fw-bold mb-2">{{ $totalExams }}</h3>
                    <p class="text-muted mb-3">إجمالي الامتحانات</p>
                    <a href="{{ route('statistics.exams') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-eye me-2"></i>عرض التفاصيل
                    </a>
                </div>
            </div>
        </div>

        <!-- Today's Visits -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-lift">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-dark mb-3 mx-auto">
                        <i class="fas fa-calendar-day text-white"></i>
                    </div>
                    <h3 class="display-6 text-dark fw-bold mb-2">{{ $visitsToday }}</h3>
                    <p class="text-muted mb-3">زيارات اليوم</p>
                    <div class="badge bg-light text-dark">
                        <i class="fas fa-clock me-1"></i>محدثة الآن
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Books Status Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-sm bg-primary me-3">
                            <i class="fas fa-chart-pie text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">حالة الكتب</h5>
                            <small class="text-muted">التوزيع الحالي للكتب</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="booksStatusChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Activity Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-sm bg-success me-3">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">النشاط الشهري</h5>
                            <small class="text-muted">إحصائيات الأنشطة عبر الأشهر</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="monthlyActivityChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        إحصائيات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="text-info">{{ $availableBooks }}</h4>
                                    <p class="text-muted mb-0">كتب متاحة</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-book-reader"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="text-warning">{{ $borrowedBooks }}</h4>
                                    <p class="text-muted mb-0">كتب مستعارة</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="text-success">{{ $studentsWithLevel }}</h4>
                                    <p class="text-muted mb-0">طلاب لديهم مستوى</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="stat-item">
                                <div class="stat-icon bg-danger">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <h4 class="text-danger">{{ $pendingBookRequests }}</h4>
                                    <p class="text-muted mb-0">طلبات معلقة</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Types Section -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-primary mb-3 mx-auto">
                        <i class="fas fa-eye text-white"></i>
                    </div>
                    <h3 class="display-6 text-primary fw-bold">{{ $readingRequests }}</h3>
                    <p class="text-muted">طلبات قراءة</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="icon-circle bg-secondary mb-3 mx-auto">
                        <i class="fas fa-hand-holding text-white"></i>
                    </div>
                    <h3 class="display-6 text-secondary fw-bold">{{ $borrowingRequests }}</h3>
                    <p class="text-muted">طلبات استعارة</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Books Section -->
    <div class="row">
        <!-- Most Read Books -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-sm bg-warning me-3">
                            <i class="fas fa-star text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">أكثر الكتب قراءة</h5>
                            <small class="text-muted">الكتب الأكثر شعبية</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الكتاب</th>
                                    <th>المؤلف</th>
                                    <th>عدد القراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostReadBooks as $book)
                                    <tr>
                                        <td>
                                            <i class="fas fa-book text-primary me-2"></i>
                                            {{ $book->book_name }}
                                        </td>
                                        <td>{{ $book->author }}</td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">{{ $book->read_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p class="mb-0">لا توجد بيانات متاحة</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Borrowed Books -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle-sm bg-success me-3">
                            <i class="fas fa-trophy text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">أكثر الكتب استعارة</h5>
                            <small class="text-muted">الكتب الأكثر طلباً</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>اسم الكتاب</th>
                                    <th>المؤلف</th>
                                    <th>عدد الاستعارات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mostBorrowedBooks as $book)
                                    <tr>
                                        <td>
                                            <i class="fas fa-book text-success me-2"></i>
                                            {{ $book->book_name }}
                                        </td>
                                        <td>{{ $book->author }}</td>
                                        <td>
                                            <span class="badge bg-success rounded-pill">{{ $book->borrow_count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p class="mb-0">لا توجد بيانات متاحة</p>
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
</div>

<!-- Custom Styles -->
<style>
.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.icon-circle-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;
    background: rgba(0, 0, 0, 0.02);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: white;
    margin-left: 1rem;
}

.stat-content h4 {
    margin: 0;
    font-weight: 700;
}

.card {
    border-radius: 1rem;
}

.card-header {
    border-radius: 1rem 1rem 0 0 !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.8rem;
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Books Status Pie Chart with Enhanced Styling
    const booksStatusCtx = document.getElementById('booksStatusChart').getContext('2d');
    new Chart(booksStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['متاح', 'مستعار', 'أخرى'],
            datasets: [{
                data: [{{ $booksStatusData['available'] }}, {{ $booksStatusData['borrowed'] }}, {{ $booksStatusData['other'] }}],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(108, 117, 125, 1)'
                ],
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 14
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });

    // Monthly Activity Line Chart with Enhanced Styling
    const monthlyActivityCtx = document.getElementById('monthlyActivityChart').getContext('2d');
    new Chart(monthlyActivityCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'الزيارات',
                    data: {!! json_encode($visitsData) !!},
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                },
                {
                    label: 'طلبات القراءة',
                    data: {!! json_encode($readingData) !!},
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                },
                {
                    label: 'طلبات الاستعارة',
                    data: {!! json_encode($borrowingData) !!},
                    borderColor: 'rgba(255, 193, 7, 1)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(255, 193, 7, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
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
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
@endsection
