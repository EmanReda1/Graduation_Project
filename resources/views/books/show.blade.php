<!-- resources/views/books/show.blade.php -->
@extends('layouts.app')
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل الكتاب</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('books.index') }}" class="btn btn-secondary">العودة إلى قائمة الكتب</a>
        </div>
    </div>

    <!-- Flash Message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Book Image -->
                <div class="col-md-4 text-center mb-4">
                    @if($book->image)
                        <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->book_name }}" class="img-fluid rounded" style="max-height: 300px;">
                    @else
                        <img src="{{ asset('images/no-image.png') }}" alt="No Image" class="img-fluid rounded" style="max-height: 300px;">
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('books.edit', $book->book_id) }}" class="btn btn-primary">تعديل الكتاب</a>
                        <form action="{{ route('books.destroy', $book->book_id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الكتاب؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">حذف الكتاب</button>
                        </form>
                    </div>
                </div>

                <!-- Book Details -->
                <div class="col-md-8">
                    <h2>{{ $book->book_name }}</h2>

                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 30%">المؤلف</th>
                                <td>{{ $book->author }}</td>
                            </tr>
                            <tr>
                                <th>رقم ISBN</th>
                                <td>{{ $book->isbn_no }}</td>
                            </tr>
                            <tr>
                                <th>رقم الكتاب</th>
                                <td>{{ $book->book_no }}</td>
                            </tr>
                            <tr>
                                <th>السعر</th>
                                <td>{{ $book->price }}</td>
                            </tr>
                            <tr>
                                <th>المصدر</th>
                                <td>{{ $book->source }}</td>
                            </tr>
                            <tr>
                                <th>القسم</th>
                                <td>{{ $book->department }}</td>
                            </tr>
                            <tr>
                                <th>الحالة</th>
                                <td>
                                    @if($book->status == 'available')
                                        <span class="badge bg-success">متاح</span>
                                    @elseif($book->status == 'borrowed')
                                        <span class="badge bg-warning">معار</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $book->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>المكان</th>
                                <td>{{ $book->place }}</td>
                            </tr>
                            <tr>
                                <th>رقم الرف</th>
                                <td>{{ $book->shelf_no }}</td>
                            </tr>
                            <tr>
                                <th>الحجم</th>
                                <td>{{ $book->size }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الإصدار</th>
                                <td>{{ $book->release_date->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <th>تاريخ الإضافة للمكتبة</th>
                                <td>{{ $book->library_date->format('Y-m-d') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4 class="mt-4">ملخص الكتاب</h4>
                    <div class="card">
                        <div class="card-body">
                            {{ $book->summary }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="mt-5">
                <h3>المراجعات ({{ $book->reviews->count() }})</h3>

                @if($book->reviews->count() > 0)
                    <div class="list-group">
                        @foreach($book->reviews as $review)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        @for($i = 0; $i < $review->rating; $i++)
                                            <i class="fas fa-star text-warning"></i>
                                        @endfor
                                        @for($i = $review->rating; $i < 5; $i++)
                                            <i class="far fa-star text-warning"></i>
                                        @endfor
                                    </h5>
                                    <small>{{ $review->created_at->format('Y-m-d') }}</small>
                                </div>
                                <p class="mb-1">{{ $review->review }}</p>
                                <small>بواسطة: {{ $review->student->username ?? 'مستخدم غير معروف' }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        لا توجد مراجعات لهذا الكتاب حتى الآن.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
