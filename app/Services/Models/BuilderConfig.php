<?php

namespace App\Services\Models;

use Exception;

class BuilderConfig
{
    //vars
    public $_config;

    public $_base_path;

    //attr cache
    private $_attr_cache = [];

    //construct
    public function __construct(array $config)
    {
        $this->_base_path = base_path();
        $this->_config = $this->isConfig($config);
    }

    //validate config
    public function isConfig($config)
    {
        if (!x_is_assoc($config)) {
            throw new Exception('Invalid models config!');
        }

        return $config;
    }

    //get config
    public function getConfig()
    {
        return $this->isConfig($this->_config);
    }

    //get nullable fields
    public function getAttrFields(array $model_config, string $attr_name, bool $attr_value=true)
    {
        //attr fields buffer
        $buffer = [];

        //model fields
        if (x_has_key($model_config, 'fields') && x_is_assoc($model_fields = $model_config['fields'])) {
            foreach ($model_fields as $field_name => $field_config) {
                if (x_has_key($field_config, $attr_name) && $field_config[$attr_name] == $attr_value) {
                    if (!in_array($field_name, $buffer)) {
                        $buffer[] = $field_name;
                    }
                }
            }
        }

        //model field attrs
        $attrs = 'field_attrs';
        if (x_has_key($model_config, $attrs) && x_has_key($tmp = $model_config[$attrs], $attr_name) && x_is_assoc($field_attrs = $tmp[$attr_name])) {
            foreach ($field_attrs as $field_name => $val) {
                if ($val == $attr_value && !in_array($field_name, $buffer)) {
                    $buffer[] = $field_name;
                }
            }
        }

        //result - attr fields
        return $buffer;
    }

    //get foreign fields
    public function getForeignFields(array $model_config)
    {
        //get config
        $config = $this->getConfig();

        //get foreign fields
        $buffer = [];
        if (x_has_key($model_config, 'relations') && x_has_key($relations = $model_config['relations'], 'belongsTo') && x_is_assoc($belongsTo = $relations['belongsTo'])) {
            foreach ($belongsTo as $key => $value) {
                //$value = [FOREIGN_MODEL_CLASS, LOCAL_FIELD_NAME, FOREIGN_FIELD_NAME, FOREIGN_MODEL REF]
                if (!(x_is_list($value) && count($value) >= 4)) {
                    continue;
                }
                $foreign_model_ref = $value[3];
                $foreign_field = $value[2];
                $local_field = $value[1];

                //get foreign table
                if (!(x_has_key($config, $foreign_model_ref) && x_has_key($config[$foreign_model_ref], 'table') && strlen($foreign_table = x_tstr($config[$foreign_model_ref]['table'])))) {
                    throw new Exception(sprintf('Foreign model ref table not found! (%s)', $foreign_model_ref));
                }

                //buffer field [FOREIGN_TABLE, FOREIGN_FIELD, FOREIGN_MODEL_REF]
                $buffer[$local_field] = [
                    $foreign_table,
                    $foreign_field,
                    $foreign_model_ref
                ];
            }
        }

        //result - foreign fields buffer
        return $buffer;
    }

    //get model ref related tables
    public function getRelatedTables(string $model_ref)
    {
        //get config
        $config = $this->getConfig();

        //get config model
        if (!x_has_key($config, $model_ref)) {
            throw new Exception(sprintf('Model ref not found! (%s)', $model_ref));
        }

        //tables buffer
        $tables = [];

        //get model config
        $model_config = $config[$model_ref];

        //get model table
        $table = $model_config['table'];

        //get model foreign fields
        if (!empty($foreign_fields = $this->getForeignFields($model_config))) {
            //foreign ref = [FOREIGN_TABLE, FOREIGN_FIELD, FOREIGN_MODEL_REF]
            foreach ($foreign_fields as $local_field => $foreign_ref) {
                if (!count($foreign_ref) == 3) {
                    throw new Exception('Invalid foreign ref!');
                }

                //get foreign table
                $foreign_table = $foreign_ref[0];
                $foreign_model_ref = $foreign_ref[2];

                //recursively get foreign table related tables
                $foreign_tables = $this->getRelatedTables($foreign_model_ref);

                //buffer add foreign tables
                foreach ($foreign_tables as $item) {
                    if (!in_array($item, $tables) && !in_array($item, [$table, $foreign_table])) {
                        $tables[] = $item;
                    }
                }

                //buffer add foreign table
                $tables[] = $foreign_table;
            }
        }
        //buffer add model table
        $tables[] = $table;

        //result - model tables
        return $tables;
    }

    //get model config value
    public function getModelConfig(
        string &$model_ref,
        array &$model_config,
        string $key,
        bool $is_assoc=false,
        bool $is_list=false,
        bool $is_optional=false
    ) {
        //check if config has key
        if (!x_has_key($model_config, $key)) {
            //if optional return optional default value
            if ($is_optional) {
                return $is_list || $is_assoc ? [] : null;
            }

            //else throw exception
            throw new Exception(sprintf('Undefined model config %s! (%s)', $key, $model_ref));
        }

        //get key value
        $val = $model_config[$key];

        //check value validity
        $is_valid = $is_assoc && ($is_optional ? is_array($val) : x_is_assoc($val))
            || $is_list && ($is_optional ? is_array($val) : x_is_list($val, 0))
            || ($is_optional ? 1 : !$is_assoc && !$is_list && x_is_alphan($val, 1));

        //validate value
        if (!$is_valid) {
            throw new Exception(sprintf('Model config %s value is invalid! (%s)', $key, $model_ref));
        }

        //result - config value
        return $val;
    }

    //checks if field ($field_name) has attribute ($attr_name) with bool value ($attr_value)
    public function fieldHasAttr(
        string &$model_ref,
        array &$model_config,
        string $field_name,
        string $attr_name,
        bool $attr_value=true
    ) {
        //init model cache
        $cache = $this->_attr_cache;
        if (!(x_has_key($cache, $model_ref) && is_array($cache[$model_ref]))) {
            $cache[$model_ref] = [];
        }

        //init model cache key
        $key = sprintf('%s-%s', $attr_name, $attr_value ? 1 : 0);
        if (!(x_has_key($cache[$model_ref], $key) && is_array($cache[$model_ref][$key]))) {
            $cache[$model_ref][$key] = [];
        }

        //set cache key value
        if (empty($cache[$model_ref][$key])) {
            $cache[$model_ref][$key] = $this->getAttrFields($model_config, $attr_name, $attr_value);
            $this->_attr_cache = $cache; //update attr cache
        }

        //result - (bool) field name has attr value
        return in_array($field_name, $cache[$model_ref][$key]);
    }
}
