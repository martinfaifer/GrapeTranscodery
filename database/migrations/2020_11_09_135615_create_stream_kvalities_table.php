<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamKvalitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_kvalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('format_id')->index();
            $table->string('kvalita');
            $table->string('minrate');
            $table->string('maxrate');
            $table->string('bitrate');
            $table->string('scale');
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
        Schema::dropIfExists('stream_kvalities');
    }
}
