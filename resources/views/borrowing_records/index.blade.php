@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>سجـل الاستعـارة</h1>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route('borrowing-records.index') }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Borrowing Records Table -->
    <div class="card">
        <div class="card-body">
            @if($borrowingRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>تاريخ الإرجاع</th>
                                <th>تاريخ الاستعارة</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($borrowingRecords as $record)
                                <tr>
                                    <td>{{ $record->request->request_id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($record->request_date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($record->request->date_of_request)->format('d/m/Y') }}</td>
                                    <td>{{ $record->request->book->book_name }}</td>
                                    <td>
                                        <a href="{{ route('students.show', $record->request->student_id) }}">
                                            {{ $record->request->student->fullname }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $borrowingRecords->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد سجلات استعارة مكتملة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
