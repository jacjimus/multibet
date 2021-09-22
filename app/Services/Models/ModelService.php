<?php

namespace App\Services\Models;

use App\Traits\HasBackup;
use Exception;

class ModelService
{
    //traits
    use HasBackup;

    //config
    private $_config;

    //get config
    public function getConfig()
    {
        if (empty($this->_config)) {
            $this->setConfig();
        }

        return $this->_config;
    }

    //setup config
    public function setConfig()
    {
        //output
        x_dump('', '[model service] - load config.');

        //load config
        $loader = new ConfigLoader();
        $config = $loader->getConfig();

        //output
        x_dump('[model service] - setup config.');

        //setup config
        $setup = new ConfigSetup($config);
        $setup->setupConfig();

        //update config
        $this->_config = $setup->getConfig();
    }

    //backup model
    public function backupModel(string $model_ref)
    {
        //output
        x_dump('', sprintf('[model service] - backup model (%s).', $model_ref));

        //get model config & table name
        if (!(
            x_has_key($config = $this->getConfig(), $model_ref)
            && x_has_key($model_config = $config[$model_ref], 'table')
            && strlen($table = x_tstr($model_config['table']))
        )) {
            throw new Exception(sprintf('Failed to get model table! (%s)', $model_ref));
        }

        //backup table
        $this->getBackupService()->backupTable(
            $table,
            $max_backups=2,
            $backup_zip=true,
            $zip_keep=false,
            $chunk_size=100,
            $order_by_column='id',
            $order_by='asc'
        );

        //output
        x_dump(' - done.');
    }

    //backup models
    public function backupModels()
    {
        //output
        x_dump('', '[model service] - backup models.');

        //check config
        if (!x_is_assoc($config = $this->getConfig())) {
            throw new Exception('Models config not loaded!');
        }

        //backup models
        foreach ($config as $model_ref => $model_config) {
            $this->backupModel($model_ref);
        }

        //output
        x_dump('', '[model service] - backup models done.', '');
    }

    //build migration
    public function buildMigrations(bool $backup=false)
    {
        //output
        x_dump('', '[model service] - build migrations.');

        //builder service build
        $builder = new MigrationsBuilder($this->getConfig());
        $builder->build($backup);

        //output
        x_dump('', '[model service] - build migrations done.', '');
    }

    //build models
    public function buildModels(bool $backup=false)
    {
        //output
        x_dump('', '[model service] - build models.');

        //builder service build
        $builder = new ModelsBuilder($this->getConfig());
        $builder->build($backup);

        //output
        x_dump('', '[model service] - build models done.');
    }
}
