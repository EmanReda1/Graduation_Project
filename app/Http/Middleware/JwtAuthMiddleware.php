<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Check if token is present
            $token = JWTAuth::getToken();
            
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'رمز المصادقة مطلوب',
                    'error_code' => 'TOKEN_MISSING'
                ], 401);
            }

            // Authenticate the user
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'المستخدم غير موجود',
                    'error_code' => 'USER_NOT_FOUND'
                ], 401);
            }

            // Check if user is a student (additional security check)
            if (!$user instanceof \App\Models\Student) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'غير مصرح بالوصول',
                    'error_code' => 'UNAUTHORIZED_USER_TYPE'
                ], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'انتهت صلاحية رمز المصادقة',
                'error_code' => 'TOKEN_EXPIRED'
            ], 401);
            
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'رمز المصادقة غير صحيح',
                'error_code' => 'TOKEN_INVALID'
            ], 401);
            
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في رمز المصادقة',
                'error_code' => 'TOKEN_ERROR'
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في المصادقة',
                'error_code' => 'AUTHENTICATION_ERROR'
            ], 500);
        }

        return $next($request);
    }
}

