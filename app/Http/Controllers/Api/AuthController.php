<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{


    /**
     * Register a new student
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:students',
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
            'password' => 'required|string|min:6|confirmed',
            'phone_no' => 'nullable|string|max:20',
            'level' => 'nullable|integer',
            'department' => 'nullable|string|max:255',
            'university_code' => 'nullable|string|max:255|unique:students',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات التسجيل غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::create([
            'username' => $request->username,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_no' => $request->phone_no,
            'level' => $request->level,
            'department' => $request->department,
            'university_code' => $request->university_code,
               'image'    => $request->image ?? 'default.png',
        ]);

        $token = JWTAuth::fromUser($student);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الطالب بنجاح',
            'data' => [
                'token' => $token,
                'student' => [
                    'student_id' => $student->student_id,
                    'username' => $student->username,
                    'fullname' => $student->fullname,
                    'email' => $student->email,
                    'level' => $student->level,
                    'department' => $student->department,
                    'university_code' => $student->university_code,
                ]
            ]
        ], 201);
    }


    /**
     * Student login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'بيانات غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('username', 'password');

        // Find student by username
        $student = Student::where('username', $credentials['username'])->first();

        if (!$student || !Hash::check($credentials['password'], $student->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة'
            ], 401);
        }

        auth()->shouldUse('api');

        try {
            // Create JWT token
            $token = JWTAuth::fromUser($student);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل في إنشاء الرمز المميز'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تسجيل الدخول بنجاح',
            'data' => [
                'token' => $token,
                'student' => [
                    'student_id' => $student->student_id,
                    'username' => $student->username,
                    'fullname' => $student->fullname,
                    'level' => $student->level,
                    'department' => $student->department,
                    'university_code' => $student->university_code,
                    'image' => $student->image ? asset($student->image) : null
                ]
            ]
        ]);
    }

    /**
     * Student logout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل الخروج بنجاح'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل في تسجيل الخروج'
            ], 500);
        }
    }

    /**
     * Get authenticated student profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


   public function profile(Request $request)
{
    try {
        Log::info("Attempting to authenticate student...");
        $student = JWTAuth::parseToken()->authenticate();
        Log::info("Authentication result: " . ($student ? "Student found with ID: " . $student->student_id : "Student not found."));

        // Start new debugging block
        $studentIdFromToken = null;
        try {
            $studentIdFromToken = JWTAuth::parseToken()->getPayload()->get("sub");
            Log::info("Student ID from token payload: " . $studentIdFromToken);
        } catch (\Exception $e) {
            Log::error("Error getting student ID from token payload: " . $e->getMessage());
        }

        if ($studentIdFromToken) {
            $studentFromDb = \App\Models\Student::find($studentIdFromToken);
            Log::info("Student found in DB using ID from token: " . ($studentFromDb ? "Yes" : "No"));
            if ($studentFromDb) {
                $student = $studentFromDb; // Use this student if found
            }
        }
        // End new debugging block

        if (!$student) {
            return response()->json([
                "status" => "error",
                "message" => "المستخدم غير موجود"
            ], 404);
        }

        return response()->json([
            "status" => "success",
            "data" => [
                "student_id" => $student->student_id,
                "username" => $student->username,
                "fullname" => $student->fullname,
                "email" => $student->email,
                "phone_no" => $student->phone_no,
                "level" => $student->level,
                "department" => $student->department,
                "university_code" => $student->university_code,
                "image" => $student->image ? asset($student->image) : null,
                "created_at" => $student->created_at,
                "updated_at" => $student->updated_at
            ]
        ]);
    } catch (JWTException $e) {
        Log::error("JWT Exception in profile: " . $e->getMessage());
        return response()->json([
            "status" => "error",
            "message" => "رمز المصادقة غير صحيح"
        ], 401);
    }
}

    /**
     * Update student profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'fullname' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:students,email,' . $student->student_id . ',student_id',
                'phone_no' => 'sometimes|required|string|max:20',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['fullname', 'email', 'phone_no']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($student->image && file_exists(public_path($student->image))) {
                    unlink(public_path($student->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $student->student_id . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/students'), $imageName);
                $updateData['image'] = 'images/students/' . $imageName;
            }

            $student->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث الملف الشخصي بنجاح',
                'data' => [
                    'student_id' => $student->student_id,
                    'username' => $student->username,
                    'fullname' => $student->fullname,
                    'email' => $student->email,
                    'phone_no' => $student->phone_no,
                    'level' => $student->level,
                    'department' => $student->department,
                    'university_code' => $student->university_code,
                    'image' => $student->image ? asset($student->image) : null,
                    'updated_at' => $student->updated_at
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'رمز المصادقة غير صحيح'
            ], 401);
        }
    }

    /**
     * Refresh JWT token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'status' => 'success',
                'message' => 'تم تجديد الرمز المميز بنجاح',
                'data' => [
                    'token' => $newToken
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل في تجديد الرمز المميز'
            ], 401);
        }
    }

    /**
     * Change password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            if (!$student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'بيانات غير صحيحة',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check current password
            if (!Hash::check($request->current_password, $student->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'كلمة المرور الحالية غير صحيحة'
                ], 400);
            }

            // Update password
            $student->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تغيير كلمة المرور بنجاح'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'رمز المصادقة غير صحيح'
            ], 401);
        }
    }
}

