<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('用户 id');
            $table->timestamps();
            $table->string('account', 30)->unique()->comment('用户名');
            $table->string('password')->comment('密码');
            $table->string('access_token')->default('')->comment('access token');
            $table->string('access_refresh_token')->default('')->comment('access refresh token');
            $table->timestamp('access_token_expires_in')->default($now = date("Y-m-d H:i:s"))->comment('access token 过期时间');
            $table->string('qiniu_token')->default('')->comment('qiniu token');
            $table->string('qiniu_refresh_token')->default('')->comment('qiniu refresh token');
            $table->timestamp('qiniu_token_expires_in')->default($now = date("Y-m-d H:i:s"))->comment('qiniu token 过期时间');
            $table->string('safe_question', 100)->default('')->comment('密保问题');
            $table->string('safe_question_answer', 50)->default('')->comment('密保问题答案');
            $table->string('mail', 100)->default('')->comment('邮箱');
            $table->unsignedInteger('phone_number')->default(0)->comment('电话号码');
            $table->string('nick_name', 100)->default('')->comment('昵称');
            $table->string('avatar_url', 150)->default('')->comment('头像 URL');
            $table->unsignedTinyInteger('is_deleted')->default(0)->comment('是否被删除');
            $table->unsignedTinyInteger('is_blocked')->default(0)->comment('是否被封禁');
            $table->index(['id', 'access_token', 'account']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
