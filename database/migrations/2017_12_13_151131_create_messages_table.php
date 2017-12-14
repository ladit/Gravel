<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('消息 id');
            $table->timestamps();
            $table->unsignedInteger('note_id')->comment('记录 id');
            $table->unsignedInteger('user_id')->comment('用户 id');
            $table->unsignedTinyInteger('is_upvoted')->default(0)->comment('是否被点赞');
            $table->unsignedTinyInteger('is_reported')->default(0)->comment('是否被举报');
            $table->unsignedTinyInteger('is_sent')->default(0)->comment('是否已经通知');
            $table->index(['id', 'note_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
