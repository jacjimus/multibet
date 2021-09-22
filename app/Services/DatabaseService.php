<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseService
{
    //vars
    public $db = DB::class;

    public $schema = Schema::class;

    private $_chunk_size = 100;

    //construct
    public function __construct()
    {
        //..
    }

    //table exists
    public function tableExists(string $table, bool $throwable=false, &$error=null)
    {
        $error = null;

        try {
            if (!strlen($table = trim($table))) {
                throw new Exception('Invalid table name!');
            }
            if (!Schema::hasTable($table)) {
                throw new Exception("Table \"$table\" does not exist!");
            }

            return true;
        } catch (Exception $e) {
            $error = $e->getMessage();
            if ($throwable) {
                throw new Exception($error);
            }

            return false;
        }
    }

    //table column exists
    public function columnExists(string $table, string $column, bool $throwable=false, &$error=null)
    {
        $error = null;

        try {
            if (!$this->tableExists($table, 1)) {
                return false;
            }
            if (!strlen($column = trim($column))) {
                throw new Exception('Invalid column name!');
            }
            if (!Schema::hasColumn($table = trim($table), $column)) {
                throw new Exception("Table column \"$column\" does not exist in \"$table\"!");
            }

            return true;
        } catch (Exception $e) {
            $error = $e->getMessage();
            if ($throwable) {
                throw new Exception($error);
            }

            return false;
        }
    }

    //db table
    public function table(string $table)
    {
        return DB::table($table);
    }

    //db select
    public function select(string $select)
    {
        return DB::select($select);
    }

    //disable foreign keys
    public function disableForeignKeys()
    {
        return Schema::disableForeignKeyConstraints();
    }

    //enable foreign keys
    public function enableForeignKeys()
    {
        return Schema::enableForeignKeyConstraints();
    }

    //get db tables
    public function getTables(bool $assoc_count=false)
    {
        //tables buffer
        $tables = [];

        //get db tables
        if (count($items = $this->select('SHOW TABLES'))) {
            foreach ($items as $item) {
                foreach ($item as $table) {
                    //assoc count
                    if ($assoc_count) {
                        $tables[$table] = $this->table($table)->count();
                    }

                    //add table to list
                    else {
                        $tables[] = $table;
                    }

                    break;
                }
            }
        }

        //result - tables
        return $tables;
    }

    //get db table columns
    public function getTableColumns(string $table)
    {
        //check table exists - throws exception
        if (!$this->tableExists($table = trim($table), 1)) {
            return;
        }

        //result - table columns array list
        return is_array($columns = Schema::getColumnListing($table)) ? array_values($columns) : [];
    }

    //get db table columns
    public function getColumnType(string $table, string $column)
    {
        //check column exists - throws exception
        if (!$this->columnExists($table = trim($table), $column = trim($column), 1)) {
            return;
        }

        //result - column type
        return DB::getSchemaBuilder()->getColumnType($table, $column);

        return Schema::getColumnType($table, $column);
    }

    //read table
    public function readTable(string $table, $items_callback, int $chunk_size=100, $query_callback=null)
    {
        //check table exists - throws exception
        if (!$this->tableExists($table = trim($table), 1)) {
            return;
        }

        //check items callback closure
        if (!is_callable($items_callback)) {
            throw new Exception('Argument $items_callback is not callable!');
        }

        //table query
        $query = $this->table($table);

        //set order by id asc
        if ($this->columnExists($table, 'id')) {
            $query->orderBy('id', 'asc');
        }

        //call query callback
        if (is_callable($query_callback)) {
            $query_callback($query);
        }

        //query limit
        $limit = is_integer($chunk_size) && $chunk_size > 0 ? $chunk_size : $this->_chunk_size;

        //read table chunk by chunk
        $offset = 0;
        while (true) {
            //query result
            $items = $query->offset($offset)->limit($limit)->get();

            //break if empty
            if (!count($items)) {
                break;
            }

            //call items callback - break if result is false
            if ($items_callback($items) === false) {
                break;
            }

            //update offset
            $offset += $limit;
        }
    }

    //truncate table
    public function truncateTable(string $table)
    {
        //ignore missing table
        if (!$this->tableExists($table = trim($table), 0, $error)) {
            x_dump($error);

            return;
        }

        //table query - count rows
        $query = $this->table($table);
        $count = $query->count();

        //output
        x_dump('', "- truncate table $table ($count)...");

        //disable foreign keys
        $this->disableForeignKeys();

        //truncate table
        $query->truncate();

        //enable foreign keys
        $this->enableForeignKeys();

        //output
        x_dump('- done');
    }

    //truncate tables
    public function truncateTables(array $tables=null)
    {
        //set vars
        $tables = x_is_list($tables, 0) ? $tables : $this->getTables();

        //output
        $count = count($tables);
        if ($count > 1) {
            x_dump("- truncate $count tables:");
        }

        //truncate
        foreach ($tables as $table) {
            $this->truncateTable($table);
        }
    }

    //get migrations
    public function getMigrations()
    {
        $migrations = [];
        if (count($items = $this->table('migrations')->get())) {
            foreach ($items as $item) {
                $item = (array) $item;
                $id = (int) $item['id'];
                $batch = (int) $item['batch'];
                $name = $migration = $item['migration'];
                $time = null;
                if (preg_match('/([0-9]{4})_([0-9]{2})_([0-9]{2})_([0-9]{6})_(.*)/', $name, $matches)) {
                    $time = x_udate(sprintf('%s-%s-%s %s', $matches[1], $matches[2], $matches[3], x_join(str_split($matches[4], 2), ':')), '');
                    $name = trim($matches[5]);
                }
                $migrations[] = [
                    'id' => $id,
                    'migration' => $migration,
                    'name' => $name,
                    'time' => $time,
                    'batch' => $batch,
                ];
            }
        }

        return $migrations;
    }
}

/*
 * notes (you can delete this)
 *
 * $query->toSql() - returns sql
*/
