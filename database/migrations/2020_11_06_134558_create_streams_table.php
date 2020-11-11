<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('pid')->nullable();
            $table->string('nazev');
            $table->string('src')->index();
            $table->string('dst')->index();
            $table->string('dst1_resolution')->nullable();
            $table->string('dst2')->nullable();
            $table->string('dst2_resolution')->nullable();
            $table->string('dst3')->nullable();
            $table->string('dst3_resolution')->nullable();
            $table->string('dst4')->nullable();
            $table->string('dst4_resolution')->nullable();
            $table->string('format')->default("H.264");
            $table->longText('script');
            $table->string('transcoder')->index();
            $table->string('status')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streams');
    }
}
