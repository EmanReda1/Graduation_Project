@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">إضافة مشروع جديد</h4>
                    <div class="card-tools">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
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

                    <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_name">اسم المشروع <span class="text-danger">*</span></label>
                                    <input type="text" name="project_name" id="project_name" class="form-control" value="{{ old('project_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">القسم <span class="text-danger">*</span></label>
                                    <select name="department" id="department" class="form-control select2" required>
                                        <option value="">اختر القسم</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>
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
                                    <label for="project_name"> مشرف المشروع <span class="text-danger">*</span></label>
                                    <input type="text" name="supervisor" id="supervisor" class="form-control" value="{{ old('supervisor') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_name"> سنة المشروع<span class="text-danger">*</span></label>
                                    <input type="date" name="project_date" id="project_date" class="form-control" value="{{ old('project_date') }}" required>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="place">المكان</label>
                                    <input type="text" name="place" id="place" class="form-control" value="{{ old('place') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shelf_no">رقم الرف</label>
                                    <input type="text" name="shelf_no" id="shelf_no" class="form-control" value="{{ old('shelf_no') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="">اختر الحالة</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" {{ old('status', 'available') == $status ? 'selected' : '' }}>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="image">صورة المشروع</label>
                                    <div class="custom-file">
                                        <input type="file" name="image" id="image" class="custom-file-input">
                                        <label class="custom-file-label" for="image">اختر صورة</label>
                                    </div>
                                    <small class="form-text text-muted">الصيغ المدعومة: JPG, PNG, GIF. الحد الأقصى للحجم: 2MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="sum">ملخص المشروع</label>
                                    <textarea name="sum" id="sum" class="form-control" rows="5">{{ old('sum') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ المشروع
                                </button>
                                <a href="{{ route('projects.index') }}" class="btn btn-secondary">
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
