<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ref', 64)->nullable();
            $table->timestamp('date', $precision=0);
            $table->string('type', 16);
            $table->float('amount', $total_digits=8, $decimal_digits=2);
            $table->string('currency', 8);
            $table->string('provider', 32);
            $table->string('name', 64);
            $table->string('phone', 16)->nullable();
            $table->string('email', 32)->nullable();
            $table->string('account', 64)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
