@extends('layouts.app')
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تعديل المشروع: {{ $project->project_name }}</h4>
                    <div class="card-tools">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                        <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> عرض التفاصيل
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('projects.update', $project->project_id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_name">اسم المشروع <span class="text-danger">*</span></label>
                                    <input type="text" name="project_name" id="project_name" class="form-control" value="{{ old('project_name', $project->project_name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">القسم <span class="text-danger">*</span></label>
                                    <select name="department" id="department" class="form-control select2" required>
                                        <option value="">اختر القسم</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ old('department', $project->department) == $dept ? 'selected' : '' }}>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="supervisor">مشرف المشروع <span class="text-danger">*</span></label>
                                    <input type="text" name="supervisor" id="supervisor" class="form-control" value="{{ old('supervisor', $project->supervisor) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_date">سنة المشروع <span class="text-danger">*</span></label>
                                    <input type="date" name="project_date" id="project_date" class="form-control" value="{{ old('project_date', $project->project_date) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="place">المكان</label>
                                    <input type="text" name="place" id="place" class="form-control" value="{{ old('place', $project->place) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shelf_no">رقم الرف</label>
                                    <input type="text" name="shelf_no" id="shelf_no" class="form-control" value="{{ old('shelf_no', $project->shelf_no) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="">اختر الحالة</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ old('status', $project->status) == $status ? 'selected' : '' }}>
                                                @if($status == 'available')
                                                    متاح
                                                @elseif($status == 'borrowed')
                                                    مستعار
                                                @elseif($status == 'archived')
                                                    مؤرشف
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="image">صورة المشروع</label>
                                    <div class="custom-file">
                                        <input type="file" name="image" id="image" class="custom-file-input" accept="image/*">
                                        <label class="custom-file-label" for="image">اختر صورة</label>
                                    </div>
                                    <small class="form-text text-muted">الصيغ المدعومة: JPG, PNG, GIF. الحد الأقصى للحجم: 2MB</small>

                                    @if($project->image)
                                        <div class="mt-2">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ Storage::url($project->image) }}" alt="{{ $project->project_name }}" class="img-thumbnail" style="max-height: 100px; max-width: 100px;">
                                                <div class="ml-3">
                                                    <span class="text-muted">الصورة الحالية</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pdf">ملف PDF للمشروع</label>
                                    <div class="custom-file">
                                        <input type="file" name="pdf" id="pdf" class="custom-file-input" accept=".pdf">
                                        <label class="custom-file-label" for="pdf">اختر ملف PDF</label>
                                    </div>
                                    <small class="form-text text-muted">الصيغة المدعومة: PDF فقط. الحد الأقصى للحجم: 10MB</small>

                                    @if($project->pdf)
                                        <div class="mt-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-pdf text-danger" style="font-size: 2em;"></i>
                                                <div class="ml-3">
                                                    <span class="text-muted">الملف الحالي</span><br>
                                                    <a href="{{ Storage::url($project->pdf) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> تحميل PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="sum">ملخص المشروع</label>
                                    <textarea name="sum" id="sum" class="form-control" rows="5">{{ old('sum', $project->sum) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ التغييرات
                                </button>
                                <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-secondary">
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
        $('#department').change(function() {
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
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    });
</script>
@endsection
