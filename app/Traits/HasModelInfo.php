<?php

namespace App\Traits;

trait HasModelInfo
{
    //attrs
    protected $model_uid;

    protected $model_input;

    //set uid
    public function setUid(string $uid=null)
    {
        $uid = ($uid = x_tstr($uid)) ? $uid : uniqid('model_');

        return $this->model_uid = $uid;
    }

    //get uid
    public function getUid()
    {
        return $this->model_uid;
    }

    //get input
    public function getInput(bool $merge_attributes=true)
    {
        $input = x_is_assoc($input = $this->model_input) ? $input : [];
        if ($merge_attributes) {
            $input = array_merge($input, $this->attributesToArray());
        }

        return $input;
    }

    //set input
    public function setInput(array $input=[])
    {
        $input = x_is_assoc($input) ? $input : [];
        $this->fill($input);

        return $this->model_input = $input;
    }

    //get fields
    public function getFields()
    {
        return static::getTableFields();
    }

    //get model ref
    public function modelRef()
    {
        return $this->model_ref;
    }

    //get id
    public function getId(&$key_name=null)
    {
        $key_name = $this->getKeyName();

        return $this->getKey();
    }

    //get access
    public function getAccess()
    {
        return x_arr($this->access);
    }

    //get options
    public function getOptions($key=null)
    {
        $opts = x_arr($this->options);
        if ($key = x_tstr($key)) {
            return array_key_exists($key, $opts) ? $opts[$key] : null;
        }

        return $opts;
    }

    //get rules
    public function getRules()
    {
        return x_arr($this->rules);
    }

    //dump info - debug
    public function dump_info($dd=false)
    {
        $info = [
            'getUid' => $this->getUid(),
            'getInput' => $this->getInput(),
            'getInput(1)' => $this->getInput(1),

            'static::getTableName' => static::getTableName(),
            'static::getRelationships' => static::getRelationships(),
            'getFields' => $this->getFields(),
            'modelRef' => $this->modelRef(),
            'getId' => ['value' => $this->getId($tmp), 'key' => $tmp],
            'getAccess' => $this->getAccess(),
            'getOptions' => $this->getOptions(),
            'getOptions(timestamps)' => $this->getOptions('timestamps'),
            'getRules' => $this->getRules(),

            //eloquent
            'exists' => $this->exists,
            'getTable' => $this->getTable(),
            'getKeyName' => $this->getKeyName(),
            'getKeyType' => $this->getKeyType(),
            'getKey' => $this->getKey(),
            'getForeignKey' => $this->getForeignKey(),
            'getAttributes' => $this->getAttributes(),
            'attributesToArray' => $this->attributesToArray(),
            'getRelations' => $this->getRelations(),
            'getArrayableRelations' => $this->getArrayableRelations(),
            'relationsToArray' => $this->relationsToArray(),
            'getDates' => $this->getDates(),
            'getDateFormat' => $this->getDateFormat(),
            'getDirty' => $this->getDirty(),
            'getChanges' => $this->getChanges(),
            'getMutatedAttributes' => $this->getMutatedAttributes(),
            'getCreatedAtColumn' => $this->getCreatedAtColumn(),
            'getUpdatedAtColumn' => $this->getUpdatedAtColumn(),
            'getHidden' => $this->getHidden(),
            'getVisible' => $this->getVisible(),
            'getFillable' => $this->getFillable(),
            'getGuarded' => $this->getGuarded(),
            'isFillable(created_at)' => $this->isFillable('created_at'),
        ];
        if ($dd) {
            dd($info);
        }

        return $info;
    }

    //static - get table name
    public static function getTableName()
    {
        return (new static)->getTable();
    }

    //static - get field names
    public static function getTableFields($table=null)
    {
        static $cache;
        if (!is_array($cache)) {
            $cache = [];
        }
        if (!(is_string($table) && strlen($table = trim($table)))) {
            $table = static::getTableName();
        }
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        return $cache[$table] = app('DatabaseService')->getTableColumns($table);
    }

    //static - get relationships
    public static function getRelationships()
    {
        static $cache;
        if (!empty($cache)) {
            return $cache;
        }
        $instance = new static;
        $relations = [];
        static::withoutEvents(function () use (&$instance, &$relations) {
            $class = get_class($instance);
            $class_methods = (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);
            $methods = array_filter($class_methods, function ($method) use ($class) {
                return $method->class === $class
                    && !$method->isStatic()
                    && !$method->getParameters() //relationships have no parameters
                    && $method->getName() !== 'getRelationships'; // prevent infinite recursion
            });
            foreach ($methods as $method) {
                $name = $method->name;
                $relation = $instance->{$name}();
                if (is_object($relation) && method_exists($relation, 'getRelated')) {
                    $relations[] = $name;
                }
            }
        });

        return $cache = $relations;
    }
}
