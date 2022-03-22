<?php

namespace App\Console\Commands;

use App\Services\ApiFootball\FixtureService;
use App\Services\ApiFootball\LeagueService;
use App\Services\ApiFootball\OddsService;
use Illuminate\Console\Command;

class FsFetch extends Command
{
    //command signature & description
    protected $signature = 'fs:fetch {mode=l} {--date=}';

    protected $description = 'Fetch & update football matches stats.';

    //vars
    private $_fs;

    private $_sp;

    private $_modes = ['l', 'f', 'o', 'm'];

    //handle command
    public function handle()
    {
        //setup verbose handler
        x_verbose_start($this);
        $modes = $this->_modes;
        $mode = $this->argument('mode');
        if (!in_array($mode, $modes)) {
            throw new \Exception(sprintf('Unsupported fetch mode "%s"! (supported = %s).', $mode, x_join($modes, ', ')));
        }
        $date = $this->option('date');
        if ($mode == 'f' && !isset($date)) {
            throw new \Exception('Fixture mode must be called with a date option');
        }
//        $this->_fs = match ($mode) {
//            'l' => new LeagueService(),
//              'f' => new FixtureService($date),
//            'o' => new OddsService(),
//        };

        // $date = trim($this->option('date'));
        //$utime = x_utime($date, 1, 'Y-m-d');
        $this->_fs->data();
    }
}
