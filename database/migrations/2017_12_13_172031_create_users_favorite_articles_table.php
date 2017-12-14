<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersFavoriteArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_favorite_articles', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('用户收藏文章 id');
            $table->timestamps();
            $table->unsignedInteger('user_id')->comment('用户 id');
            $table->unsignedInteger('article_id')->comment('文章 id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_favorite_articles');
    }
}
