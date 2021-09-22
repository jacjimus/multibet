<?php

namespace App\Services\Models;

use Exception;

class FieldConfigSetup extends ModelsConfig
{
    //vars
    private $_config;

    private $_field_types;

    private $_field_type_primary;

    //construct
    public function __construct(array $config)
    {
        $this->_config = $this->isConfig($config);
        $this->_field_types = (new FieldTypes)->getFieldTypes();
        $this->_field_type_primary = FieldTypes::PRIMARY_KEY_TYPE;
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
        return $this->_config;
    }

    //setup field config
    public function setupFieldConfig(string $model_ref, string $field_name, $field_config)
    {
        //validate model ref
        $this->isModelConfigRef($model_ref, $this->_config, 1);

        //validate field name
        $this->isModelName($field_name, 1);

        //ignore setup
        if (x_has_key($field_config, 'is_setup') && $field_config['is_setup']) {
            return;
        }

        //field is foreign
        if (x_has_key($field_config, 'foreign')) {
            return $this->setupForeign($model_ref, $field_name, $field_config);
        }

        //get field type
        $field_type = $this->getFieldConfigType($field_config, $field_type_config);

        //convert string field config to array
        if (!x_is_assoc($field_config)) {
            $field_config = [];
        }

        //setup field type config
        $this->setupFieldTypeConfig(
            $model_ref,
            $field_name,
            $field_config,
            $field_type_config
        );
    }

    //setup foreign field
    public function setupForeign(string $model_ref, string $field_name, array $field_config)
    {
        $config = &$this->_config;
        $model_config = x_array_get($model_ref, $config, null, 1);

        //model vars
        $is_pivot = !!x_array_get('pivot', $model_config);
        $model_name = x_array_get('name', $model_config, null, 1);
        $model_table = x_array_get('table', $model_config, null, 1);
        $model_class = x_array_get('class_name', $model_config, null, 1);

        //foreign values
        $foreign_ref = x_array_get('foreign.0', $field_config, null, 1);
        $foreign_field = x_array_get('foreign.1', $field_config, null, 1);
        $foreign_class = x_array_get("$foreign_ref.class_name", $config, null, 1);
        $foreign_name = x_array_get("$foreign_ref.name", $config, null, 1);
        $foreign_table = x_array_get("$foreign_ref.table", $config, null, 1);

        //belongsTo
        $tmp = preg_replace('/_id$/', '', $field_name); //i.e. auth_user_id > auth_user
        $tmp = trim(preg_replace('/(^' . $foreign_name . '|' . $foreign_name . '$)/', '', $tmp), '_'); //i.e foreign_name = user: auth_user > auth
        $method = lcfirst(x_studly($tmp . '_' . x_singular($foreign_table), 1)); //i.e. foreign_table = user: auth > auth_user
        x_array_set("$model_ref.relations.belongsTo.$method", $config, [
            $foreign_class,
            $field_name,
            $foreign_field,
            $foreign_ref,
        ]);

        //setup foreign models
        $foreign_models = x_array_get("$model_ref.foreign_models", $config, []);
        if (!in_array($foreign_ref, $foreign_models)) {
            $foreign_models[] = $foreign_ref;
            x_array_set("$model_ref.foreign_models", $config, $foreign_models);
        }

        //setup foreign reference
        x_array_set(
            "$model_ref.foreign_reference.$field_name",
            $config,
            sprintf(
                "\$table->foreign('%s')->references('%s')->on('%s')",
                $field_name,
                $foreign_field,
                $foreign_table
            )
        );

        //if field name is id
        if (preg_match('/_id$/', $field_name)) {

            //hasMany/hasOne
            if (!$is_pivot) {

                //method
                $tmp = preg_replace('/_id$/', '', $field_name);
                $tmp = preg_replace('/^' . $foreign_name . '/', '', $tmp);
                $method = lcfirst(x_studly($tmp . '_' . $model_table, 1));

                //foreign has
                $foreign_has_one = !!x_array_get('foreign_has_one', $field_config);
                $method = $foreign_has_one ? x_singular($method) : $method;
                $tmp_has = $foreign_has_one ? 'hasOne' : 'hasMany';

                //set foreign relation
                x_array_set("$foreign_ref.relations.$tmp_has.$method", $config, [
                    $model_class,
                    $field_name,
                    $foreign_field,
                    $foreign_name,
                ]);
            }

            //pivot belongsToMany
            else {

                //check model fields for pivot foreign fields
                $model_fields = x_array_get("$model_ref.fields", $config, null, 1);
                foreach ($model_fields as $tmp_field => $tmp_field_config) {

                    //ignore self or model field not a foreign id field
                    if (!($tmp_field != $field_name && preg_match('/_id$/', $field_name) && x_has_key($tmp_field_config, 'foreign'))) {
                        continue;
                    }

                    //get pivot field foreign
                    $tmp_foreign_ref = x_array_get('foreign.0', $tmp_field_config, null, 1);
                    $tmp_foreign_name = x_array_get("$tmp_foreign_ref.name", $config, null, 1);
                    $tmp_foreign_class = x_array_get("$tmp_foreign_ref.class_name", $config, null, 1);

                    //get pivot foreign method
                    $tmp = preg_replace('/_id$/', '', $field_name);
                    $tmp = preg_replace('/^' . $foreign_name . '/', '', $tmp);
                    $method = lcfirst(x_studly(preg_replace('/_id$/', '', $tmp_field), 1));
                    $belong_method = lcfirst(x_studly($tmp . '_' . x_plural($method), 1));

                    //set foreign model options - pivots
                    x_array_set("$foreign_ref.options.pivots", $config, true);

                    //set foreign relation - belongsToMany
                    x_array_set("$foreign_ref.relations.belongsToMany.$belong_method", $config, [
                        $tmp_foreign_class,
                        $model_table,
                        $field_name,
                        $tmp_field,
                    ]);
                }
            }
        }

        //unset type
        unset($field_config['type']);

        //update field config
        $field_config = x_merge($field_config, [
            'is_setup' => true,
            'rules' => [
                'integer',
                sprintf('exists_or_null:%s,%s', $foreign_table, $foreign_field),
            ],
            'table' => "\$table->unsignedBigInteger('$field_name')",
        ]);

        //update model field
        x_array_set("$model_ref.fields.$field_name", $config, $field_config);
    }

