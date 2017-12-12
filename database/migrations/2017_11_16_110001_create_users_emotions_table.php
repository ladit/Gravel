<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersEmotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_emotions', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('用户情绪 id');
            $table->timestamps();
            $table->unsignedInteger('user_id')->comment('用户 id');
            $table->unsignedInteger('emotion_id')->comment('情绪 id');
            $table->double('coefficient', 8, 2)->default(0.0)->comment('情绪系数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_emotions');
    }
}
