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

// 需要验证
Route::middleware('auth')->group(function () {

    /******** auth *********/
    // 更新用户 Token
    Route::put('/users/{user}/access_token', 'UserController@updateAccessToken');

    // 获取上传七牛云 Token，有效期7天
    Route::get('/users/{user}/qiniu_token', 'UserController@getQiniuToken');

    /******** auth *********/

    /******** message *********/

    // 获取新消息
    // /users/:id/messages?all=0
    // 若 all=1，返回所有历史消息，默认值为 0
    Route::get('/users/{user}/messages', 'UserController@getMessages');

    /******** message *********/

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
    // /users/:id/notes?all=0&year=2017&month=12&day=05
    // 若 all=1，返回所有记录
    // 若 all=0，返回 year、month、day 指定的记录，年4位，月日2位
    // all 默认值为 0
    Route::get('/users/{user}/notes', 'NoteController@restore');

    // 存储记录
    Route::post('/users/{user}/notes', 'NoteController@store');

    // 修改记录
    Route::put('/users/{user}/notes/{note}', 'NoteController@update');

    // 删除记录
    Route::delete('/users/{user}/notes/{note}', 'NoteController@delete');

    // 获取流星
    Route::get('/users/{user}/meteors', 'NoteController@getMeteors');

    // 点亮流星
    Route::put('/users/{user}/meteors/{note}/upvotation', 'NoteController@upvoteMeteor');

    // 取消点亮流星
    Route::put('/users/{user}/meteors/{note}/cancelUpvotation', 'NoteController@cancelUpvoteMeteor');

    // 举报流星
    Route::put('/users/{user}/meteors/{note}/report', 'NoteController@reportMeteor');

    /******** note *********/

    /******** article *********/

    // 获取文章
    // /users/:id/articles?favorite=0&all_random=1
    // 若 favorite=1，无视 all_random 参数，返回收藏的文章，默认值为 0
    // 若 all_random=0，返回推荐的文章
    // 若 all_random=1，返回的都是随机文章
    // all_random 默认值为 1
    Route::get('/users/{user}/articles', 'ArticleController@get');

    // 更新文章
    Route::put('/users/{user}/articles/{article}', 'ArticleController@update');

    // 收藏文章
    Route::put('/users/{user}/articles/{article}/favorite', 'ArticleController@favorite');

    // 取消收藏文章
    Route::put('/users/{user}/articles/{article}/cancelFavorite', 'ArticleController@cancelFavorite');

    // 收集阅读文章时间
    Route::post('/users/{user}/articles/{article}/time', 'ArticleController@time');

    // 上传用于分析的文章 id
    Route::put('/users/{user}/articles/{article}/read', 'ArticleController@lastRead');

    /******** article *********/
});