    //setup field type config
    public function setupFieldTypeConfig(string $model_ref, string $field_name, array $field_config, array $field_type_config)
    {

        //validate model ref
        $this->isModelConfigRef($model_ref, $this->_config, 1);

        //validate field name
        $this->isModelName($field_name, 1);

        //unset type
        unset($field_config['type']);

        //field names
        $buffer_field_names = [];

        //field replace
        $is_replaced = x_has_key($field_type_config, 'replace') && !!$field_type_config['replace'];
        if ($is_replaced) {
            $field_config['replace'] = true;
        }

        //field options
        if (x_has_key($field_type_config, 'options') && x_is_assoc($options = $field_type_config['options'])) {
            foreach ($options as $option => $value) {
                x_array_set("$model_ref.options.$option", $this->_config, $value);
            }
        }

        //field rename
        if (x_has_key($field_type_config, 'rename')) {
            $this->isModelName($name = $field_type_config['rename'], 1);
            if (($name = trim($name)) != $field_name) {

                //model fields - unset new name if exists
                if (x_has_key($this->_config[$model_ref]['fields'], $name)) {
                    unset($this->_config[$model_ref]['fields'][$name]);
                }

                //model fields - insert new field name
                $this->_config[$model_ref]['fields'] = x_array_insert(
                    [$name => $field_config],
                    $this->_config[$model_ref]['fields'],
                    $field_name //insert at current field name index
                );

                //model fields - unset current field name & update $field_name
                unset($this->_config[$model_ref]['fields'][$field_name]);
                $field_name = $name;
            }
        }

        //field replaces - unset model replaced fields
        if (x_has_key($field_type_config, 'replaces') && x_is_list($replaces = $field_type_config['replaces'])) {
            foreach ($replaces as $name) {
                //replace field name '{column}'
                if (strpos($name, '{column}') !== false) {
                    $name = str_replace('{column}', $field_name, $name);
                }

                //ignore field name
                if ($name == $field_name) {
                    continue;
                }

                //model fields - unset replaced field
                unset($this->_config[$model_ref]['fields'][$name]);

                //add name to field names buffer
                if (!in_array($name, $buffer_field_names)) {
                    $buffer_field_names[] = $name;
                }
            }
        }

        //field - merge field configs
        if (x_has_key($field_type_config, 'field') && x_is_assoc($tmp = $field_type_config['field'])) {
            $field_config = x_merge($tmp, $field_config); //merge (current field config prioritized)
        }

        //table - field table create column method
        if (x_has_key($field_type_config, 'table') && is_string($tmp = $field_type_config['table']) && strlen($tmp)) {
            $field_config['table'] = trim($tmp); //update field config 'table'
        }

        if (x_has_key($field_config, 'table') && !x_is_empty($field_config['table'])) {
            if (strpos($field_config['table'], ':column') !== false) {
                $field_config['table'] = str_replace(':column', "'$field_name'", $field_config['table']);
            }
        }

        //fields - setup extra fields
        if (x_has_key($field_type_config, 'fields') && x_is_assoc($extra_fields = $field_type_config['fields'])) {
            $insert_at = $field_name; //fields insert after index
            foreach ($extra_fields as $extra_field_name => $extra_field_config) {
                //insert extra field
                $this->_config[$model_ref]['fields'] = x_array_insert(
                    [$extra_field_name => $extra_field_config],
                    $this->_config[$model_ref]['fields'],
                    $insert_at,
                    $insert_after=true
                );

                //update insert at index
                $insert_at = $extra_field_name;

                //setup extra field
                $this->setupFieldConfig(
                    $model_ref,
                    $extra_field_name,
                    $extra_field_config
                );

                //add extra field name to field names buffer
                if (!in_array($extra_field_name, $buffer_field_names)) {
                    $buffer_field_names[] = $extra_field_name;
                }
            }
        }

        //update field config
        $field_config['is_setup'] = true;
        x_array_set("$model_ref.fields.$field_name", $this->_config, $field_config);

        //modify field names buffer
        if (!$is_replaced && !in_array($field_name, $buffer_field_names)) {
            $buffer_field_names[] = $field_name;
        }
        if ($is_replaced && ($i = array_search($field_name, $buffer_field_names)) !== false) {
            unset($buffer_field_names[$i]);
        }
        $buffer_field_names = x_to_list($buffer_field_names);

        //field cast
        if (x_has_key($field_type_config, 'cast')) {
            x_array_set("$model_ref.casts.$field_name", $this->_config, $field_type_config['cast']);
        }

        //list field attrs
        foreach (['traits', 'appends', 'with'] as $key) {
            if (!(x_has_key($field_type_config, $key) && x_is_list($items = $field_type_config[$key]))) {
                continue;
            }
            $path = "$model_ref.$key";
            x_array_set(
                $path,
                $this->_config,
                array_unique(array_merge(
                    x_array_get($path, $this->_config, []),
                    $items
                ))
            );
        }

        //bool field attrs
        foreach (['hidden', 'fillable', 'nullable', 'system'] as $attr_name) {
            if (!x_has_key($field_type_config, $attr_name)) {
                continue;
            }
            $this->setupFieldsBoolAttr(
                $model_ref,
                $attr_name,
                $field_type_config[$attr_name],
                $buffer_field_names
            );
        }
    }

