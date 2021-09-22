<?php

namespace App\Services\Models;

class ConfigLoader extends ModelsConfig
{
    //config
    private $_config;

    //constructor
    public function __construct()
    {
        $this->loadConfig();
    }

    //get config
    public function getConfig()
    {
        return $this->_config;
    }

    //load config
    public function loadConfig()
    {
        //get config dir
        $config_dir = $this->getConfigDir();

        //load config file paths
        $paths = x_scan_dir_get(
            $config_dir,
            $recurse=1,
            $include_files=1,
            $include_dirs=0,
            self::MODEL_FILE_PATTERN,
            $break_on=false,
            $scandir_sort=SCANDIR_SORT_ASCENDING
        );

        //config buffer
        $buffer = [];

        //load model config
        foreach ($paths as $path) {
            //load config
            $config = x_file_get_array($path);

            //ignore invalid config data
            if (!x_is_assoc($config)) {
                continue;
            }

            //ignore disabled model
            if (x_has_key($config, 'disabled') && !!$config['disabled']) {
                continue;
            }

            //model name (snake case)
            $name = $this->toModelName(str_replace('.php', '', basename($path)));

            //replace model name with config name if they differ
            if (x_has_key($config, 'name') && ($tmp = $this->toModelName($config['name'])) != $name) {
                $name = $tmp;
            }

            //get model ref
            $ref = $this->getModelPathRef($path);

            //get model table from model ref
            $table = $this->getModelRefTable($ref, x_has_key($config, 'pivot') && !!$config['pivot']);

            //get model namespace from model ref (sets model class name)
            $namespace = $this->getModelRefNamespace($ref, $class);

            //get namespace path
            $class_path = $this->getModelClassPath($ref);

            //new config values
            $new_config = [
                'path' => $path,
                'name' => $name,
                'ref' => $ref,
                'table' => $table,
                'class' => $class,
                'namespace' => $namespace,
                'class_path' => $class_path,
                'class_name' => $namespace . '\\' . $class,
            ];

            //unset new config keys from old config
            $config = x_array_unset_keys($config, ...array_keys($new_config));

            //merge (prepend) new config with old
            $config = x_merge($new_config, $config);

            //buffer config (assoc by model ref)
            $buffer[$ref] = $config;
        }

        //result - config buffer
        //dd($buffer);
        return $this->_config = $buffer;
    }
}
