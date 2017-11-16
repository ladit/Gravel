<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesEmotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notes_emotions', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('记录情绪 id');
            $table->timestamps();
            $table->unsignedInteger('note_id')->comment('记录 id');
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
        Schema::dropIfExists('notes_emotions');
    }
}
