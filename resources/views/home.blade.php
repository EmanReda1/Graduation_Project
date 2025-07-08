@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام مكتبة الكلية</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="app-container">
        <!-- الشريط الجانبي -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>نظام مكتبة الكلية</h3>
                <p>مرحباً، {{ Auth::user()->name }}</p>
            </div>
            <!--nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="{{ route('books.index') }}"><i class="fas fa-book"></i> كل الكتب</a></li>
                    <li><a href="{{ route('students.index') }}"><i class="fas fa-users"></i> كل الطلاب</a></li>
                    <li><a href="{{ route('visits.index') }}"><i class="fas fa-history"></i> سجل الزيارات</a></li>
                    <li><a href="{{ route('reading-records.index') }}"><i class="fas fa-book-reader"></i> سجل القراءة</a></li>
                    <li><a href="{{ route('borrowing-records.index') }}"><i class="fas fa-exchange-alt"></i> سجل الاستعارة</a></li>
                    <li><a href="{{ route('borrowed-books.index') }}"><i class="fas fa-book-open"></i> الكتب المستعارة</a></li>
                    <li><a href="{{ route('reading-requests.index') }}"><i class="fas fa-clipboard-list"></i> طلبات القراءة</a></li>
                    <li><a href="{{ route('borrowing-requests.index') }}"><i class="fas fa-hand-holding"></i> طلبات الاستعارة</a></li>
                    <li><a href="{{ route('exams.index') }}"><i class="fas fa-file-alt"></i> إضافة امتحان</a></li>
                    <li><a href="{{ route('projects.index') }}"><i class="fas fa-project-diagram"></i> إضافة مشروع</a></li>
                    <li><a href="{{ route('statistics.index') }}"><i class="fas fa-chart-bar"></i> إحصائيات</a></li>
                </ul>
            </nav>
        </aside!-->

        <!-- المحتوى الرئيسي -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
@endsection
