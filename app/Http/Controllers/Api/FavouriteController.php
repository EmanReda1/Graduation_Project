<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favourite;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;


class FavouriteController extends Controller
{
    /**
     * Get student's favorite books
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {


      //  dd("بدء تنفيذ دالة index في FavouriteController"); // done the problem is not here

        try {
            // 1. التحقق من مصادقة الطالب
            $student = JWTAuth::parseToken()->authenticate();
            dd($student); // عرض بيانات الطالب بعد المصادقة

            $perPage = $request->get("per_page", 15);
            // dd("per_page: " . $perPage); // عرض قيمة per_page

            // 2. جلب المفضلة مع علاقة الكتاب
            $favorites = Favourite::where("student_id", $student->student_id)
                ->with(["book:book_id,book_name,author,image,status,department"])
                ->orderBy("created_at", "desc")
                ->paginate($perPage);

            // dd($favorites); // عرض كائن الـ Paginator بعد جلب المفضلة

            // 3. تحويل البيانات إلى التنسيق المطلوب
            $favoritesData = $favorites->getCollection()->map(function($favorite) {
                // dd($favorite); // عرض كل عنصر مفضل قبل التحويل
                // تحقق من وجود الكتاب قبل محاولة الوصول إلى خصائصه
                if (!$favorite->book) {
                    // dd("كتاب غير موجود للمفضلة ID: " . $favorite->favorite_id); // إذا كان الكتاب غير موجود
                    return null;
                }
                return [
                    "favorite_id" => $favorite->favorite_id,
                    "book" => [
                        "book_id" => $favorite->book->book_id,
                        "book_name" => $favorite->book->book_name,
                        "author" => $favorite->book->author,
                        "department" => $favorite->book->department,
                        "status" => $favorite->book->status,
                        "image" => $favorite->book->image ? asset($favorite->book->image) : null
                    ],
                    "created_at" => $favorite->created_at
                ];
            })->filter();

            // dd($favoritesData); // عرض البيانات بعد التحويل وقبل الإرجاع

            return response()->json([
                "status" => "success",
                "data" => [
                    "favorites" => $favoritesData,
                    "pagination" => [
                        "current_page" => $favorites->currentPage(),
                        "total_pages" => $favorites->lastPage(),
                        "total_items" => $favorites->total(),
                        "per_page" => $favorites->perPage(),
                        "has_next" => $favorites->hasMorePages(),
                        "has_previous" => $favorites->currentPage() > 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            // إذا حدث أي خطأ، اعرض تفاصيله كاملة
            dd($e); // هذا سيعرض لكِ كل تفاصيل الـ Exception (الرسالة، الملف، السطر، تتبع الخطأ)
        }

}





    /**
     * Add book to favorites
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'book_id' => 'required|exists:books,book_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'معرف الكتاب غير صحيح',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if book is already in favorites
            $existingFavorite = Favourite::where('student_id', $student->student_id)
                ->where('book_id', $request->book_id)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب موجود بالفعل في المفضلة'
                ], 400);
            }

            // Add to favorites
            $favorite = Favourite::create([
                'student_id' => $student->student_id,
                'book_id' => $request->book_id
            ]);

            // Get book details
            $book = Book::find($request->book_id);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إضافة الكتاب للمفضلة بنجاح',
                'data' => [
                    'favorite_id' => $favorite->favorite_id,
                    'book' => [
                        'book_id' => $book->book_id,
                        'book_name' => $book->book_name,
                        'author' => $book->author,
                        'image' => $book->image ? asset($book->image) : null
                    ],
                    'created_at' => $favorite->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إضافة الكتاب للمفضلة'
            ], 500);
        }
    }

    /**
     * Remove book from favorites
     *
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($bookId)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $favorite = Favourite::where('student_id', $student->student_id)
                ->where('book_id', $bookId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'الكتاب غير موجود في المفضلة'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إزالة الكتاب من المفضلة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في إزالة الكتاب من المفضلة'
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a book
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $validator = Validator::make($request->all(), [
                'book_id' => 'required|exists:books,book_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'معرف الكتاب غير صحيح',
                    'errors' => $validator->errors()
                ], 422);
            }

            $favorite = Favourite::where('student_id', $student->student_id)
                ->where('book_id', $request->book_id)
                ->first();

            if ($favorite) {
                // Remove from favorites
                $favorite->delete();
                $message = 'تم إزالة الكتاب من المفضلة';
                $isFavorited = false;
            } else {
                // Add to favorites
                Favourite::create([
                    'student_id' => $student->student_id,
                    'book_id' => $request->book_id
                ]);
                $message = 'تم إضافة الكتاب للمفضلة';
                $isFavorited = true;
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'book_id' => $request->book_id,
                    'is_favorited' => $isFavorited
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في تحديث حالة المفضلة'
            ], 500);
        }
    }

    /**
     * Check if book is favorited by current student
     *
     * @param int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check($bookId)
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $isFavorited = Favourite::where('student_id', $student->student_id)
                ->where('book_id', $bookId)
                ->exists();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'book_id' => $bookId,
                    'is_favorited' => $isFavorited
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في التحقق من حالة المفضلة'
            ], 500);
        }
    }

    /**
     * Get favorite books count for current student
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function count()
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $count = Favourite::where('student_id', $student->student_id)->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'favorites_count' => $count
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في جلب عدد الكتب المفضلة'
            ], 500);
        }
    }

    /**
     * Clear all favorites for current student
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear()
    {
        try {
            $student = JWTAuth::parseToken()->authenticate();

            $deletedCount = Favourite::where('student_id', $student->student_id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم مسح جميع الكتب المفضلة بنجاح',
                'data' => [
                    'deleted_count' => $deletedCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ في مسح الكتب المفضلة'
            ], 500);
        }
    }
}

