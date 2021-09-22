<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFstatsFsWdwsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fstats-fs_wdws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('date');
            $table->unsignedBigInteger('fs_match_id')->nullable();
            $table->string('h2h_url');
            $table->string('fixture');
            $table->float('home_win', $total_digits=8, $decimal_digits=2);
            $table->float('away_win', $total_digits=8, $decimal_digits=2);
            $table->float('draw_win', $total_digits=8, $decimal_digits=2);
            $table->float('home_odds', $total_digits=8, $decimal_digits=2);
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
        Schema::dropIfExists('fstats-fs_wdws');
    }
}
