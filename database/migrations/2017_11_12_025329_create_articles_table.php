<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('文章 id');
            $table->timestamps();
            $table->string('publish_time', 50)->default('')->comment('发表时间');
            $table->string('url', 150)->default('')->comment('内容 URL');
            $table->string('title', 200)->default('')->comment('标题');
            $table->string('author', 200)->default('')->comment('作者');
            $table->text('content')->nullable()->comment('文本内容');
            $table->index(['id', 'url']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
