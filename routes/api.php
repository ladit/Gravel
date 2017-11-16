<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 注册
Route::post('/user/registration', 'UserController@create');

// 登录
Route::put('/user/login', 'UserController@login');

// 更新用户 Token
Route::put('/users/{user}/access_token', 'UserController@updateAccessToken');

// 需要验证
Route::middleware('auth')->group(function () {

    /******** auth *********/

    // 获取上传七牛云 Token
    Route::get('/users/{user}/qiniu_token', 'UserController@getQiniuToken');

    // 更新七牛 Token
    Route::put('/users/{user}/qiniu_token', 'UserController@updateQiniuToken');

    /******** auth *********/

    /******** user *********/

    // 修改用户名
    Route::put('/users/{user}/account', 'UserController@updateAccount');

    // 修改密码
    Route::put('/users/{user}/password', 'UserController@updatePassword');

    // 获取头像 URL
    Route::get('/users/{user}/avatar', 'UserController@getAvatarUrl');

    // 修改头像 URL
    Route::put('/users/{user}/avatar', 'UserController@updateAvatarUrl');

    /******** user *********/

    /******** note *********/

    // 恢复记录
    Route::get('/users/{user}/notes', 'NoteController@restore');

    // 存储记录
    Route::post('/users/{user}/notes', 'NoteController@store');

    // 修改记录
    Route::put('/users/{user}/notes/{note}', 'NoteController@update');

    // 删除记录
    Route::delete('/users/{user}/notes/{note}', 'NoteController@delete');

    // 获取流星
    Route::get('/users/{user}/meteors', 'NoteController@getMeteors');

    /******** note *********/

    /******** article *********/

    // 获取文章
    // /users/:id/articles?all_random=1
    // 若 all_random=1，返回的都是随机文章，默认值为 1
    Route::get('/users/{user}/articles', 'ArticleController@get');

    // 存储文章
    Route::put('/users/{user}/articles', 'ArticleController@store');

    /******** article *********/
});


/******** test *********/

Route::middleware('auth')->get('/users/{user}/test', function (App\User $user) {
    return response()->json([
        'error_code' => 200,
        'data' => [
            'account' => $user->account
        ]
    ]);
});

Route::post('/test', function (Request $request) {
    $account = 'ladit';
    return response()->json([
        '$tokenExpireTime' => config('app.token_expires_seconds')
    ]);
//    $tokenExpireTime = date('Y-m-d H:i:s', time()+604800);
//    $accessTokenInfo = [
//        'uniqid' => uniqid('', true),
//        'account' => $account,
//        'tokenExpireTime' => $tokenExpireTime
//    ];
//    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
//    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
//    mcrypt_encrypt(MCRYPT_RIJNDAEL_128, config('APP_KEY'),
//        implode(',', $accessTokenInfo), MCRYPT_MODE_CBC, $iv);
    //return $accessTokenInfo;
//    $refreshTokenInfo = [
//        'uniqid' => uniqid('', true),
//        'account' => $account,
//        'tokenExpireTime' => $tokenExpireTime
//    ];
//    $accessToken = base64_encode(implode(',', $accessTokenInfo));
//    $refreshToken = base64_encode(implode(',', $refreshTokenInfo));
//    $data = [
//        '$accessToken' => $accessToken,
//        '$refreshToken' => $refreshToken,
//    ];
//    return $data;
    //return $request->input('id');
    //return $request->all();
    //return $request->header('Authorization');

    //    if ($request->isJson()) {
    //        //return $request->header('Authorization');
    //        return $request->id;
    //    }
    //return $request->all();
});

Route::get('/admin', 'AdministratorController@index');

Route::get('/user', 'UserController@index');

Route::get('/users/{user}', function (App\User $user) {
    return $user;
});

Route::get('/article', 'ArticleController@index');

Route::get('/emotion', 'EmotionController@index');

Route::get('/note', 'NoteController@index');

Route::get('/notes/{note}', function (App\Note $note) {
    return $note;
});

/******** test *********/