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
        // body 内容非 JSON 则返回 400 错误
        if (!$request->isJson()) {
            return response()->json([
                'error_code' => 400
            ]);
        }

        // 验证路由路径，访问错误则返回 404
        $requestSegments = $request->segments();
        if ($requestSegments[0] != 'api' or $requestSegments[1] != 'users' or !is_numeric($requestSegments[2])) {
            return response()->json([
                'error_code' => 400
            ]);
        }

        // Token 验证
        try {
            $user = User::findOrFail($requestSegments[2]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error_code' => 403
            ]);
        }
        if ($request->header('Authorization') != $user->access_token) {
            return response()->json([
                'error_code' => 401
            ]);
        }

        return $next($request);
    }
}
