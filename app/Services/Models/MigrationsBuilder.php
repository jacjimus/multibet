<?php

namespace App\Services\Models;

use App\Traits\HasConsole;
use Exception;

class MigrationsBuilder extends BuilderConfig
{
    //traits
    use HasConsole;

    //migrations
    private $_migrations_dir;

    private $_migrations = [];

    private $_migration_order = [];

    //artisan migration commands
    private $_artisan_migrations = [
        'queue:table', //create jobs
        'queue:failed-table', //create failed_jobs
    ];

    //backup
    private $_backup_name = 'migrations';

    private $_max_backups = 2;

    //construct
    public function __construct(array $config)
    {
        parent::__construct($config); //construct BuilderConfig
        $this->_migrations_dir = $this->_base_path . '/database/migrations';
    }

    //set migration order
    public function setMigrationOrder(...$items)
    {
        //ignore if no items
        if (!x_is_list($items, 0)) {
            return;
        }

        //get migration order buffer
        $buffer = $this->_migration_order;
        if (!x_is_list($buffer)) {
            $buffer = [];
        }

        //buffer single item
        $count = count($items);
        if ($count == 1 && !in_array($item = $items[0], $buffer)) {
            $buffer[] = $item;
        } else {
            //last item index
            $x = $count - 1;
            $last_item = $items[$x];
            $last_index = array_search($last_item, $buffer);

            //buffer items
            for ($i = 0; $i < $x; $i ++) {
                //get item
                $item = $items[$i];

                //append item if missing
                if (!in_array($item, $buffer)) {
                    $buffer[] = $item;
                }

                //move item to before last index
                if ($last_index !== false) {
                    //get item index
                    $item_index = array_search($item, $buffer);

                    //check if index is after last item index
                    if ($item_index > $last_index) {
                        //remove last item from buffer
                        unset($buffer[$item_index]);

                        //re-insert item at last item index
                        $buffer = x_array_insert(
                            $item,
                            $buffer,
                            $last_index,
                            $insert_after=false
                        );
                    }
                }
            }

            //append last item if missing
            if (!in_array($last_item, $buffer)) {
                $buffer[] = $last_item;
            }
        }

        //update migration order
        $this->_migration_order = $buffer;
    }

