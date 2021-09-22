<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelpersProvider extends ServiceProvider
{
    //helpers folder
    private $_helpers = 'app/Helpers';

    /**
     *	Register Helpers
     *
     *	@return void
     */
    public function register()
    {
        //register helper functions
        $seen = [];
        $file_pattern='/^[\/-0-9_a-z]+\.php$/i';
        $dir = base_path($this->_helpers . '/Functions');

        //import /x-file-methods.php (allows us to use the x_scan_dir method)
        $seen[] = $path = $dir . '/x-file-methods.php';
        require_once $path;

        //scan handler - import path
        $handler = function ($path) use (&$seen) {
            //ignore seen
            if (in_array($path, $seen)) {
                return;
            }
            $seen[] = $path;

            //import path
            require_once $path;
        };

        //scan functions directory
        x_scan_dir(
            $dir,
            $handler,
            $recurse=1,
            $include_files=1,
            $include_dirs=0,
            $file_pattern,
            $break_on=false,
            $scandir_sort=SCANDIR_SORT_ASCENDING
        );
    }
}
