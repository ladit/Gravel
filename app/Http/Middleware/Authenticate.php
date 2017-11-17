<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 非 GET 方式，body 内容非 JSON，返回 400
        if ($request->method() != 'GET' and !$request->isJson()) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'If method is not GET, body should be json.'
            ]);
        }

        // header 无 Authorizations 字段，返回 400
        $token = $request->header('Authorizations');
        if (!$token) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require Authorizations in header.'
            ]);
        }

        // 路由中用户错误，返回 400
        if (!is_numeric($request->segment(3))) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'User ID error.'
            ]);
        }

        // 用户不存在，返回 403
        $user = $request->user;
        if (!$user) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'User not exist.'
            ]);
        }

        // Refresh token 过期（14天过期）
        if ($request->segment(3) == 'access_token') {
            if (strtotime($user->access_token_expires_in) + config('app.token_expires_seconds') < time()) {
                return response()->json([
                    'error_code' => 403,
                    'error_message' => 'Refresh token expired.'
                ]);
            }

            // Refresh token 验证
            if ($token != $user->access_refresh_token) {
                return response()->json([
                    'error_code' => 401,
                    'error_message' => 'Wrong access refresh token.'
                ]);
            }
        }

        if (strtotime($user->access_token_expires_in) < time()) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Access token expired.'
            ]);
        }

        // Token 验证
        if ($token != $user->access_token) {
            return response()->json([
                'error_code' => 401,
                'error_message' => 'Wrong access token.'
            ]);
        }

        return $next($request);
    }
}
