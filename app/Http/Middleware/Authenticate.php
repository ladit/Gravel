<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App\Note;
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
        $user = $request->user;
        $requestSegments = $request->segments();
        $token = $request->header('Authorization');

        // 非 GET 方式，header 中 Content-Type 不为 application/json（body 内容非 JSON），返回 400
        if ($request->method() != 'GET' and !$request->isJson()) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'If method is not GET, body should be json.'
            ]);
        }

        // header 无 Authorization 字段，返回 400
        if (!$token) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require Authorization in header.'
            ]);
        }

        // 路由中用户 id 错误，返回 404
        if (!is_numeric($requestSegments[2])) {
            return response()->json([
                'error_code' => 404,
                'error_message' => 'User ID error.'
            ]);
        }

        if ($requestSegments[3] == 'access_token') {
            // Refresh token 验证
            if ($token != $user->access_refresh_token) {
                return response()->json([
                    'error_code' => 401,
                    'error_message' => 'Wrong access refresh token.'
                ]);
            }

            // 检查 Refresh token 过期（14 天过期）
            if (strtotime($user->access_token_expires_in)
                + config('app.token_expires_seconds') < time()) {
                User::refreshToken($user);
                return response()->json([
                    'error_code' => 403,
                    'error_message' => 'Refresh token expired.'
                ]);
            }
        } else {
            // Token 验证
            if ($token != $user->access_token) {
                return response()->json([
                    'error_code' => 401,
                    'error_message' => 'Wrong access token.'
                ]);
            }

            // 检查 Access token 过期（7 天过期）
            if (strtotime($user->access_token_expires_in) < time()) {
                return response()->json([
                    'error_code' => 403,
                    'error_message' => 'Access token expired.'
                ]);
            }
        }

        // 路由中记录 id 错误，返回 404
        if ($requestSegments[3] == 'notes' and isset($requestSegments[4])) {
            if (!is_numeric($requestSegments[4])) {
                return response()->json([
                    'error_code' => 404,
                    'error_message' => 'Note ID error.'
                ]);
            }
            $note = $request->note;
            if ($note->user->id != $user->id) {
                return response()->json([
                    'error_code' => 404,
                    'error_message' => 'Note ID error.'
                ]);
            }
        }

        // 路由中文章 id 错误，返回 404
        if ($requestSegments[3] == 'articles'
            and isset($requestSegments[4])
            and !is_numeric($requestSegments[4])) {
            return response()->json([
                'error_code' => 404,
                'error_message' => 'Article ID error.'
            ]);
        }

        return $next($request);
    }
}
