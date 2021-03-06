<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProxiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('host')->nullable();
            $table->string('port')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('country')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->default(""); // system, admin
            $table->string('using_type')->nullable(); // rent, own
            $table->integer('price')->nullable();
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
        Schema::dropIfExists('proxies');
    }
}
