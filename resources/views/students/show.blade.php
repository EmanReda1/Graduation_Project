<!-- resources/views/students/show.blade.php - Basic Student Data Only -->
@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل الطالب</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">كل الطلاب</a></li>
                    <li class="breadcrumb-item active">{{ $student->fullname }}</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Student Basic Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> بيانات الطالب</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                        <h4>{{ $student->fullname }}</h4>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>الاسم الكامل:</strong></label>
                                <p class="form-control-plaintext">{{ $student->fullname }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>الكود الجامعي:</strong></label>
                                <p class="form-control-plaintext">
                                    @if($student->university_code)
                                        <span class="badge bg-success">{{ $student->university_code }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>المستوى:</strong></label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-primary fs-6">المستوى {{ $student->level }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>القسم:</strong></label>
                                <p class="form-control-plaintext">
                                    @if($student->department)
                                        <span class="badge bg-info fs-6">{{ $student->department }}</span>
                                    @else
                                        <span class="text-muted">غير محدد</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label"><strong>تاريخ التسجيل:</strong></label>
                                <p class="form-control-plaintext">
                                    <i class="fas fa-calendar"></i>
                                    {{ $student->created_at->format('Y-m-d') }}
                                    <small class="text-muted">({{ $student->created_at->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control-plaintext {
        padding: 0.375rem 0;
        margin-bottom: 0;
        font-size: 1rem;
        line-height: 1.5;
        color: #212529;
        background-color: transparent;
        border: solid transparent;
        border-width: 1px 0;
    }
</style>
@endpush
@endsection

