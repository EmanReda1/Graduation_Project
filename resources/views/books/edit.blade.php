
@extends('layouts.app')
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تعديل الكتاب</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('books.show', $book->book_id) }}" class="btn btn-secondary">العودة إلى تفاصيل الكتاب</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('books.update', $book->book_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="book_name" class="form-label">اسم الكتاب <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('book_name') is-invalid @enderror" id="book_name" name="book_name" value="{{ old('book_name', $book->book_name) }}" required>
                            @error('book_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="author" class="form-label">المؤلف <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('author') is-invalid @enderror" id="author" name="author" value="{{ old('author', $book->author) }}" required>
                            @error('author')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="isbn_no" class="form-label">رقم ISBN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('isbn_no') is-invalid @enderror" id="isbn_no" name="isbn_no" value="{{ old('isbn_no', $book->isbn_no) }}" required>
                            @error('isbn_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="book_no" class="form-label">رقم الكتاب <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('book_no') is-invalid @enderror" id="book_no" name="book_no" value="{{ old('book_no', $book->book_no) }}" required>
                            @error('book_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">السعر <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $book->price) }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="source" class="form-label">المصدر <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('source') is-invalid @enderror" id="source" name="source" value="{{ old('source', $book->source) }}" required>
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Enhanced Department Section with ML Prediction -->
                        <div class="mb-3">
                            <label for="department" class="form-label">
                                القسم
                                <span class="text-danger">*</span>
                                <button type="button" id="predict-btn" class="btn btn-sm btn-outline-primary ms-2" title="إعادة توقع القسم باستخدام الذكاء الاصطناعي">
                                    <i class="fas fa-magic"></i> إعادة توقع القسم
                                </button>
                            </label>

                            <!-- Current Department Info -->
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    القسم الحالي: <strong>{{ $book->department }}</strong>
                                </small>
                            </div>

                            <!-- Prediction Status -->
                            <div id="prediction-status" class="mb-2" style="display: none;">
                                <div class="alert alert-info d-flex align-items-center" role="alert">
                                    <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                                    <div>جاري إعادة توقع القسم المناسب...</div>
                                </div>
                            </div>

                            <!-- Prediction Result -->
                            <div id="prediction-result" class="mb-2" style="display: none;">
                                <div class="alert alert-success d-flex align-items-center justify-content-between" role="alert">
                                    <div>
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>القسم المتوقع:</strong> <span id="predicted-department"></span>
                                    </div>
                                    <div>
                                        <button type="button" id="accept-prediction" class="btn btn-sm btn-success me-1">قبول</button>
                                        <button type="button" id="dismiss-prediction" class="btn btn-sm btn-outline-secondary">تجاهل</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Prediction Error -->
                            <div id="prediction-error" class="mb-2" style="display: none;">
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span id="error-message">فشل في توقع القسم. يرجى اختيار القسم يدوياً.</span>
                                </div>
                            </div>

                            <select class="form-select @error('department') is-invalid @enderror" id="department" name="department" required>
                                <option value="">اختر القسم</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ old('department', $book->department) == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                                <option value="other" {{ old('department', $book->department) == 'other' ? 'selected' : '' }}>قسم آخر</option>
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="other_department_div" style="display: none;">
                            <label for="other_department" class="form-label">قسم آخر <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="other_department" name="other_department">
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="available" {{ old('status', $book->status) == 'available' ? 'selected' : '' }}>متاح</option>
                                <option value="borrowed" {{ old('status', $book->status) == 'borrowed' ? 'selected' : '' }}>معار</option>
                                <option value="reserved" {{ old('status', $book->status) == 'reserved' ? 'selected' : '' }}>محجوز</option>
                                <option value="unavailable" {{ old('status', $book->status) == 'unavailable' ? 'selected' : '' }}>غير متاح</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="place" class="form-label">المكان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('place') is-invalid @enderror" id="place" name="place" value="{{ old('place', $book->place) }}" required>
                            @error('place')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shelf_no" class="form-label">رقم الرف <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('shelf_no') is-invalid @enderror" id="shelf_no" name="shelf_no" value="{{ old('shelf_no', $book->shelf_no) }}" required>
                            @error('shelf_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="size" class="form-label">الحجم <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('size') is-invalid @enderror" id="size" name="size" value="{{ old('size', $book->size) }}" required>
                            @error('size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="release_date" class="form-label">تاريخ الإصدار <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date', $book->release_date->format('Y-m-d')) }}" required>
                            @error('release_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="library_date" class="form-label">تاريخ الإضافة للمكتبة <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('library_date') is-invalid @enderror" id="library_date" name="library_date" value="{{ old('library_date', $book->library_date->format('Y-m-d')) }}" required>
                            @error('library_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">صورة الكتاب</label>
                            @if($book->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $book->image) }}" alt="{{ $book->book_name }}" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
                            <small class="form-text text-muted">اترك هذا الحقل فارغًا إذا كنت لا ترغب في تغيير الصورة.</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="summary" class="form-label">ملخص الكتاب <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="5" required>{{ old('summary', $book->summary) }}</textarea>
                    @error('summary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">تحديث الكتاب</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
