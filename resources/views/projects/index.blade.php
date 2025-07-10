@extends('layouts.app')
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="container">
        <div class="search-section-custom">
            <h1>المشاريع </h1>

                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة مشروع جديد
                </a>

            <!-- Search -->
            <div class="mb-4">
                <form action="{{ route('projects.index') }}" method="GET">


                                <label for="search">بحث</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="اسم المشروع ..." value="{{ request('search') }}">


                </form>
            </div>
        </div>



                        <!-- Projects Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>رقم المشروع</th>
                                        <th>اسم المشروع</th>
                                        <th>القسم</th>
                                        <th>المكان</th>
                                        <th>رقم الرف</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($projects as $project)
                                        <tr>
                                            <td>{{ $project->project_id }}</td>
                                            <td>{{ $project->project_name }}</td>
                                            <td>{{ $project->department }}</td>
                                            <td>{{ $project->place ?? 'غير محدد' }}</td>
                                            <td>{{ $project->shelf_no ?? 'غير محدد' }}</td>
                                            <td>
                                                @if ($project->status == 'available')
                                                    <span class="badge badge-success">متاح</span>
                                                @elseif($project->status == 'borrowed')
                                                    <span class="badge badge-warning">مستعار</span>
                                                @elseif($project->status == 'archived')
                                                    <span class="badge badge-secondary">مؤرشف</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('projects.show', $project->project_id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('projects.edit', $project->project_id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-secondary" title="PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>

                                                    <form action="{{ route('projects.destroy', $project->project_id) }}"
                                                        method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('هل أنت متأكد من حذف هذا المشروع؟')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>

                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">لا توجد مشاريع</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $projects->appends(request()->query())->links() }}
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
