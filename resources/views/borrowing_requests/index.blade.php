@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>طلبـات الاستعـارة</h1>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route('borrowing-requests.index') }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Borrowing Requests Table -->
    <div class="card">
        <div class="card-body">
            @if($borrowingRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>التفاصيل</th>
                                <th>رقم الطلب</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowingRequests as $request)
                                <tr>
                                    <td>
                                        <a href="{{ route('borrowing-requests.show', $request->request_id) }}" class="btn btn-sm btn-primary">عرض</a>
                                    </td>
                                    <td>{{ $request->request_id }}</td>
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
                    {{ $borrowingRequests->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد طلبات استعارة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
