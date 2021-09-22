<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->rename();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->rename(1);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function rename($reverse=0)
    {
        $items = [
            ['fstats-fs_matches'],
            ['fstats-sp_correlations'],
            ['fstats-fs_wdws', ['fs_match_id' => 'fstats_fs_matches']],
            ['fstats-sp_matches', ['fs_match_id' => 'fstats_fs_matches']],
        ];
        $a = $reverse ? '_' : '-';
        $b = $reverse ? '-' : '_';
        foreach ($items as $item) {
            $old_table = str_replace("fstats$b", "fstats$a", $item[0]);
            $new_table = str_replace("fstats$a", "fstats$b", $old_table);
            $foreign = null;
            if (isset($item[1]) && x_is_assoc($foreign = $item[1])) {
                Schema::table($old_table, function (Blueprint $table) use (&$foreign, &$a, &$b) {
                    $table->dropForeign(array_keys($foreign));
                });
            }
            Schema::rename($old_table, $new_table);
            if ($foreign) {
                Schema::table($new_table, function (Blueprint $table) use (&$foreign, &$a, &$b) {
                    foreach ($foreign as $key => $val) {
                        $val = str_replace("fstats$a", "fstats$b", $val);
                        $table->foreign($key)->references('id')->on($val)->onDelete('set null');
                    }
                });
            }
        }
    }
}
