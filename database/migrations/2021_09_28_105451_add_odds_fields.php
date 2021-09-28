<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOddsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fstats_fs_matches', function (Blueprint $table) {
            $table->float('home_odds', 4, 2)->nullable()->after('away_form_last5');
            $table->float('away_odds', 4, 2)->nullable()->after('home_odds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fstats_fs_matches', function (Blueprint $table) {
            $table->dropColumn('home_odds');
            $table->dropColumn('away_odds');
        });
    }
}