    //setup migrations
    public function setupMigrations()
    {
        //output
        x_dump('', '[migrations builder] - setup migrations..');

        //setup config migrations
        $config = $this->getConfig();
        foreach ($config as $model_ref => $model_config) {
            //get model config values
            $table = $this->getModelConfig($model_ref, $model_config, 'table');
            $model_path = $this->getModelConfig($model_ref, $model_config, 'path');
            $model_fields = $this->getModelConfig($model_ref, $model_config, 'fields', 1);

            //foreign fields
            $foreign_reference = x_array_get('foreign_reference', $model_config, []);
            $foreign_models = x_array_get('foreign_models', $model_config, []);

            //buffer uses, create
            $uses = [];
            $create = [];

            //table columns
            $columns = [];
            foreach ($model_fields as $field_name => $field_config) {
                if (!x_has_key($field_config, 'table')) {
                    continue;
                }
                //if (x_has_key($foreign_fields, $field_name)) $foreign[] = $field_name;

                //column create field call methods
                $column = [$field_config['table']];

                //unique
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'unique', true)) {
                    $column[] = '-> unique()';
                }

                //index
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'index', true)) {
                    $column[] = '-> index()';
                }

                //nullable
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'nullable', true)) {
                    $column[] = '-> nullable()';
                }

                //default
                if (x_has_key($field_config, 'default')) {
                    //get default value
                    $default = x_str($field_config['default']);

                    //set column default
                    switch (strtoupper($default)) {
                        //default timestamp
                        case 'CURRENT_TIMESTAMP':
                            //add use Illuminate\Support\Facades\DB
                            $uses = x_list_add('use Illuminate\Support\Facades\DB;', $uses);

                            //set default value (method)
                            $column[] = "-> default(DB::raw('CURRENT_TIMESTAMP'))";

                            break;

                        //default value
                        default:
                            //set default value
                            $column[] = sprintf("-> default('%s')", $default);

                            break;
                    }
                }

                //set column create
                $create[] = x_join($column, ' ');
            }

            //table foreign references (set migration items)
            if (x_is_assoc($foreign_reference)) {
                $create[] = null; //separator
                foreach ($foreign_reference as $key => $value) {
                    //set on delete
                    if ($this->fieldHasAttr($model_ref, $model_config, $key, 'nullable', true)) {
                        $value .= "->onDelete('set null')";
                    } else {
                        $value .= "->onDelete('cascade')";
                    }

                    //create buffer
                    $create[] = $value;
                }
            }

            //add model ref as last migration item
            $migration_items = x_array_unset_values($foreign_models, $model_ref);
            $migration_items[] = $model_ref;

            //set migration order
            $this->setMigrationOrder(...$migration_items);

            //table up method
            $t = "\t";
            $eol = PHP_EOL;
            $table_up = $t . 'public function up(){';
            $table_up .= sprintf($eol . $t . $t . "Schema::create('%s', function (Blueprint \$table){", $table);
            foreach ($create as $item) {
                $table_up .= $item ? $eol . $t . $t . $t . "$item;" : $eol;
            }
            $table_up .= $eol . $t . $t . '});' . $eol . $t . '}';

            //table down method
            $table_down = $t . 'public function down(){';
            $table_down .= sprintf($eol . $t . $t . "Schema::dropIfExists('%s');", $table);
            $table_down .= $eol . $t . '}';

            //setup migrations
            $this->_migrations[$model_ref] = [
                'uses' => $uses,
                'path' => $model_path,
                'table' => $table,
                'up' => $table_up,
                'down' => $table_down
            ];
        }
    }

    //empty migrations folder
    public function emptyMigrations()
    {
        //get migrations folder
        $dir = $this->_migrations_dir;

        //output
        x_dump(...[
            '',
            '[migrations builder] - empty migration folder:',
            '   - ' . x_trim(str_replace($this->_base_path, '', $dir), '/'),
        ]);

        //delete folder contents
        x_dir_delete_contents($dir);
    }

    //backup migrations folder
    public function backupMigrations()
    {
        //output
        x_dump('', '[migrations builder] - backup migrations');

        //backup migrations folder
        app()->make('BackupService')->backup(
            $this->_backup_name,
            $this->_migrations_dir,
            $this->_max_backups,
            $backup_zip=true,
            $zip_keep=false
        );
    }

    //call artisan migrations
    public function artisanMigrations()
    {
        //ignore if no artisan migrations
        if (!x_is_list($arr = $this->_artisan_migrations, 0)) {
            return;
        }

        //run artisan migration commands
        $console = $this->getConsoleService();
        foreach ($arr as $cmd) {
            try {
                //output
                x_dump('', sprintf('[migrations builder] - running "artisan %s"', $cmd));

                //run artisan command
                $result = $console->runArtisan($cmd, 1);

                //output
                x_dump(' - ' . trim($console->output));
            } catch (Exception $e) {
                //output
                x_dump('[migrations builder] - artisan exception: ', x_err($e));
            }
        }
    }

    //create migrations
    public function createMigrations()
    {
        //output
        x_dump('', '[migrations builder] - creating migration files..');

        //get migration order
        $order = $this->_migration_order;

        //get migrations
        $migrations = $this->_migrations;

        //migrations folder
        $dir = $this->_migrations_dir;

        //add missing migration keys to order
        $keys = array_keys($migrations);
        foreach ($keys as $key) {
            $order = x_list_add($key, $order);
        }

        //create migrations
        $time = now();
        foreach ($order as $i => $model_ref) {
            //get migration
            $migration = $migrations[$model_ref];
            $table = $migration['table'];
            $uses = $migration['uses'];
            $model_path = $migration['path'];

            //add 2 seconds
            $time->addSeconds(2);
            $tmp = $time->format('Y_m_d_His');

            //name
            $name = 'create_' . $table . '_table';

            //class
            $class = x_studly($name, 1);

            //path
            $path = $dir . '/' . $tmp . '_' . $name . '.php';

            //migration contents
            $t = "\t"; //tab
            $eol = PHP_EOL; //eol

            //php file buffer
            $lines = [];
            $lines[] = '<?php';

            //uses
            $lines[] = null; //space
            $lines[] = 'use Illuminate\Database\Migrations\Migration;';
            $lines[] = 'use Illuminate\Database\Schema\Blueprint;';
            $lines[] = 'use Illuminate\Support\Facades\Schema;';

            //uses
            if (x_is_list($uses, 0)) {
                $lines = array_merge($lines, $uses);
            }

            $lines[] = null; //space
            $lines[] = "class $class extends Migration";
            $lines[] = '{';

            //comment
            $lines[] = $t . '/**';
            $lines[] = $t . ' * Run the migrations.';
            $lines[] = $t . ' *';
            $lines[] = $t . ' * @return void';
            $lines[] = $t . ' */';

            //migration up
            $lines[] = $migration['up'];
            $lines[] = null; //space

            //comment
            $lines[] = $t . '/**';
            $lines[] = $t . ' * Reverse the migrations.';
            $lines[] = $t . ' *';
            $lines[] = $t . ' * @return void';
            $lines[] = $t . ' */';

            //migration down
            $lines[] = $migration['down'];
            $lines[] = '}';

            //php file content - buffer to string
            $content = x_join($lines, $eol);

            //output
            x_dump('- create: ' . x_trim(str_replace($dir, '', $path), '/'));

            //write content to path
            x_file_put($path, $content);
        }
    }

    //build migration files
    public function build(bool $backup=false)
    {
        //output
        x_dump('', '[migrations builder] - build started..');

        //setup migrations if not setup
        if (empty($this->_migrations)) {
            $this->setupMigrations();
        }

        //backup migrations
        if ($backup) {
            $this->backupMigrations();
        }

        //empty migrations folder
        $this->emptyMigrations();

        //create migrations
        $this->artisanMigrations();
        $this->createMigrations();

        //output
        x_dump('', '[migrations builder] - build done.');
    }
}
