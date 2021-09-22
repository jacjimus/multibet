<?php

namespace App\Services;

use App\Traits\HasDatabase;
use App\Traits\HasZipService;
use Exception;
use Illuminate\Support\Collection;

class BackupService
{
    //traits
    use HasZipService, HasDatabase;

    //vars
    private $_backup_dir;

    private $_backup_dir_table;

    private $_backup_dir_tables;

    //construct
    public function __construct()
    {
        $this->_backup_dir = storage_path('backup');
        $this->_backup_dir_table = $this->_backup_dir . '/table';
        $this->_backup_dir_tables = $this->_backup_dir . '/tables';
    }

    //backup folder keep purge
    public function backupDirMax(string $backup_dir, int $max_backups=0, string $file_pattern=null)
    {
        //ignore missing backup dir or no max backupts
        if (!(x_is_dir($backup_dir) && $max_backups > 0)) {
            return;
        }

        //get backup dir content paths
        $paths = x_scan_dir_get(
            $backup_dir,
            $recurse=false,
            $include_files=true,
            $include_dirs=true,
            $file_pattern,
            $break_on=false,
            $scandir_sort=SCANDIR_SORT_DESCENDING //descending
        );

        //ignore below backup files count max
        if (($count = count($paths)) < $max_backups) {
            return;
        }

        //get paths to delete
        $delete_paths = array_slice($paths, $max_backups - 1);

        //output
        x_dump(...array_merge(
            ['', '- backup dir max delete:'],
            array_map(function ($path) use (&$backup_dir) {
                return '  ' . x_path_no_base($path, $backup_dir);
            }, $delete_paths)
        ));

        //delete paths
        x_delete(...$delete_paths);

        //output
        x_dump('- done.');
    }