    //setup model field type attr
    public function setupListItem(string $model_ref, string $list, string $item)
    {
        //validate model config ref
        $this->isModelConfigRef($model_ref, $this->_config, 1);

        //validate list name
        if (!x_is_alphan($list, 1)) {
            throw new Exception(sprintf('Invalid model list name! (%s)', $list));
        }
        if (is_string($list)) {
            $list = trim($list);
        }

        //validate list item
        if (!x_is_alphan($item, 1)) {
            throw new Exception(sprintf('Invalid model list item! (%s)', $item));
        }
        if (is_string($item)) {
            $item = trim($item);
        }

        //init list
        if (!(x_has_key($this->_config[$model_ref], $list) && is_array($this->_config[$model_ref][$list]))) {
            $this->_config[$model_ref][$list] = [];
        }

        //add item if not in list
        if (!in_array($item, $this->_config[$model_ref][$list])) {
            $this->_config[$model_ref][$list][] = $item;
        }
    }

    //setup model fields boolean attribute
    public function setupFieldsBoolAttr(
        string $model_ref,
        string $attr_name,
        $attr_value,
        array $affected_fields=null
    ) {
        //validate model config ref
        $this->isModelConfigRef($model_ref, $this->_config, 1);

        //field attr buffer [...FIELD_NAME => bool]
        $buffer = [];

        //list attr value [...FIELD_NAME] ($value = true)
        if (x_is_list($attr_value, 0)) {
            foreach ($attr_value as $attr_field) {
                $this->isModelName($attr_field, 1); //validate attr field
                $buffer[$attr_field] = true; //buffer attr field => true
            }
        }

        //assoc attr value [...FIELD_NAME => !!$value]
        elseif (x_is_assoc($attr_value)) {
            foreach ($attr_value as $attr_field => $value) {
                $this->isModelName($attr_field, 1); //validate attr field
                $buffer[$attr_field] = !!$value; //buffer attr field => (bool) $val
            }
        }

        //other attr value [...$affected_fields] ($value=!!$attr_value)
        elseif (x_is_list($affected_fields, 0)) {
            foreach ($affected_fields as $attr_field) {
                $this->isModelName($attr_field, 1); //validate attr field
                $buffer[$attr_field] = !!$attr_value; //buffer attr field => (bool) $attr_value
            }
        }

        //setup attr buffer
        foreach ($buffer as $attr_field => $value) {
            //model config field attrs key
            $key = 'field_attrs';

            //init model config field attrs key
            if (!(x_has_key($this->_config[$model_ref], $key) && is_array($this->_config[$model_ref][$key]))) {
                $this->_config[$model_ref][$key] = [];
            }

            //init attr
            if (!(x_has_key($this->_config[$model_ref][$key], $attr_name) && is_array($this->_config[$model_ref][$key][$attr_name]))) {
                $this->_config[$model_ref][$key][$attr_name] = [];
            }

            //setup attr field value
            $this->_config[$model_ref][$key][$attr_name][$attr_field] = $value;
        }
    }

