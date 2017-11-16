<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesEmotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles_emotions', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('文章情绪 id');
            $table->timestamps();
            $table->unsignedInteger('article_id')->comment('文章 id');
            $table->unsignedInteger('emotion_id')->comment('情绪 id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles_emotions');
    }
}
