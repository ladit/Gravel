<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_articles', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('用户文章 id');
            $table->timestamps();
            $table->unsignedInteger('user_id')->comment('用户 id');
            $table->unsignedInteger('article_id')->comment('文章 id');
            $table->double('coefficient', 8, 2)->default(0.0)->comment('匹配系数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_articles');
    }
}