    //get field config
    public function getFieldConfig(string $model_ref, string $field_name)
    {
        //validate model config ref
        $this->isModelConfigRef($model_ref, $config = $this->_config, 1);

        //get model_fields
        $model_fields = $config[$model_ref]['fields'];

        //check if field exists
        if (!x_has_key($model_fields, $field_name)) {
            throw new Exception(sprintf('Undefined model field "%s"! (%s)', $field_name, $model_ref));
        }

        //return field config
        return $model_fields[$field_name];
    }

    //get field type (sets $field_type_config)
    public function getFieldConfigType($field_config, &$field_type_config=null)
    {
        //dump(['getFieldConfigType $field_config' => $field_config]);

        //init field type config
        $field_type_config = null;

        //get field type from field config
        $field_type = null;
        if (is_string($field_config)) {
            $field_type = trim($field_config);
        } elseif (x_has_key($field_config, 'type') && is_string($tmp = $field_config['type'])) {
            $field_type = trim($tmp);
        }

        //validate field type
        if (x_is_empty($field_type)) {
            throw new Exception('Invalid field config!');
        }

        //get field type config
        if (!x_is_assoc($field_types = $this->_field_types)) {
            throw new Exception('Unable to get model field types!');
        }
        if (!x_has_key($field_types, $field_type)) {
            throw new Exception(sprintf('Undefined field type! (%s)', $field_type));
        }

        //set field type config
        $field_type_config = $field_types[$field_type];

        //return valid field type
        return $field_type;
    }
}
