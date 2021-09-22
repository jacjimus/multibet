<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NcmsPurge extends Command
{
    //attrs
    protected $signature = 'ncms:purge {item?} {value?} {--force}';

    protected $description = 'NCMS purging tool. [items = backup (value), logs (value), config, route, view, cache, clear, storage]';

    //vars
    private $_clear_commands = [
        'config' => 'config:clear',
        'route' => 'route:clear',
        'view' => 'view:clear',
        'cache' => 'cache:clear',
        'clear' => 'clear',
    ];

    //construct
    public function __construct()
    {
        parent::__construct();
    }

    //handle
    public function handle()
    {
        //setup verbose handler
        x_verbose_start($this);

        //set item
        $item = $this->getItem();

        //backup
        if ($item == 'backup') {
            $this->purgeBackup();
        }

        //logs
        elseif ($item == 'logs') {
            $this->purgeLogs();
        }

        //storage
        elseif ($item == 'storage') {
            $this->purgeStorage();
        }

        //clear command
        elseif (x_has_key($commands = $this->_clear_commands, $item)) {
            $this->purgeCall($commands[$item]);
        }

        //default - all
        else {
            $this->purgeAll();
        }

        //output
        $this->info('done.');
        $this->line('');
    }

    //arguments - item
    public function getItem()
    {
        return trim($this->argument('item'));
    }

    //arguments - value
    public function getValue()
    {
        return trim($this->argument('value'));
    }

    //purge all
    public function purgeAll()
    {
        if ($this->option('force')) {
            $this->purgeBackup();
            $this->purgeStorage();
        }
        $this->purgeLogs();
        $this->purgeClear();
    }

    //purge clear commands
    public function purgeClear()
    {
        foreach ($this->_clear_commands as $purge => $cmd) {
            $this->purgeCall($cmd);
        }
    }

    //purge call
    public function purgeCall($cmd)
    {
        $this->line("artisan $cmd");
        $this->call($cmd);
    }

    //purge backups
    public function purgeBackup()
    {
        //set folder
        $dir = $root = storage_path('backup');

        //specify backup name
        if ($name = $this->getValue()) {
            $dir = "$dir/$name";
        }

        //ignore missing/invalid
        if (!x_is_dir($dir)) {
            return;
        }

        //purge
        $this->purgePath($dir, $root);
    }

    //purge logs
    public function purgeLogs()
    {
        //set folder
        $dir = storage_path('logs');

        //ignore missing/invalid
        if (!x_is_dir($dir)) {
            return;
        }

        //set scan file pattern
        $file_pattern='/.*\.log$/';
        if ($name = $this->getValue()) {
            $file_pattern=sprintf('/%s.*\.log$/', preg_quote($name));
        }

        //scan handler - purge path
        $handler = function ($path) use (&$dir) {
            $this->purgePath($path, $dir);
        };

        //scan directory
        x_scan_dir(
            $dir,
            $handler,
            $recurse=1,
            $include_files=1,
            $include_dirs=0,
            $file_pattern,
            $break_on=false,
            $scandir_sort=SCANDIR_SORT_ASCENDING //SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE
        );
    }

    //purge storage
    public function purgeStorage()
    {
        //folders
        $dirs = [
            'app/request-service',
            'app/public',
            'framework/sessions',
            'logs',
        ];

        //scan handler - purge path
        $root = null;
        $handler = function ($path) use (&$root) {
            $this->purgePath($path, $root);
        };

        //scan purge folders
        $file_pattern = '/^((?!(\.gitignore|uploads)).)*$/';
        foreach ($dirs as $dir) {
            if (!x_is_dir($dir = storage_path($dir))) {
                continue;
            }
            $root = dirname($dir);
            x_scan_dir(
                $dir,
                $handler,
                $recurse=0,
                $include_files=1,
                $include_dirs=1,
                $file_pattern,
                $break_on=false,
                $scandir_sort=SCANDIR_SORT_ASCENDING,
                $file_pattern_dir=true
            );
        }
    }

    //purge path
    public function purgePath(string $path, string $root)
    {
        //ignore missing
        if (!file_exists($path)) {
            return;
        }

        //output
        $this->line("- purge path: $path");

        //delete path
        x_path_delete($path);

        //delete empty parents
        $tmp = $path;
        $x = substr_count($path, '/');
        while (($x --) > 0) {
            //get parent
            $tmp = dirname($tmp);

            //breat at root
            if (x_trim($root, '/') == x_trim($tmp, '/')) {
                break;
            }

            //delete empty path
            if (x_dir_empty($tmp)) {
                x_dir_delete($tmp);

                //output
                $this->line("- delete empty ($tmp).");
            }
        }

        //output
        $this->info('- path deleted.');
    }
}
