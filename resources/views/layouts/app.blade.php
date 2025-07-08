<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Fci-Zu Library') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome للأيقونات -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- الستايلز الأساسية -->
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- ربط الـ CSS الإضافي --}}
    @stack('styles')
</head>
<body>
    <div id="app">
        {{-- الـ Header المحسن --}}
        <header class="main-header">
            <div class="header-left">
                <div class="menu" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>

            <div class="header-center">
                <h1>Fci-Zu Library</h1>
            </div>

            <div class="header-right">

                <a href="{{-- route('chat.index') ?? '#' --}}" class="header-link">
                    <div class="chat">
                        <span>المحادثات</span>
                        <i class="fa-regular fa-comment"></i>
                    </div>
                </a>
                <a href="{{ route('library.qr-code') ?? '#' }}" class="header-link">
                    <div class="qrcode">
                        <span>QRcode</span>
                        <i class="fas fa-qrcode"></i>
                    </div>
                </a>
            </div>
        </header>

        {{-- الـ Side Menu --}}
        <nav class="side-menu" id="sideMenu">
            <ul class="menu-items">
                <li>
                    <a href="{{route('books.index') ?? '#'}}"><span>كل الكتب</span></a>
                </li>
                <li>
                    <a href="{{route('students.index') ?? '#' }}"><span>كل الطلاب</span></a>
                </li>
                <li class="{{ Request::routeIs('visits.*') ? 'active' : '' }}">
                    <a href="{{ route('visits.index') }}"><span>سجل الزيارات</span></a>
                </li>
                <li>
                    <a href="{{ route('reading-records.index') ?? '#' }}"><span>سجل القراءة</span></a>
                </li>
                <li>
                    <a href="{{ route('borrowing-records.index') ?? '#' }}"><span>سجل الاستعارة</span></a>
                </li>
                <li>
                    <a href="{{ route('borrowed-books.index') ?? '#' }}"><span>الكتب المستعارة</span></a>
                </li>
                <li>
                    <a href="{{ route('reading-requests.index') ?? '#' }}"><span>طلبات القراءة</span></a>
                </li>
                <li>
                    <a href="{{ route('borrowing-requests.index') ?? '#' }}"><span>طلبات الاستعارة</span></a>
                </li>
                <li>
                    <a href="{{ route('exams.index') ?? '#' }}"><span>إضافه امتحان</span></a>
                </li>
                <li>
                    <a href="{{ route('projects.index') ?? '#' }}"><span>إضافه مشروع</span></a>
                </li>
                <li>
                    <a href="{{ route('statistics.index') ?? '#' }}"><span>إحصائيات</span></a>
                </li>
                <li class="menu-footer">
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span>تسجيل الخروج</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>

        {{-- المحتوى الرئيسي --}}
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    {{-- JavaScript للتحكم في القائمة --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sideMenu = document.getElementById('sideMenu');
            const body = document.body;

            menuToggle.addEventListener('click', function() {
                sideMenu.classList.toggle('active');
                body.classList.toggle('menu-open');
            });

            // إغلاق القائمة عند الضغط خارجها
            document.addEventListener('click', function(e) {
                if (!sideMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                    sideMenu.classList.remove('active');
                    body.classList.remove('menu-open');
                }
            });
        });
    </script>
</body>
</html>
