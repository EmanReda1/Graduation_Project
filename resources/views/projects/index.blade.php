@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">المشاريع</h4>
                    <div class="card-tools">
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> إضافة مشروع جديد
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
                                    <form action="{{ route('projects.index') }}" method="GET">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="search">بحث</label>
                                                    <input type="text" name="search" id="search" class="form-control" placeholder="اسم المشروع، القسم، المكان..." value="{{ request('search') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="department">القسم</label>
                                                    <select name="department" id="department" class="form-control select2">
                                                        <option value="">الكل</option>
                                                        @foreach($departments as $dept)
                                                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                                                {{ $dept }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="status">الحالة</label>
                                                    <select name="status" id="status" class="form-control">
                                                        <option value="">الكل</option>
                                                        @foreach($statuses as $status)
                                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
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
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> بحث
                                                </button>
                                                <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-redo"></i> إعادة تعيين
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
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
                                            @if($project->status == 'available')
                                                <span class="badge badge-success">متاح</span>
                                            @elseif($project->status == 'borrowed')
                                                <span class="badge badge-warning">مستعار</span>
                                            @elseif($project->status == 'archived')
                                                <span class="badge badge-secondary">مؤرشف</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('projects.edit', $project->project_id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('projects.destroy', $project->project_id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المشروع؟')">
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
