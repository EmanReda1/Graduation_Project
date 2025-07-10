@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">تفاصيل المشروع: {{ $project->project_name }}</h4>
                        <div class="card-tools">
                            <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right"></i> العودة للقائمة
                            </a>
                            <a href="{{ route('projects.edit', $project->project_id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> تعديل
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                @if ($project->image)
                                    <img src="{{ Storage::url($project->image) }}" alt="{{ $project->project_name }}"
                                        class="img-fluid img-thumbnail" style="max-height: 300px;">
                                @else
                                    <img src="{{ asset('images/project-placeholder.png') }}" alt="صورة افتراضية"
                                        class="img-fluid img-thumbnail" style="max-height: 300px;">
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">معلومات المشروع</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 30%">رقم المشروع</th>
                                                <td>{{ $project->project_id }}</td>
                                            </tr>
                                            <tr>
                                                <th>اسم المشروع</th>
                                                <td>{{ $project->project_name }}</td>
                                            </tr>
                                            <tr>
                                                <th>القسم</th>
                                                <td>{{ $project->department }}</td>
                                            </tr>
                                            <tr>
                                                <th>مشرف المشروع</th>
                                                <td>{{ $project->supervisor ?? 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <th>سنة المشروع</th>
                                                <td>{{ $project->project_date ? date('Y-m-d', strtotime($project->project_date)) : 'غير محدد' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>المكان</th>
                                                <td>{{ $project->place ?? 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <th>رقم الرف</th>
                                                <td>{{ $project->shelf_no ?? 'غير محدد' }}</td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td>
                                                    @if ($project->status == 'available')
                                                        <span class="badge badge-success">متاح</span>
                                                    @elseif($project->status == 'borrowed')
                                                        <span class="badge badge-warning">مستعار</span>
                                                    @elseif($project->status == 'archived')
                                                        <span class="badge badge-secondary">مؤرشف</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>تاريخ الإضافة</th>
                                                <td>{{ $project->created_at ? date('Y-m-d', strtotime($project->created_at)) : 'غير متوفر' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>آخر تحديث</th>
                                                <td>{{ $project->updated_at ? date('Y-m-d', strtotime($project->updated_at)) : 'غير متوفر' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                @if ($project->pdf)
                                    <div class="card">
                                        <div class="card-header bg-danger text-white">
                                            <h5 class="card-title mb-0">ملف PDF</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf text-danger" style="font-size: 4em;"></i>
                                            <div class="mt-3">
                                                <a href="{{ Storage::url($project->pdf) }}" target="_blank"
                                                    class="btn btn-danger btn-block">
                                                    <i class="fas fa-download"></i> تحميل PDF
                                                </a>
                                                <a href="{{ $project->pdf }}" target="_blank"
                                                    class="btn btn-outline-danger btn-block mt-2">
                                                    <i class="fas fa-eye"></i> عرض PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h5 class="card-title mb-0">ملف PDF</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-pdf text-muted" style="font-size: 4em;"></i>
                                            <div class="mt-3">
                                                <span class="text-muted">لا يوجد ملف PDF</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($project->sum)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h5 class="card-title mb-0">ملخص المشروع</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="project-summary">
                                                {!! nl2br(e($project->sum)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">طلبات المشروع</h5>
                                    </div>
                                    <div class="card-body">
                                        @if (count($requests) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>رقم الطلب</th>
                                                            <th>الطالب</th>
                                                            <th>نوع الطلب</th>
                                                            <th>تاريخ الطلب</th>
                                                            <th>الحالة</th>
                                                            <th>الإجراءات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($requests as $request)
                                                            <tr>
                                                                <td>{{ $request->request_id }}</td>
                                                                <td>
                                                                    @if ($request->student)
                                                                        <a
                                                                            href="{{ route('students.show', $request->student->student_id) }}">
                                                                            {{ $request->student->username }}
                                                                        </a>
                                                                    @else
                                                                        <span class="text-muted">غير متوفر</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($request->type == 'reading')
                                                                        <span class="badge badge-info">قراءة</span>
                                                                    @elseif($request->type == 'borrowing')
                                                                        <span class="badge badge-primary">استعارة</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $request->date_of_request ? date('Y-m-d', strtotime($request->date_of_request)) : 'غير متوفر' }}
                                                                </td>
                                                                <td>
                                                                    @if ($request->status == 'pending')
                                                                        <span class="badge badge-warning">قيد
                                                                            الانتظار</span>
                                                                    @elseif($request->status == 'approved')
                                                                        <span class="badge badge-success">تمت
                                                                            الموافقة</span>
                                                                    @elseif($request->status == 'rejected')
                                                                        <span class="badge badge-danger">مرفوض</span>
                                                                    @elseif($request->status == 'completed')
                                                                        <span class="badge badge-secondary">مكتمل</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('requests.show', $request->request_id) }}"
                                                                        class="btn btn-sm btn-info">
                                                                        <i class="fas fa-eye"></i> عرض
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                            </div>
                                            <div class="d-flex justify-content-center mt-4">
                                                {{ $requests->links() }}
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                لا توجد طلبات لهذا المشروع.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <div class="btn-group">
                                    <a href="{{ route('projects.edit', $project->project_id) }}" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> تعديل المشروع
                                    </a>

                                    <form action="{{ route('projects.destroy', $project->project_id) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا المشروع؟')">
                                            <i class="fas fa-trash"></i> حذف المشروع
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
