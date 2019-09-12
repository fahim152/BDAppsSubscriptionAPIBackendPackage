<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUssdSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ussd_subscription', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('AppId')->nullable();
            $table->string('subscriberId')->nullable();
            $table->string('ussdOperation')->nullable();
            $table->string('requestId')->nullable();
            $table->string('sessionId')->nullable();
            $table->string('message')->nullable();
            $table->string('encoding')->nullable();
            $table->string('version')->nullable();
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
        Schema::dropIfExists('ussd_subscription');
    }
}