    //get backup path
    public function getBackupPath(string $backup_dir, string $file_pattern=null, $backup_time=null)
    {
        //ignore missing backup dir
        if (!x_is_dir($backup_dir)) {
            return;
        }

        //set backup time limit
        $format = 'Y-m-d_His';
        $time = ($backup_time = trim($backup_time)) ? x_utime($backup_time, 0, $format, null) : null;
        $date = $time ? substr(x_udate($time, $format), 0, strlen($backup_time)) : null;

        //scan handler - set backup path
        $backup_path = null;
        $handler = function ($path) use (&$backup_path, &$time, &$date, &$format) {
            //path backup time limit
            if ($time && $date) {
                //check path time
                if (preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6})/', basename($path), $matches)) {
                    //set path time
                    $path_time = x_utime($matches[1], 0, $format);
                    $path_date = $path_time ? x_udate($path_time, $format) : null;

                    //check path time limit - set backup path
                    if ($path_time < $time || strpos($path_date, $date) !== false) {
                        $backup_path = $path;

                        return false;
                    }
                }
            } else {
                //set backup path
                $backup_path = $path;

                return false;
            }
        };

        //scan backup folder
        x_scan_dir(
            $backup_dir,
            $handler,
            $recurse=0,
            $include_files=1,
            $include_dirs=1,
            $file_pattern,
            $break_on=false,
            $scandir_sort=SCANDIR_SORT_DESCENDING
        );

        //result - path
        return $backup_path;
    }

    //get table backup path
    public function getTableBackupPath(string $table, $backup_time=null, &$is_temp=null)
    {
        //is temp default
        $is_temp = null;

        //ignore invalid table name
        if (!($table = trim($table))) {
            return;
        }

        //set vars
        $backup_dir = $this->_backup_dir . '/table';

        //temp paths
        $tmp_path = null;

        //set latest tables backup
        $tables_backup = $this->getBackupPath(
            $this->_backup_dir . '/tables',
            $file_pattern=null,
            $backup_time
        );

        //dir - copy table backup
        if (x_is_dir($tables_backup)) {
            //get path timestamp
            $ts = preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6})/', basename($tables_backup), $matches) ? $matches[1] : null;

            //check backup file path
            if (x_is_file($path = "$tables_backup/$table.data")) {
                //set copy destination
                $dest = "$backup_dir/$table" . ($ts ? "-$ts" : '') . '.data';

                //copy missing - set temp path
                if (!x_is_file($dest)) {
                    if (x_copy($path, $dest)) {
                        $tmp_path = $dest;
                    }
                }
            }
        }

        //zip - extract table backup
        elseif (x_is_file($tables_backup) && x_file_ext($tables_backup) == 'zip') {
            //get path timestamp
            $ts = preg_match('/([0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6})/', basename($tables_backup), $matches) ? $matches[1] : null;

            //set extract destination
            $dest = "$backup_dir/$table" . ($ts ? "-$ts" : '') . '.data';

            //extract missing - set temp path
            if (!x_is_file($dest)) {
                if ($this->getZipService()->extract($tables_backup, $dest, "$table.data")) {
                    $tmp_path = $dest;
                }
            }
        }

        //set latest table backup
        $table_path = $this->getBackupPath(
            $backup_dir,
            $file_pattern=sprintf('/%s/', preg_quote($table)),
            $backup_time
        );

        //check temp path
        if ($tmp_path) {
            //set is temp
            if ($table_path && $table_path == $tmp_path) {
                $is_temp = true;
            }

            //delete temp
            else {
                $dir = dirname($tmp_path);
                x_delete($tmp_path);
                if (x_dir_empty($backup_dir)) {
                    x_dir_delete($backup_dir);
                }
            }
        }

        //result - backup path
        return $table_path;
    }

    //restore table backup
    public function restoreTableBackup(string $path, string $table, string $backup_table=null)
    {
        //check path - throws exception
        if (!x_is_file($path, 1)) {
            return;
        }

        //check table - throws exception
        if (!$this->getDB()->tableExists($table, 1)) {
            return;
        }

        //output
        x_dump(
            '',
            "- backup restore table '$table'" . ($table != $backup_table ? " from '$backup_table'" : '') . '...',
            '  ' . x_path_no_base($path, $this->_backup_dir)
        );

        //disable foreign keys
        $this->getDB()->disableForeignKeys();

        //restore backup items
        $num = 0;
        $count = 0;
        $count_lines = 0;
        $columns = $this->getDB()->getTableColumns($table);

        //handler - file read line
        $handler = function ($line) use (&$path, &$table, &$backup_table, &$num, &$count, &$count_lines, &$columns) {
            //line count
            $num += 1;

            //output - progress every 10 lines
            $n = $num - 1;
            if (($n % 10) == 0) {
                $prog = round(($n / $count_lines) * 100, 2);
                x_dump(" - $table Restored ($n/$count_lines) - $prog%...");
            }

            //parse line text - set item assoc
            $json = x_is_json($line) ? $line : x_decrypt($line);
            if (!(x_is_json($json) && x_is_assoc($item = json_decode($json, 1)))) {
                //output error - ignore
                x_dump(sprintf('- Error: Failed to parse backup data! [%d](%s - %s)', $num, $path, $table));

                return;
            }

            //setup fields
            $tmp = [];
            foreach ($item as $key => $value) {
                //skip null value
                if (is_null($value)) {
                    continue;
                }

                //skip unknown field
                if (!in_array($key, $columns)) {
                    x_dump("- Skip: Unknown table column '$key'.");

                    continue;
                }

                //set field value
                $tmp[$key] = $value;
            }
            $item = $tmp;

            //skip invalid
            if (!x_is_assoc($item)) {
                x_dump("- Skip: Invalid item data. ($num)");

                return;
            }

            //attempt - table insert item
            try {
                //insert item
                $id = $this->getDB()->table($table)->insert($item);

                //count saved
                $count += 1;
            }

            //catch error
            catch (Exception $e) {
                //output - error
                x_dump(sprintf('- Error: %s (%s)', $e->getMessage(), $num));

                //stop processing
                return false;
            }
        };

        //file read line - set count lines
        x_file_readline($path, $handler, $count_lines);

        //enable foreign keys
        $this->getDB()->enableForeignKeys();

        //output
        x_dump("- done ($count items restored).");
    }

    //restore table
    public function restoreTable(string $backup_table, string $restore_table=null, $backup_time=null)
    {
        //check backup table
        if (!strlen($backup_table = trim($backup_table))) {
            throw new Exception('Restore backup table is undefined!');
        }

        //set restore table
        $restore_table = x_is_string($restore_table, 1) ? $restore_table : $backup_table;

        //check restore table - throws exception
        if (!$this->getDB()->tableExists($restore_table = trim($restore_table), 1)) {
            return;
        }

        //set backup time limit
        $format = 'Y-m-d_His';
        $time = ($backup_time = trim($backup_time)) ? x_utime($backup_time, 0, $format, null) : null;
        $date = $time ? substr(x_udate($time, $format), 0, strlen($backup_time)) : null;
        $time_debug = !$time ? '' : " (backup time: '$backup_time'" . ($date != $backup_time ? " - '$date'" : '') . ')';

        //output
        x_dump("- restore table: $backup_table" . ($backup_table != $restore_table ? " - $restore_table" : '') . $time_debug);

        //set backup path
        $path = $this->getTableBackupPath($backup_table, $backup_time, $is_temp);

        //check backup path
        if ($path && x_is_file($path)) {
            //restore backup
            $this->restoreTableBackup($path, $restore_table, $backup_table);

            //delete temp
            if ($is_temp) {
                $tmp = dirname($path);
                x_delete($path);
                if (x_dir_empty($tmp)) {
                    x_dir_delete($tmp);
                }
            }
        }

        //output - not found
        else {
            if (!$path) {
                x_dump('- no existing backup.' . $time_debug);
            } else {
                x_dump("- backup path missing: $path");
            }
        }
    }

    //restore tables
    public function restoreTables(array $backup_tables=null, array $restore_tables=null, $backup_time=null)
    {
        //set vars
        $backup_tables = x_is_list($backup_tables, 0) ? $backup_tables : $this->getDB()->getTables();
        $restore_tables = x_to_list($restore_tables);

        //output
        $count = count($backup_tables);
        if ($count > 1) {
            x_dump("- restore $count tables:");
        }

        //restore tables
        foreach ($backup_tables as $i => $backup_table) {
            $restore_table = $i < count($restore_tables) ? $restore_tables[$i] : null;
            $this->restoreTable(
                $backup_table,
                $restore_table,
                $backup_time
            );
        }
    }

    //backup db table
    public function backupTable(string $table, bool $encrypt=false, int $max_backups=0)
    {
        //check table - throws exception
        if (!$this->getDB()->tableExists($table = trim($table), 1)) {
            return;
        }

        //backup folder
        $backup_dir = $this->_backup_dir_table;

        //max backups limit
        $this->backupDirMax(
            $backup_dir,
            $max_backups,
            $file_pattern=sprintf('/%s/', preg_quote($table))
        );

        //backup timestamp
        $now = now()->format('Y-m-d_His');

        //backup destination
        $dest = "$backup_dir/$table-$now.data";

        //output
        x_dump(
            '',
            "- backup table $table...",
            '  ' . x_path_no_base($dest)
        );

        //table items handler
        $count_items = 0;
        $items_callback=function ($items) use (&$count_items, &$dest, &$encrypt) {
            //ignore invalid
            if (!($items instanceof Collection && count($items))) {
                return;
            }

            //save items
            foreach ($items as $i => $item) {
                //set vars
                $count_items += 1;
                $json = json_encode((array) $item);
                $content = trim($encrypt ? x_encrypt($json) : $json) . PHP_EOL;

                //content - append to file
                if (!x_file_put($dest, $content, $flags=FILE_APPEND)) {
                    throw new Exception(sprintf('Failed to append item data! "%s" (%s)', $dest, strlen($content)));
                }
            }
        };

        //read table - backup items
        $this->getDB()->readTable(
            $table,
            $items_callback,
            $chunk_size=100,
            $query_callback=null
        );

        //output
        x_dump("- done ($count_items items).");
    }

    //backup db tables
    public function backupTables(array $tables=null, bool $encrypt=false, int $max_backups=0, bool $backup_zip=false, bool $zip_keep=false)
    {
        //set tables (default = all tables)
        $tables = x_is_list($tables, 0) ? $tables : $this->getDB()->getTables();

        //ignore empty tables list
        if (!($count = count($tables))) {
            x_dump(' - no backup tables!');

            return;
        }

        //backup one table
        if ($count == 1) {
            return $this->backupTable(
                $tables[0],
                $encrypt,
                $max_backups
            );
        }

        //backup folder
        $backup_dir = $this->_backup_dir_tables;

        //max backups limit
        $this->backupDirMax($backup_dir, $max_backups);

        //backup timestamp
        $now = now()->format('Y-m-d_His');

        //backup destination
        $dest_dir = $backup_dir . '/' . $now;
        $dest = null;

        //table items handler
        $count_items = 0;
        $items_callback=function ($items) use (&$count_items, &$dest, &$encrypt) {
            //ignore invalid
            if (!($items instanceof Collection && count($items))) {
                return;
            }

            //save items
            foreach ($items as $i => $item) {
                //set vars
                $count_items += 1;
                $json = json_encode((array) $item);
                $content = trim($encrypt ? x_encrypt($json) : $json) . PHP_EOL;

                //content - append to file
                if (!x_file_put($dest, $content, $flags=FILE_APPEND)) {
                    throw new Exception(sprintf('Failed to append item data! "%s" (%s)', $dest, strlen($content)));
                }
            }
        };

        //output
        x_dump('', "- backup ($count) tables:");

        //read tables
        foreach ($tables as $i => $table) {
            //set vars
            $count_items = 0;
            $num = ($i + 1) . '/' . $count;
            $dest = $dest_dir . '/' . $table . '.data';

            //output
            x_dump(
                '',
                "- backup table ($num) $table...",
                '  ' . x_path_no_base($dest)
            );

            //read table - backup items
            $this->getDB()->readTable(
                $table,
                $items_callback,
                $chunk_size=100,
                $query_callback=null
            );

            //output
            x_dump("- done ($count_items items).");
        }

        //output
        x_dump('- done');

        //backup zip
        if ($backup_zip) {
            $this->zip($dest_dir, null, $zip_keep);
        }
    }

    //backup path (file/folder)
    public function backup(string $backup_name, string $path, int $max_backups=0, bool $backup_zip=false, bool $zip_keep=false)
    {
        //check path - throws exception
        if (!x_file_exists($path, 1, $path)) {
            return;
        }

        //check backup name (must be a valid path string)
        $backup_name = x_trim($backup_name, '/');
        if (!x_is_path($backup_name)) {
            throw new Exception(sprintf('Invalid backup name! (%s)', $backup_name));
        }

        //backup folder
        $backup_dir = $this->_backup_dir . '/' . $backup_name;

        //max backups limit
        $this->backupDirMax($backup_dir, $max_backups);

        //backup timestamp
        $now = now()->format('Y-m-d_His');

        //backup destination
        $dest_dir = $backup_dir . '/' . $now;
        $dest = $dest_dir . '/' . basename($path);

        //output
        x_dump(...[
            '',
            '- backup copy:',
            '  ' . x_path_no_base($path),
            '  ' . x_path_no_base($dest)
        ]);

        //backup copy
        if (!($copy = x_copy($path, $dest))) {
            throw new Exception(sprintf('Error creating backup copy! (%s - %s)', $path, $dest));
        }

        //output
        x_dump('- done.');

        //backup zip
        if ($backup_zip) {
            $this->zip($dest_dir, null, $zip_keep);
        }
    }

    //zip path contents
    public function zip(string $path, string $zip_file=null, bool $zip_keep=true)
    {
        //set zip file path from $path if not set
        if (is_null($zip_file)) {
            $zip_file = $path . '.zip';
        }

        //validate zip file extension
        if (($ext = x_file_ext($zip_file)) != 'zip') {
            throw new Exception(sprintf('Invalid zip file path extension "%s"! (%s)', $ext, $zip_file));
        }

        //remove zip file if exists
        if (x_is_file($zip_file)) {
            x_file_delete($zip_file);
        }

        //output
        x_dump(
            '',
            '- backup zip:',
            '  ' . x_path_no_base($path),
            '  ' . x_path_no_base($zip_file)
        );

        //zip path contents
        if (!$this->getZipService()->zip($path, $zip_file)) {
            throw new Exception(sprintf('Failed to zip contents! (%s - %s)', $path, $zip_file));
        }

        //remove zipped $path
        if (!$zip_keep) {
            //output
            x_dump(
                '',
                '- backup zip no keep:',
                '  ' . x_path_no_base($path)
            );

            //delete path
            x_path_delete($path);
        }

        //output
        x_dump('- done.');
    }
}
