<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFstatsSpCorrelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fstats-sp_correlations', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('sp_name');
            $table->string('fs_name');
            $table->float('similarity', $total_digits=8, $decimal_digits=2);
            $table->float('sim_avg', $total_digits=8, $decimal_digits=2);
            $table->float('teams_avg', $total_digits=8, $decimal_digits=2);
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
        Schema::dropIfExists('fstats-sp_correlations');
    }
}
