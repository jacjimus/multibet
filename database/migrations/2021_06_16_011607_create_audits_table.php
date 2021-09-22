<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('action', 64);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('auth_user_id')->nullable();
            $table->ipAddress('auth_ip')->nullable();
            $table->string('auth_useragent', 256)->nullable();
            $table->string('data_model')->nullable();
            $table->unsignedBigInteger('data_id')->nullable();
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();
            $table->uuidMorphs('auditable');
            $table->timestamps();
            $table->softDeletes('deleted_at', $precision=0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('auth_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audits');
    }
}
