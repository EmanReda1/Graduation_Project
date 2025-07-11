<?php
// app/Http/Controllers/BookDepartmentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookDepartmentController extends Controller
{
    private $mlApiUrl = 'http://localhost:5000/predict';

    public function predictDepartment(Request $request)
    {
        try {
            // التحقق من البيانات المدخلة
            $request->validate([
                'book_name' => 'required|string|max:255',
                'book_summary' => 'required|string|max:2000'
            ]);

            // إرسال البيانات للـ ML API
            $response = Http::timeout(30)->post($this->mlApiUrl, [
                'book_name' => $request->input('book_name'),
                'book_summary' => $request->input('book_summary')
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json([
                    'success' => true,
                    'predicted_department' => $result['predicted_department']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'خطأ في الاتصال بخدمة التنبؤ'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('ML Prediction Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في عملية التنبؤ'
            ], 500);
        }
    }

    // الطريقة المطلوبة: استخدام shell_exec مع predict_department.py
    public function predictDepartmentCLI(Request $request)
    {
        try {
            $request->validate([
                'book_name' => 'required|string|max:255',
                'book_summary' => 'required|string|max:2000'
            ]);

            $bookName = escapeshellarg($request->input('book_name'));
            $bookSummary = escapeshellarg($request->input('book_summary'));

            // المسار للـ Python script
            $pythonScript = base_path('ml_api/predict_department.py');

            // تأكد من وجود الملف
            if (!file_exists($pythonScript)) {
                return response()->json([
                    'success' => false,
                    'error' => 'ملف التنبؤ غير موجود'
                ], 500);
            }

            // تشغيل الـ Python script
            $command = "python3 \"{$pythonScript}\" {$bookName} {$bookSummary} 2>&1";
            $output = shell_exec($command);

            if ($output && !str_contains($output, 'Error:')) {
                return response()->json([
                    'success' => true,
                    'predicted_department' => trim($output)
                ]);
            } else {
                Log::error('Python Script Error: ' . $output);
                return response()->json([
                    'success' => false,
                    'error' => 'فشل في تنفيذ عملية التنبؤ: ' . trim($output)
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('CLI Prediction Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في عملية التنبؤ'
            ], 500);
        }
    }

    // طريقة مساعدة للتحقق من Python و المكتبات
    public function checkPythonSetup()
    {
        $pythonScript = base_path('ml_api/predict_department.py');
        $testCommand = "python \"{$pythonScript}\" \"test book\" \"test summary\" 2>&1";
        $output = shell_exec($testCommand);

        return response()->json([
            'script_exists' => file_exists($pythonScript),
            'test_output' => $output
        ]);
    }
}
