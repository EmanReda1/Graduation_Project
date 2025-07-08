@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تفاصيل الامتحان</h4>
                    <div class="card-tools">
                        <a href="{{ route('exams.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                        <a href="{{ route('exams.edit', $exam->exam_id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                        @if($exam->pdf)
                            <a href="{{ route('exams.download', $exam->exam_id) }}" class="btn btn-success">
                                <i class="fas fa-download"></i> تحميل PDF
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">رقم الامتحان:</th>
                                    <td>{{ $exam->exam_id }}</td>
                                </tr>
                                <tr>
                                    <th>اسم المقرر:</th>
                                    <td>{{ $exam->course_name }}</td>
                                </tr>
                                <tr>
                                    <th>نوع الامتحان:</th>
                                    <td>
                                        <span class="badge badge-lg
                                            @if($exam->type == 'final') badge-danger
                                            @elseif($exam->type == 'midterm') badge-warning
                                            @elseif($exam->type == 'quiz') badge-info
                                            @elseif($exam->type == 'assignment') badge-success
                                            @else badge-secondary
                                            @endif">
                                            {{ $exam->type_in_arabic }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>القسم:</th>
                                    <td>{{ $exam->dept }}</td>
                                </tr>
                                <tr>
                                    <th>اسم الدكتور:</th>
                                    <td>{{ $exam->doctor }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">الفصل الدراسي:</th>
                                    <td>{{ $exam->semester_in_arabic }}</td>
                                </tr>
                                <tr>
                                    <th>المستوى:</th>
                                    <td>المستوى {{ $exam->level }}</td>
                                </tr>
                                <tr>
                                    <th>السنة الأكاديمية:</th>
                                    <td>{{ $exam->formatted_year }}</td>
                                </tr>
                                <!--tr>
                                    <th>تاريخ الإضافة:</th>
                                    <td>{{ $exam->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>آخر تحديث:</th>
                                    <td>{{ $exam->updated_at->format('Y-m-d H:i') }}</td>
                                </tr!-->
                            </table>
                        </div>
                    </div>

                    @if($exam->pdf)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                            ملف الامتحان
                                        </h3>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-file-pdf text-danger" style="font-size: 4rem;"></i>
                                        </div>
                                        <h5>{{ $exam->pdf }}</h5>
                                        <p class="text-muted">ملف PDF - امتحان {{ $exam->course_name }}</p>
                                        <div class="btn-group">
                                            <a href="{{ route('exams.download', $exam->exam_id) }}" class="btn btn-success">
                                                <i class="fas fa-download"></i> تحميل الملف
                                            </a>
                                            <a href="{{ asset('storage/exams/' . $exam->pdf) }}" target="_blank" class="btn btn-info">
                                                <i class="fas fa-external-link-alt"></i> فتح في نافذة جديدة
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    لا يوجد ملف PDF مرفق لهذا الامتحان
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <div class="btn-group">
                                <a href="{{ route('exams.edit', $exam->exam_id) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> تعديل الامتحان
                                </a>
                                <form action="{{ route('exams.destroy', $exam->exam_id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا الامتحان؟\n\nسيتم حذف الملف المرفق أيضاً!')">
                                        <i class="fas fa-trash"></i> حذف الامتحان
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Add confirmation for delete action
        $('form[action*="destroy"]').on('submit', function(e) {
            if (!confirm('هل أنت متأكد من حذف هذا الامتحان؟\n\nسيتم حذف الملف المرفق أيضاً!')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endsection

