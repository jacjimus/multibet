<?php

namespace App\Console\Commands;

use App\Traits\HasBackup;
use App\Traits\HasDatabase;
use Illuminate\Console\Command;

class NcmsTables extends Command
{
    //traits
    use HasDatabase, HasBackup;

    //vars
    protected $signature = 'ncms:tables
		{action=list : Command action. [backup|restore|truncate|list]}
		{tables? : Action table names (csv).}
		{--max=0 : Set maximum backup files count (0 = no limit).}
		{--encrypt : Enable backup data encryption.}
		{--restore= : Restore into table names csv.}
		{--time= : Set restore backup time (x) where backup timestamp <= x.}
	';

    protected $description = 'NCMS Database tables processing.';

    //construct
    public function __construct()
    {
        parent::__construct();
    }

    //handler
    public function handle()
    {
        //setup verbose
        x_verbose_start($this);

        //output
        $this->info('NCMS Tables');

        //get action - ignore undefined
        if (!($action = trim($this->argument('action')))) {
            return;
        }

        //get tables - csv to array list
        $tables = x_to_list($this->argument('tables'), 0, 1, ',');

        //handle action
        switch ($action) {
            //backup tables
            case 'backup':
                $this->doBackup($tables);

                break;

            //restore tables
            case 'restore':
                $this->doRestore($tables);

                break;

            //truncate tables
            case 'truncate':
                $this->doTruncate($tables);

                break;

            //list tables
            case 'list':
                $this->doListTables($tables);

                break;

            //list columns
            case 'col':
            case 'cols':
                $this->doListColumns($tables);

                break;

            //default - unsupported
            default:
                $this->error("Unsupported command action '$action'!");

                break;
        }
    }

    //do truncate
    public function doTruncate(array $tables=null)
    {
        //output
        $this->line('');
        $this->comment('Truncate...');

        //truncate
        $this->getDB()->truncateTables($tables);

        //output
        $this->line('');
    }

    //do backup
    public function doBackup(array $tables=null)
    {
        //output
        $this->line('');
        $this->comment('Backup...');

        //backup
        $this->getBackupService()->backupTables(
            $tables,
            $this->option('encrypt'),
            x_int($this->option('max'), 1),
            $backup_zip=1,
            $zip_keep=0
        );

        //output
        $this->line('');
    }

    //restore tables
    public function doRestore(array $tables=null)
    {
        //output
        $this->line('');
        $this->comment('Restore...');

        //restore
        $this->getBackupService()->restoreTables(
            $tables,
            x_to_list($this->option('restore'), 0, 1, ','),
            $this->option('time')
        );

        //output
        $this->line('');
    }

    //list tables
    public function doListTables(array $tables=null)
    {
        //output
        $this->line('');
        $this->comment('List tables...');

        //set tables
        $db = $this->getDB();
        $tables = x_is_list($tables, 0) ? $tables : $db->getTables();
        if (!x_is_list($tables, 0)) {
            return $this->error('No tables!');
        }

        //list tables
        $num = 0;
        foreach ($tables as $table) {
            $num += 1;
            $str = "$num. $table";
            if ($db->tableExists($table)) {
                $count = $db->table($table)->count();
                $str .= " ($count)";
                $this->line($str);
            } else {
                $str .= ' (missing)';
                $this->error($str);
            }
        }

        //output
        $this->line('');
    }

    //list table columns
    public function doListColumns(array $tables=null)
    {
        //output
        $this->line('');
        $this->comment('List tables...');

        //set tables
        $db = $this->getDB();
        $tables = x_is_list($tables, 0) ? $tables : $db->getTables();
        if (!x_is_list($tables, 0)) {
            return $this->error('No tables!');
        }

        //list tables
        $num = 0;
        foreach ($tables as $table) {
            $num += 1;
            $str = "$num. $table";
            if ($db->tableExists($table)) {
                //output table
                $count = $db->table($table)->count();
                $str .= " ($count)";
                $this->line($str);

                //list table columns
                if (x_is_list($columns = $db->getTableColumns($table), 0)) {
                    foreach ($columns as $i => $column) {
                        $type = $db->getColumnType($table, $column);
                        $this->line(sprintf(' - (%d) %s - %s', $i + 1, $column, $type));
                    }
                } else {
                    $this->line(' - no columns.');
                }
            } else {
                $str .= ' (missing)';
                $this->error($str);
            }

            //output
            $this->line('');
        }
    }
}
