<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdministratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('管理员 id');
            $table->timestamps();
            $table->string('account', 30)->unique()->comment('用户名');
            $table->char('password', 128)->comment('密码');
            $table->string('access_token', 255)->default('')->comment('access token');
            $table->string('access_refresh_token', 255)->default('')->comment('access refresh token');
            $table->timestamp('access_token_expires_in')->default($now = date("Y-m-d H:i:s"))->comment('access token 过期时间');
            $table->index(['id', 'account']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('administrators');
    }
}
