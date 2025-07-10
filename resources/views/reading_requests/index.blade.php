@extends('layouts.app')
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="container">
        <div class="search-section-custom">
            <h1>طلبات القراءة المعلقة</h1>

            <div class="mb-4">
                <form action="{{ route('reading-requests.index') }}" method="GET" class="d-flex">
                    <input type="text" class="form-control" id="search" name="search" placeholder="بحث..."
                        value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary ms-2">
                        <i class="fas fa-search"></i>
                    </button>

                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">قائمة طلبات القراءة</h5>
            </div>
            <div class="card-body">
                @if ($readingRequests->isEmpty())
                    <p class="text-center">لا توجد طلبات قراءة معلقة حالياً.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>اسم الكتاب</th>
                                    <th>تاريخ الطلب</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($readingRequests as $request)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $request->student->fullname }}</td>
                                        <td>{{ $request->book->book_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($request->date_of_request)->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($request->status == 'pending')
                                                <span class="badge bg-warning">قيد الانتظار</span>
                                            @elseif($request->status == 'approved')
                                                <span class="badge bg-success">تمت الموافقة</span>
                                            @elseif($request->status == 'rejected')
                                                <span class="badge bg-danger">مرفوض</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $request->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('reading-requests.show', $request->request_id) }}"
                                                class="btn btn-sm btn-info me-2">عرض</a>
                                            @if ($request->status == 'pending')
                                                <form
                                                    action="{{ route('reading-requests.approve', $request->request_id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success me-2">قبول</button>
                                                </form>
                                                <form action="{{ route('reading-requests.reject', $request->request_id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">رفض</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $readingRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
