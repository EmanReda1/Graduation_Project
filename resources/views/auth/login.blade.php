@extends('layouts.auth')

{{-- ربط الـ CSS بصفحة الـ login بس --}}
@push('styles')
<link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="card-header">تسجيل الدخول</div>

        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        تذكرني
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    تسجيل الدخول
                </button>

                @if (Route::has('password.request'))
                    <div style="margin-top: 20px;">
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            نسيت كلمة المرور؟
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
