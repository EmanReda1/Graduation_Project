@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>طلبـات القـراءة</h1>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route('reading-requests.index') }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Reading Requests Table -->
    <div class="card">
        <div class="card-body">
            @if($readingRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>حالة الطلب</th>
                                <th>رقم الطلب</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readingRequests as $request)
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('reading-requests.approve', $request->request_id) }}" class="btn btn-sm btn-primary me-2">قبول</a>
                                            <a href="{{ route('reading-requests.reject', $request->request_id) }}" class="btn btn-sm btn-danger">رفض</a>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('reading-requests.show', $request->request_id) }}">
                                            {{ $request->request_id }}
                                        </a>
                                    </td>
                                    <td>{{ $request->book ? $request->book->book_name : 'غير متوفر' }}</td>
                                    <td>
                                        <a href="{{ route('students.show', $request->student_id) }}">
                                            {{ $request->student ? $request->student->fullname : 'غير متوفر' }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $readingRequests->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد طلبات قراءة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
