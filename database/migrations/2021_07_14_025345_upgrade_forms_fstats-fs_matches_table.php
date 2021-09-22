<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeFormsFstatsFsMatchesTable extends Migration
{
    /**
     * @var string Table name.
     */
    private $table_name = 'fstats_fs_matches';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            //league_url
            $table->string('league_url')->nullable()->change();

            //home_form
            $table->float('home_form')->nullable()->change();
            $table->float('home_form_home_away')->nullable()->after('home_form');
            $table->renameColumn('home_form', 'home_form_last5');

            //away_form
            $table->float('away_form')->nullable()->change();
            $table->float('away_form_home_away')->nullable()->after('away_form');
            $table->renameColumn('away_form', 'away_form_last5');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->table_name, function (Blueprint $table) {
            $table->renameColumn('home_form_last5', 'home_form');
            $table->renameColumn('away_form_last5', 'away_form');
            $table->dropColumn(['home_form_home_away', 'away_form_home_away']);
        });
    }
}
