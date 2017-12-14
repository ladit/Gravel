<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('笔记 id');
            $table->timestamps();
            $table->unsignedInteger('user_id')->comment('用户 id');
            $table->unsignedInteger('upvote_quantity')->comment('点赞数');
            $table->unsignedTinyInteger('is_shared')->default(0)->comment('是否被分享');
            $table->unsignedTinyInteger('is_reported')->default(0)->comment('是否被举报');
            $table->unsignedTinyInteger('is_deleted')->default(0)->comment('是否被删除');
            $table->unsignedTinyInteger('is_blocked')->default(0)->comment('是否被封禁');
            $table->string('url', 150)->default('')->comment('内容 URL');
            $table->text('content')->comment('文本内容');
            $table->index(['id', 'user_id', 'url']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notes');
    }
}
