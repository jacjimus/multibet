<?php

namespace App\Console\Commands;

use App\Services\Fstats\Footystats;
use Exception;
use Illuminate\Console\Command;

class FsFetch extends Command
{
    //command signature & description
    protected $signature = 'fs:fetch {mode=all} {--update=0} {--date=} {--prev}';

    protected $description = 'Fetch & update football matches stats.';

    //vars
    private $_fs;

    private $_sp;

    private $_modes = ['all', 'fs', 'wdw', 'wdw-corr'];

    //construct
    public function __construct()
    {
        parent::__construct();
    }

    //get footystats instance
    private function getFootystats()
    {
        if (!($fs = $this->_fs)) {
            $this->_fs = $fs = new Footystats();
        }

        return $fs;
    }

    //handle command
    public function handle()
    {
        //setup verbose handler
        x_verbose_start($this);

        //fetch mode
        $modes = $this->_modes;
        $mode = $this->argument('mode');
        if (!in_array($mode, $modes)) {
            throw new Exception(sprintf('Unsupported fetch mode "%s"! (supported = %s).', $mode, x_join($modes, ', ')));
        }

        //fetch options
        $update = (int) $this->option('update');
        $date = trim($this->option('date'));
        $utime = x_utime($date, 1, 'Y-m-d');

        //fetch fs matches
        if (in_array($mode, ['all', 'fs'])) {
            //output
            $this->comment('Fetch Footystats Matches.');

            //fetch data
            $this->getFootystats()->fetchMatches($update, $date);
        }

        //fetch matches wdw
        if (in_array($mode, ['all', 'wdw'])) {
            //output
            $this->comment('Fetch Footystats Win-Draw-Win.');

            //fetch data
            $this->getFootystats()->fetchWdw($update, $date);
        }

        //correlate fs wdw matches
        if (in_array($mode, ['wdw-corr'])) {
            //output
            $this->comment('Correlate Footystats Win-Draw-Win Matches.');

            //fetch data
            $this->getFootystats()->correlateFsWdwMatches();
        }

        //clear updating
        sleep(1); //delay 1 sec
        $this->comment("fs-fetch-$utime - done");
        x_cache_delete("fs-fetch-$utime");

        //call - update previous date
        if ($this->option('prev')) {
            $prev_utime = $utime - (24 * 60 * 60);
            $prev_date = x_udate($prev_utime, 'Y-m-d');
            $this->line('');
            $this->info("> UPDATE PREVIOUS DATE: $prev_date");
            $this->line('');
            $this->call('fs:fetch', [
                'mode' => 'fs',
                '--update' => 1,
                '--date' => $prev_date,
            ]);
        }
    }
}
