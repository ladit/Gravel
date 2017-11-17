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

    // 获取上传七牛云 Token，有效期7天
    Route::get('/users/{user}/qiniu_token', 'UserController@getQiniuToken');

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

    // 更新文章
    Route::put('/users/{user}/articles/{article}', 'ArticleController@update');

    /******** article *********/
});
