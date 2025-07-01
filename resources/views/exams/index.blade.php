@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">الامتحانات</h4>
                    <div class="card-tools">
                        <a href="{{ route('exams.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة امتحان جديد
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">بحث وتصفية</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('exams.index') }}" method="GET">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="search">بحث</label>
                                                    <input type="text" name="search" id="search" class="form-control" placeholder="اسم المقرر، الدكتور، نوع الامتحان..." value="{{ request('search') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="dept">القسم</label>
                                                    <select name="dept" id="dept" class="form-control select2">
                                                        <option value="">الكل</option>
                                                        @foreach($departments as $dept)
                                                            <option value="{{ $dept }}" {{ request('dept') == $dept ? 'selected' : '' }}>
                                                                {{ $dept }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="type">نوع الامتحان</label>
                                                    <select name="type" id="type" class="form-control">
                                                        <option value="">الكل</option>
                                                        @foreach($types as $examType)
                                                            <option value="{{ $examType }}" {{ request('type') == $examType ? 'selected' : '' }}>
                                                                @if($examType == 'midterm')
                                                                    امتحان نصف الفصل
                                                                @elseif($examType == 'final')
                                                                    امتحان نهائي
                                                                @elseif($examType == 'quiz')
                                                                    كويز
                                                                @elseif($examType == 'assignment')
                                                                    تكليف
                                                                @else
                                                                    {{ $examType }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="semester">الفصل الدراسي</label>
                                                    <select name="semester" id="semester" class="form-control">
                                                        <option value="">الكل</option>
                                                        @foreach($semesters as $sem)
                                                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>
                                                                @if($sem == 'first')
                                                                    الفصل الأول
                                                                @elseif($sem == 'second')
                                                                    الفصل الثاني
                                                                @elseif($sem == 'summer')
                                                                    الفصل الصيفي
                                                                @else
                                                                    {{ $sem }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="level">المستوى</label>
                                                    <select name="level" id="level" class="form-control">
                                                        <option value="">الكل</option>
                                                        @foreach($levels as $lvl)
                                                            <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>
                                                                المستوى {{ $lvl }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="year">السنة الأكاديمية</label>
                                                    <select name="year" id="year" class="form-control">
                                                        <option value="">الكل</option>
                                                        @foreach($years as $yr)
                                                            <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>
                                                                {{ $yr }} - {{ $yr + 1 }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <div class="d-flex">
                                                        <button type="submit" class="btn btn-primary mr-2">
                                                            <i class="fas fa-search"></i> بحث
                                                        </button>
                                                        <a href="{{ route('exams.index') }}" class="btn btn-secondary">
                                                            <i class="fas fa-redo"></i> إعادة تعيين
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exams Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>رقم الامتحان</th>
                                    <th>اسم المقرر</th>
                                    <th>نوع الامتحان</th>
                                    <th>القسم</th>
                                    <th>الفصل الدراسي</th>
                                    <th>المستوى</th>
                                    <th>الدكتور</th>
                                    <th>السنة الأكاديمية</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                    <tr>
                                        <td>{{ $exam->exam_id }}</td>
                                        <td>{{ $exam->course_name }}</td>
                                        <td>
                                            <span class="badge
                                                @if($exam->type == 'final') badge-danger
                                                @elseif($exam->type == 'midterm') badge-warning
                                                @elseif($exam->type == 'quiz') badge-info
                                                @elseif($exam->type == 'assignment') badge-success
                                                @else badge-secondary
                                                @endif">
                                                {{ $exam->type_in_arabic }}
                                            </span>
                                        </td>
                                        <td>{{ $exam->dept }}</td>
                                        <td>{{ $exam->semester_in_arabic }}</td>
                                        <td>المستوى {{ $exam->level }}</td>
                                        <td>{{ $exam->doctor }}</td>
                                        <td>{{ $exam->formatted_year }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('exams.show', $exam->exam_id) }}" class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($exam->pdf)
                                                    <a href="{{ route('exams.download', $exam->exam_id) }}" class="btn btn-sm btn-success" title="تحميل PDF">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('exams.edit', $exam->exam_id) }}" class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('exams.destroy', $exam->exam_id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا الامتحان؟')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">لا توجد امتحانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $exams->appends(request()->query())->links() }}
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
        // Initialize Select2
        $('.select2').select2({
            dir: "rtl",
            language: "ar"
        });
    });
</script>
@endsection

