@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="container">

        <div class="search-section-custom">
            <h1>الامتحانات </h1>

                <a href="{{ route('exams.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة امتحان جديد
                </a>

            <!-- Search -->
            <div class="mb-4">
                <form action="{{ route('exams.index') }}" method="GET">


                                <label for="search">بحث</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="اسم المقرر، الدكتور، نوع الامتحان..." value="{{ request('search') }}">


                </form>
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
                                <span
                                    class="badge
                                                @if ($exam->type == 'final') badge-danger
                                                @elseif($exam->type == 'midterm') badge-warning
                                                @elseif($exam->type == 'quiz') badge-info
                                                @elseif($exam->type == 'assignment') badge-success
                                                @else badge-secondary @endif">
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
                                    <a href="{{ route('exams.show', $exam->exam_id) }}" class="btn btn-sm btn-info"
                                        title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($exam->pdf)
                                        <a href="{{ route('exams.download', $exam->exam_id) }}"
                                            class="btn btn-sm btn-success" title="تحميل PDF">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('exams.edit', $exam->exam_id) }}" class="btn btn-sm btn-primary"
                                        title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('exams.destroy', $exam->exam_id) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا الامتحان؟')">
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
