<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\BorrowingRequestController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// إضافة مهمة إلغاء الحجوزات المنتهية الصلاحية
Schedule::call([BorrowingRequestController::class, 'cancelExpiredReservations'])
    ->hourly()
    ->name('cancel-expired-reservations')
    ->description('Cancel book reservations that have expired after 24 hours');

// ->hourly()
// ->everyMinute() للاختبار
// ->daily() مرة واحدة يوميًا
// ->everyFiveMinutes() كل 5 دقائق
