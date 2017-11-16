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
        // body 内容非 JSON、header 无 Authorization 字段、路由路径错误，返回 400
        $requestSegments = $request->segments();
        //
        if (!$request->isJson() or !$request->header('Authorization')
            or !is_numeric($requestSegments[2])) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Body should be json, router should be right, require authorization.'
            ]);
        }

        $user = $request->user;
        $access_token = $request->header('Authorization');

        if (!$user) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'User not exist.'
            ]);
        }

        // Token 过期
        if (strtotime($user->access_token_expires_in) < time()) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Access token expired.'
            ]);
        }

        // Token 验证
        if ($access_token != $user->access_token) {
            return response()->json([
                'error_code' => 401,
                'error_message' => 'Wrong access token.'
            ]);
        }

        return $next($request);
    }
}
