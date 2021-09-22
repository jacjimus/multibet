<?php

namespace App\Console\Commands;

use App\Traits\HasModelService;
use Illuminate\Console\Command;

class NcmsModels extends Command
{
    //traits
    use HasModelService;

    //vars
    protected $signature = 'ncms:models
		{action? : Command action [build <models/migrations> | migrate <reset/fresh/refresh/rollback> | seed].}
		{value? : Command action value.}
		{--seed : Enable database seeder where supported.}
		{--backup : Enable backup where supported.}
	';

    protected $description = 'NCMS Database models processing.';

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

        //output - title
        $this->info('NCMS Models');

        //get action & value
        $action = $this->argument('action');
        $value = $this->argument('value');

        //handle action
        switch ($action) {
            //build
            case 'build':
                switch ($value) {
                    //build models
                    case 'models':
                        $this->buildModels();

                        break;

                    //build migrations
                    case 'migrations':
                        $this->buildMigrations();

                        break;

                    //build (default)
                    default:
                        $this->buildModels();
                        $this->buildMigrations();

                        break;
                }

                break;

            //migrate
            case 'migrate':
                switch ($value) {
                    //reset - roll back all app migrations
                    case 'reset':
                        $this->runMigration('migrate:reset');

                        break;

                    //fresh - re-creates database
                    case 'fresh':
                        $this->runMigration('migrate:fresh', 1);

                        break;

                    //refresh - roll back and re-migrate
                    case 'refresh':
                        $this->runMigration('migrate:refresh', 1);

                        break;

                    //rollback - roll back migrations
                    case 'rollback':
                        $this->runMigration('migrate:rollback');

                        break;

                    //default - migrate
                    default:
                        $this->runMigration('migrate', 1);

                        break;
                }

                break;

            //seed
            case 'seed':
                $this->runMigration('db:seed');

                break;

            //default - list models
            default:
                $this->listModels();

                break;
        }
    }

    //run migration
    private function runMigration(string $cmd, bool $can_seed=false)
    {
        //output
        $this->line('');
        $this->info("Running '$cmd'...");

        //backup models
        if ($this->option('backup')) {
            $this->getModelService()->backupModels();
        }

        //call artisan
        if ($can_seed && $this->option('seed')) {
            $this->call($cmd, ['--seed' => true]);
        } else {
            $this->call($cmd);
        }

        //output
        $this->line('');
    }

    //build migrations
    private function buildMigrations()
    {
        //output
        $this->line('');
        $this->info('Building migrations...');

        //build
        $this->getModelService()->buildMigrations($this->option('backup'));

        //output
        $this->line('');
    }

    //build models
    private function buildModels()
    {
        //output
        $this->line('');
        $this->info('Building models...');

        //build
        $this->getModelService()->buildModels($this->option('backup'));

        //output
        $this->line('');
    }

    //list models
    public function listModels()
    {
        //output
        $this->line('');
        $this->info('List models...');

        //ignore invalid
        if (!x_is_assoc($config = $this->getModelService()->getConfig())) {
            return $this->error('Models config not loaded!');
        }

        //output
        $this->line('');
        $this->line('Available Models:');

        //list available models
        $num = 0;
        foreach ($config as $model_ref => $model_config) {
            $num += 1;
            $class_name = x_array_get('class_name', $model_config, null, 1);
            $table = x_array_get('table', $model_config, null, 1);
            $this->line(sprintf('%s. %s - %s', str_pad($num, 2, '0', STR_PAD_LEFT), $class_name, $table));
        }

        //output
        $this->line('');
    }
}
