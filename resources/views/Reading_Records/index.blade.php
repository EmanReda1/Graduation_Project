@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>سجـل القـراءة</h1>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <form action="{{ route('reading-records.index') }}" method="GET" class="d-flex">
            <input type="text" class="form-control" id="search" name="search" placeholder="بحث..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary ms-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- Reading Records Table -->
    <div class="card">
        <div class="card-body">
            @if($readingRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>استعادة الكتاب</th>
                                <th>رقم الطلب</th>
                                <th>التاريخ</th>
                                <th>اسم الكتاب</th>
                                <th>اسم الطالب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($readingRecords as $record)
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('reading-records.request-borrow', $record->retrieve_id) }}" class="btn btn-sm btn-primary me-2">استعارة</a>
                                            <a href="{{ route('reading-records.return', $record->retrieve_id) }}" class="btn btn-sm btn-danger">رفض</a>
                                        </div>
                                    </td>
                                    <td>{{ $record->request->request_id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($record->request_date)->format('d/m/Y') }}</td>
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
                    {{ $readingRecords->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    لا توجد سجلات قراءة حالياً.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
