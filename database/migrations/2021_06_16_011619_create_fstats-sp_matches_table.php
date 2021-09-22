<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFstatsSpMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fstats-sp_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('date');
            $table->unsignedBigInteger('fs_match_id')->nullable();
            $table->string('league_name');
            $table->string('country', 64);
            $table->unsignedInteger('comp_id');
            $table->unsignedInteger('match_id');
            $table->unsignedInteger('sms_id');
            $table->unsignedBigInteger('time');
            $table->unsignedInteger('home_id');
            $table->string('home_name');
            $table->float('home_odds', $total_digits=8, $decimal_digits=2);
            $table->unsignedInteger('away_id');
            $table->string('away_name');
            $table->float('away_odds', $total_digits=8, $decimal_digits=2);
            $table->float('draw_odds', $total_digits=8, $decimal_digits=2);
            $table->timestamps();

            $table->foreign('fs_match_id')->references('id')->on('fstats-fs_matches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fstats-sp_matches');
    }
}
