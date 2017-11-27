<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTdBotSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TDsession', function (Blueprint $table) {
            $table->increments('id');
            $table->string('s_user_id');
            $table->string('s_token');
            $table->longText('td_token')->nullable();
            $table->unsignedInteger('td_searches')->default(0);
            $table->unsignedInteger('td_tickets')->default(0);
            $table->unsignedInteger('td_other')->default(0);
            $table->unsignedInteger('s_showtickets')->default(0);
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
        Schema::dropIfExists('TDsession');
    }
}
