<?php

namespace App\Services\Models;

use Exception;

class ConfigSetup extends ModelsConfig
{
    //config
    private $_config;

    //construct
    public function __construct(array $config)
    {
        if (!x_is_assoc($config)) {
            throw new Exception('Invalid setup $config!');
        }
        $this->_config = $config;
    }

    //get config
    public function getConfig()
    {
        return $this->_config;
    }

    //setup config
    public function setupConfig()
    {
        //validate config
        if (!x_is_assoc($config = $this->_config)) {
            throw new Exception('Invalid setup $config!');
        }

        //fields setup service
        $fields_setup = new FieldConfigSetup($config);

        //setup config recursively
        //$config = x_clone($config);
        foreach ($config as $model_ref => $model_config) {
            //get model fields
            if (!(x_has_key($model_config, 'fields') && x_is_assoc($model_fields = $model_config['fields']))) {
                throw new Exception(sprintf('Model ref has invalid fields config! (%s)', $model_ref));
            }

            //setup model fields
            foreach ($model_fields as $field_name => $field_config) {
                $fields_setup->setupFieldConfig(
                    $model_ref,
                    $field_name,
                    $field_config
                );
            }
        }

        //update config setup
        $this->_config = $fields_setup->getConfig();
    }
}
