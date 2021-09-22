<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16)->default('basic');
            $table->string('name', 64);
            $table->string('username', 32)->nullable();
            $table->string('email', 32)->nullable();
            $table->timestamp('email_verified_at', $precision=0)->nullable();
            $table->string('phone_number', 16)->nullable();
            $table->char('phone_region', 2)->nullable();
            $table->timestamp('phone_verified_at', $precision=0)->nullable();
            $table->string('password')->nullable();
            $table->rememberToken()->nullable();
            $table->string('temp_token')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedTinyInteger('status')->default('1');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes('deleted_at', $precision=0);
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
