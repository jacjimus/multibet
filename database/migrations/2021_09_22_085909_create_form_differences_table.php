<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormDifferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_differences', function (Blueprint $table) {
            $table->id();
            $table->string('home_team', 50);
            $table->string('away_team', 50);
            $table->json('last_five', 50);
            $table->float('home_team_points', 5, 2);
            $table->float('away_team_points', 5, 2);
            $table->float('form_diff', 5, 2);
            $table->index('form_diff');
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
        Schema::dropIfExists('form_differences');
    }
}
