<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->unsignedInteger('fixture_id')->primary();
            $table->dateTime('fixture_date');
            $table->unsignedInteger('league_id');
            $table->foreign('league_id')->references('league_id')->on('leagues');
            $table->string('home_team', 100);
            $table->string('away_team', 100);
            $table->float('home_team_odds', 4, 2);
            $table->float('draw_odds', 4, 2);
            $table->float('away_team_odds', 4, 2);
            $table->integer('half_time_home_goals')->default(0);
            $table->integer('half_time_away_goals')->default(0);
            $table->integer('full_time_home_goals')->default(0);
            $table->integer('full_time_away_goals')->default(0);
            $table->integer('extra_time_home_goals')->default(0);
            $table->integer('extra_time_away_goals')->default(0);
            $table->integer('penalty_home_goals')->default(0);
            $table->integer('penalty_away_goals')->default(0);
            $table->integer('results')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('fixtures');
    }
}
