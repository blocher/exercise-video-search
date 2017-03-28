<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('youtube_id',255);
            $table->string('etag',255);
            $table->string('video_id',255);
            $table->dateTime('published_at');
            $table->string('title',255);
            $table->longText('description');
            $table->integer('position')->unsigned();
            $table->integer('duration')->unsigned()->default(0);
            $table->integer('view_count')->unsigned()->default(0);
            $table->integer('like_count')->unsigned()->default(0);
            $table->integer('dislike_count')->unsigned()->default(0);
            $table->integer('favorite_count')->unsigned()->default(0);
            $table->integer('comment_count')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
}
