<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Qiniu\Auth;

class UserController extends Controller
{
    /**
     * 注册账号
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // header 中 Content-Type 不为 application/json（body 内容非 JSON），返回 400
        if (!$request->isJson()) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'If method is not GET, body should be json.'
            ]);
        }

        $account = $request->input('account');
        $password = $request->input('password');

        if (is_null($account) or is_null($password)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require account and password.'
            ]);
        }

        if (!$this->check('account', $account)
            or !$this->check('password', $password)) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Account or password format error.'
            ]);
        }

        $existedUser = User::where('account', $account)->first();
        if ($existedUser) {
            return response()->json([
                'error_code' => 409,
                'error_message' => 'Account exist.'
            ]);
        }

        $user = new User;
        $user->account = $account;
        $user->password = Hash::make($password);
        $user->save();

        $this->refreshToken($user);

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'account' => $account
            ]
        ]);
    }

    /**
     * 登录账号
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // header 中 Content-Type 不为 application/json（body 内容非 JSON），返回 400
        if (!$request->isJson()) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'If method is not GET, body should be json.'
            ]);
        }

        $account = $request->input('account');
        $password = $request->input('password');

        if (is_null($account) or is_null($password)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require account and password.'
            ]);
        }

        $user = User::where('account', $account)->first();
        if (!$user) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'User not exist.'
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'error_code' => 401,
                'error_message' => 'Wrong password.'
            ]);
        }

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'account' => $account,
                'create_time' => $user->created_at->toDateTimeString(),
                'access_token' => $user->access_token,
                'refresh_token' => $user->access_refresh_token,
                'expire_time' => $user->access_token_expires_in
            ]
        ]);
    }

    /**
     * 更新用户 Token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccessToken(Request $request, User $user)
    {
        $tokenInfo = $this->refreshToken($user);

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'access_token' => $tokenInfo['access_token'],
                'refresh_token' => $tokenInfo['refresh_token'],
                'expire_time' => $tokenInfo['expire_time']
            ]
        ]);
    }

    /**
     * 获取上传七牛云 Token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getQiniuToken(Request $request, User $user)
    {
        $accessKey = config('app.qiniu_access_key');
        $secretKey = config('app.qiniu_secret_key');
        $bucketName = config('app.qiniu_bucket_name');
        $auth = new Auth($accessKey, $secretKey);
        $upToken = $auth->uploadToken($bucketName, null, config('app.token_expires_seconds'));
        $tokenExpireTime = date('Y-m-d H:i:s',
            time() + config('app.token_expires_seconds'));

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'qiniu_token' => $upToken,
                'expire_time' => $tokenExpireTime
            ]
        ]);
    }

    /**
     * 修改用户名
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request, User $user)
    {
        $newAccount = $request->account;

        if (is_null($newAccount)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require account.'
            ]);
        }

        if (!$this->check('account', $newAccount)) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Account format error.'
            ]);
        }

        $existedUser = User::where('account', $newAccount)->first();
        if ($existedUser) {
            return response()->json([
                'error_code' => 409,
                'error_message' => 'Account exist.'
            ]);
        }

        $user->account = $newAccount;
        $user->save();

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'account' => $newAccount
            ]
        ]);
    }

    /**
     * 修改密码
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request, User $user)
    {
        $oldPassword = $request->old_password;
        $newPassword = $request->new_password;

        if (is_null($oldPassword) or is_null($newPassword)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require new and old password.'
            ]);
        }

        if (!$this->check('password', $oldPassword) or
            !$this->check('password', $newPassword)) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Password format error.'
            ]);
        }

        if (!Hash::check($oldPassword, $user->password)) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Old password error.'
            ]);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id
            ]
        ]);
    }

    /**
     * 获取头像 URL
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAvatarUrl(Request $request, User $user)
    {
        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'avatar_url' =>$user->avatar_url
            ]
        ]);
    }

    /**
     * 修改头像 URL
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAvatarUrl(Request $request, User $user)
    {
        $avatarUrl = $request->avatar_url;

        if (is_null($avatarUrl)) {
            return response()->json([
                'error_code' => 400,
                'error_message' => 'Require avatar url.'
            ]);
        }

        if (strlen($avatarUrl) > 150) {
            return response()->json([
                'error_code' => 403,
                'error_message' => 'Avatar url too long.'
            ]);
        }

        $user->avatar_url = $avatarUrl;
        $user->save();

        return response()->json([
            'error_code' => 200,
            'data' => [
                'user_id' => $user->id,
                'avatar_url' =>$avatarUrl
            ]
        ]);
    }

    /**
     * 生成用户 Token、刷新 Token、Token 过期时间
     *
     * @param  User  $user
     * @return array $tokenInfo
     */
    public function refreshToken(User $user)
    {
        $tokenExpireTime = date('Y-m-d H:i:s',
            time() + config('app.token_expires_seconds'));
        $accessTokenInfo = [
            'uniqid' => uniqid('', true),
            'account' => $user->account,
            'tokenExpireTime' => $tokenExpireTime
        ];
        $refreshTokenInfo = [
            'uniqid' => uniqid('', true),
            'account' => $user->account,
            'tokenExpireTime' => $tokenExpireTime
        ];
        $accessToken = base64_encode(implode(',', $accessTokenInfo));
        $refreshToken = base64_encode(implode(',', $refreshTokenInfo));

        $user->access_token = $accessToken;
        $user->access_refresh_token = $refreshToken;
        $user->access_token_expires_in = $tokenExpireTime;
        $user->save();

        $tokenInfo = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expire_time' => $tokenExpireTime
        ];
        return $tokenInfo;
    }

    /**
     * 格式检查
     *
     * @param string $action
     * @param mixed $data
     * @return bool
     */
    public function check($action, $data)
    {
        switch ($action) {
            case 'account':
                //登录名应为 6 到 20 位的字母、数字、下划线、中文组合，且以字母或中文作为第一个字符
                if (preg_match('/^[[:alpha:]\x{4e00}-\x{9fa5}][\-\w\x{4e00}-\x{9fa5}]{2,19}$/u', $data)) {
                    return true;
                }
                return false;
                break;

            case 'password':
                //密码应为 6 到 20 位的字母、数字、符号~!@#$%^&*()_=+|,.?:;'"{}[]-/\组合
                if (preg_match('/^[~!@#$%^&*()_=+|,.?:;\'"{}[\]\-\/\\\\\w]{6,20}$/', $data)) {
                    return true;
                }
                return false;
                break;

            default:

                break;
        }
    }
}
