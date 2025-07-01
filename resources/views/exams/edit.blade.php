@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تعديل الامتحان</h4>
                    <div class="card-tools">
                        <a href="{{ route('exams.show', $exam->exam_id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <a href="{{ route('exams.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('exams.update', $exam->exam_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="course_name">اسم المقرر <span class="text-danger">*</span></label>
                                    <input type="text" name="course_name" id="course_name" class="form-control" value="{{ old('course_name', $exam->course_name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">نوع الامتحان <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="">اختر نوع الامتحان</option>
                                        @foreach($types as $examType)
                                            <option value="{{ $examType }}" {{ old('type', $exam->type) == $examType ? 'selected' : '' }}>
                                                @if($examType == 'midterm')
                                                    امتحان نصف الفصل
                                                @elseif($examType == 'final')
                                                    امتحان نهائي
                                                @elseif($examType == 'quiz')
                                                    كويز
                                                @elseif($examType == 'assignment')
                                                    تكليف
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dept">القسم <span class="text-danger">*</span></label>
                                    <select name="dept" id="dept" class="form-control select2" required>
                                        <option value="">اختر القسم</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ old('dept', $exam->dept) == $dept ? 'selected' : '' }}>
                                                {{ $dept }}
                                            </option>
                                        @endforeach
                                        <option value="new_department">قسم جديد...</option>
                                    </select>
                                </div>
                                <div class="form-group new-department-input" style="display: none;">
                                    <label for="new_department">اسم القسم الجديد <span class="text-danger">*</span></label>
                                    <input type="text" name="new_department" id="new_department" class="form-control" value="{{ old('new_department') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doctor">اسم الدكتور <span class="text-danger">*</span></label>
                                    <input type="text" name="doctor" id="doctor" class="form-control" value="{{ old('doctor', $exam->doctor) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="semester">الفصل الدراسي <span class="text-danger">*</span></label>
                                    <select name="semester" id="semester" class="form-control" required>
                                        <option value="">اختر الفصل الدراسي</option>
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem }}" {{ old('semester', $exam->semester) == $sem ? 'selected' : '' }}>
                                                @if($sem == 'first')
                                                    الفصل الأول
                                                @elseif($sem == 'second')
                                                    الفصل الثاني
                                                @elseif($sem == 'summer')
                                                    الفصل الصيفي
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="level">المستوى <span class="text-danger">*</span></label>
                                    <select name="level" id="level" class="form-control" required>
                                        <option value="">اختر المستوى</option>
                                        @foreach($levels as $lvl)
                                            <option value="{{ $lvl }}" {{ old('level', $exam->level) == $lvl ? 'selected' : '' }}>
                                                المستوى {{ $lvl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">السنة الأكاديمية <span class="text-danger">*</span></label>
                                    <select name="year" id="year" class="form-control" required>
                                        <option value="">اختر السنة الأكاديمية</option>
                                        @foreach($years as $yr)
                                            <option value="{{ $yr }}" {{ old('year', $exam->year) == $yr ? 'selected' : '' }}>
                                                {{ $yr }} - {{ $yr + 1 }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pdf">ملف الامتحان (PDF)</label>
                                    @if($exam->pdf)
                                        <div class="alert alert-info">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                            الملف الحالي: <strong>{{ $exam->pdf }}</strong>
                                            <a href="{{ route('exams.download', $exam->exam_id) }}" class="btn btn-sm btn-success ml-2">
                                                <i class="fas fa-download"></i> تحميل
                                            </a>
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" name="pdf" id="pdf" class="custom-file-input" accept=".pdf">
                                        <label class="custom-file-label" for="pdf">
                                            {{ $exam->pdf ? 'اختر ملف PDF جديد (اختياري)' : 'اختر ملف PDF' }}
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        الصيغة المدعومة: PDF فقط. الحد الأقصى للحجم: 10MB
                                        @if($exam->pdf)
                                            <br><strong>ملاحظة:</strong> إذا لم تختر ملف جديد، سيبقى الملف الحالي كما هو.
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ التعديلات
                                </button>
                                <a href="{{ route('exams.show', $exam->exam_id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> عرض
                                </a>
                                <a href="{{ route('exams.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Initialize Select2
        $('.select2').select2({
            dir: "rtl",
            language: "ar",
            tags: true
        });

        // Show/hide new department input
        $('#dept').change(function() {
            if ($(this).val() === 'new_department') {
                $('.new-department-input').show();
                $('#new_department').prop('required', true);
            } else {
                $('.new-department-input').hide();
                $('#new_department').prop('required', false);
            }
        });

        // Custom file input
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            if (fileName) {
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            } else {
                $(this).siblings(".custom-file-label").removeClass("selected").html('{{ $exam->pdf ? "اختر ملف PDF جديد (اختياري)" : "اختر ملف PDF" }}');
            }
        });

        // Validate file size
        $('#pdf').change(function() {
            var file = this.files[0];
            if (file) {
                var fileSize = file.size / 1024 / 1024; // Convert to MB
                if (fileSize > 10) {
                    alert('حجم الملف كبير جداً. الحد الأقصى المسموح: 10MB');
                    $(this).val('');
                    $(this).siblings(".custom-file-label").removeClass("selected").html('{{ $exam->pdf ? "اختر ملف PDF جديد (اختياري)" : "اختر ملف PDF" }}');
                }
            }
        });
    });
</script>
@endsection

