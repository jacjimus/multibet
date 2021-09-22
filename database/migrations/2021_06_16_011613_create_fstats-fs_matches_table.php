<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFstatsFsMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fstats-fs_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fs_wdw_id')->nullable();
            $table->unsignedBigInteger('sp_match_id')->nullable();
            $table->unsignedBigInteger('date');
            $table->string('league_name');
            $table->string('league_url');
            $table->string('country', 64);
            $table->unsignedInteger('comp_id');
            $table->string('h2h_url');
            $table->unsignedBigInteger('time');
            $table->unsignedInteger('home_id');
            $table->string('home_url');
            $table->string('home_name');
            $table->float('home_form', $total_digits=8, $decimal_digits=2);
            $table->unsignedInteger('home_score')->nullable();
            $table->unsignedInteger('away_id');
            $table->string('away_url');
            $table->string('away_name');
            $table->float('away_form', $total_digits=8, $decimal_digits=2);
            $table->unsignedInteger('away_score')->nullable();
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
        Schema::dropIfExists('fstats-fs_matches');
    }
}
