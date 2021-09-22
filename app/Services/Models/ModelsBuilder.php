<?php

namespace App\Services\Models;

use Exception;

class ModelsBuilder extends BuilderConfig
{
    //models
    private $_models_dir;

    private $_models = [];

    private $_max_length = 100;

    //backup
    private $_backup_name = 'models';

    private $_max_backups = 2;

    //construct
    public function __construct(array $config)
    {
        parent::__construct($config); //construct BuilderConfig
        $this->_models_dir = $this->_base_path . '/app/Models';
    }

    //get model config rules
    public function getModelConfigRules(string $model_ref, array $model_config)
    {
        //rules list method (organize rules)
        $__rules_list = function (array $rules) {
            if (!x_is_list($rules, 0)) {
                $rules = [];
            }
            $required = null;
            $before = [];
            $after = [];
            foreach ($rules as $rule) {
                if (strpos($rule, 'required') !== false) {
                    if (!$required) {
                        $required = $rule;
                    } elseif (strpos($rule, 'required_with') !== false) {
                        $required = $rule;
                    }
                } elseif ($rule == 'sometimes') {
                    array_unshift($before, $rule);
                } elseif ($rule == 'nullable') {
                    $before[] = $rule;
                } else {
                    $after[] = $rule;
                }
            }

            return array_unique(array_merge($before, x_to_array($required), $after));
        };

        //buffer rules method (['field_name' => [...rules]...])
        $buffer = [];
        $__buffer_rules = function (array $rules) use (&$buffer, &$__rules_list) {
            //ignore invalid rules
            if (!x_is_assoc($rules)) {
                return;
            }

            //buffer field rules
            foreach ($rules as $field_name => $field_rules) {
                if (empty($field_rules = array_values($field_rules))) {
                    continue;
                }
                $buffer[$field_name] = $__rules_list(array_merge(
                    $field_rules,
                    x_array_get($field_name, $buffer, [])
                ));
            }
        };

        //buffer model rules
        $__buffer_rules(x_array_get('rules', $model_config, []));

        //buffer model field rules
        if (x_has_key($model_config, 'fields') && x_is_assoc($model_fields = $model_config['fields'])) {
            foreach ($model_fields as $field_name => $field_config) {
                if (!(x_has_key($field_config, 'rules') && x_is_list($field_rules = $field_config['rules'], 0))) {
                    continue;
                }

                //ignore system, guarded fields
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'system', true)) {
                    continue;
                }
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'guarded', true)) {
                    continue;
                }

                //field required
                $required = true;

                //nullable
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'nullable', true)) {
                    array_unshift($field_rules, 'nullable'); //append nullable to rules
                    $required = false;
                }

                //sometimes
                if (x_has_key($field_config, 'default') && x_is_alphan($field_config['default'])) {
                    array_unshift($field_rules, 'sometimes'); //append sometimes to rules
                    $required = false;
                }

                //required
                if ($required) {
                    array_unshift($field_rules, 'required');
                } //append required to rules

                //buffer field rules
                $__buffer_rules([$field_name => $field_rules]);
            }
        }

        //result - buffer
        return $buffer;
    }

    //setup models
    public function setupModels()
    {
        //output
        x_dump('', '[models builder] - setup models..');

        //setup config models
        $config = $this->getConfig();
        foreach ($config as $model_ref => $model_config) {
            //model vars
            $is_pivot = x_has_key($model_config, 'pivot') && !!$model_config['pivot'];
            $model_name = $this->getModelConfig($model_ref, $model_config, 'name');
            $table = $this->getModelConfig($model_ref, $model_config, 'table');

            //model class
            $class = $this->getModelConfig($model_ref, $model_config, 'class');
            $namespace = $this->getModelConfig($model_ref, $model_config, 'namespace');
            $class_path = $this->getModelConfig($model_ref, $model_config, 'class_path');
            $class_name = $this->getModelConfig($model_ref, $model_config, 'class_name');

            //model array attrs
            $casts = $this->getModelConfig($model_ref, $model_config, 'casts', $is_assoc=1, $is_list=0, $is_optional=1);
            $traits = $this->getModelConfig($model_ref, $model_config, 'traits', $is_assoc=0, $is_list=1, $is_optional=1);
            $with = $this->getModelConfig($model_ref, $model_config, 'with', $is_assoc=0, $is_list=1, $is_optional=1);
            $appends = $this->getModelConfig($model_ref, $model_config, 'appends', $is_assoc=0, $is_list=1, $is_optional=1);
            $fillable = $this->getModelConfig($model_ref, $model_config, 'fillable', $is_assoc=0, $is_list=1, $is_optional=1);
            $hidden = $this->getModelConfig($model_ref, $model_config, 'hidden', $is_assoc=0, $is_list=1, $is_optional=1);
            $guarded = $this->getModelConfig($model_ref, $model_config, 'guarded', $is_assoc=0, $is_list=1, $is_optional=1);

            //model rules
            $model_rules = $this->getModelConfigRules($model_ref, $model_config);

            //model relations
            $relations = $this->getModelConfig($model_ref, $model_config, 'relations', $is_assoc=1, $is_list=0, $is_optional=1);

            //check if model is pivot
            $is_pivot = x_has_key($model_config, 'pivot') && !!$model_config['pivot'];

            //model extends
            $tmp = $is_pivot ? 'Pivot' : 'Model';
            $extend = 'App\Base' . $tmp;
            $extend_alt = sprintf('App\%s' . $tmp, $class);
            if (class_exists($extend_alt)) {
                $extend = $extend_alt;
            }

            //model field attrs merge
            $guarded = array_merge($guarded, $this->getAttrFields($model_config, 'guarded', true));
            $fillable = array_merge($fillable, $this->getAttrFields($model_config, 'fillable', true));
            $hidden = array_merge($hidden, $this->getAttrFields($model_config, 'hidden', true));

            //model fields - names buffer
            $model_field_names = [];

            //model fields
            $model_fields = $this->getModelConfig($model_ref, $model_config, 'fields', $is_assoc=1);
            foreach ($model_fields as $field_name => $field_config) {
                //validate field config
                if (!x_is_assoc($field_config)) {
                    throw new Exception(sprintf('Invalid field config! (%s - %s)', $model_ref, $field_name));
                }
                $model_field_names[] = $field_name;

                //ignore replace
                if (x_has_key($field_config, 'replace') && !!$field_config['replace']) {
                    continue;
                }

                //guarded
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'guarded', true)) {
                    $guarded = x_list_add($field_name, $guarded); //add to guarded
                } else {
                    $guarded = x_array_unset_values($guarded, $field_name);
                } //remove from guarded

                //hidden
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'hidden', true)) {
                    $hidden = x_list_add($field_name, $hidden); //add to hidden
                } else {
                    $hidden = x_array_unset_values($hidden, $field_name);
                } //remove from hidden

                //fillable
                if ($this->fieldHasAttr($model_ref, $model_config, $field_name, 'fillable', true)) {
                    $fillable = x_list_add($field_name, $fillable); //add to fillable
                } else {
                    $fillable = x_array_unset_values($fillable, $field_name);
                } //remove from fillable
            }

            //model relations - names buffer
            $relation_names = [];
            if (x_is_assoc($relations)) {
                foreach ($relations as $key => $val) {
                    if (x_is_assoc($val)) {
                        foreach ($val as $k => $v) {
                            $relation_names[] = $k;
                        }
                    }
                }
            }

            //tab, eol
            $t = "\t";
            $eol = PHP_EOL;

            //class lines
            $lines = [];
            $lines[] = '<?php';
            $lines[] = null;
            $lines[] = "namespace $namespace;";
            $lines[] = null;

            //class traits
            $use_traits = [];
            $traits = array_unique(array_map(function ($item) {
                return x_rtrim($item, ';');
            }, $traits));
            foreach ($traits as $trait) {
                $use_traits[] = x_end(x_split('\\', $trait));
                $lines[] = "use $trait;";
            }

            //class extend
            $extend = x_rtrim($extend, ';');
            $extend_class = x_end(x_split('\\', $extend));
            $lines[] = "use $extend;";

            //class use traits
            $lines[] = null;
            $lines[] = null;
            $lines[] = "class $class extends $extend_class";
            $lines[] = '{';
            if (!empty($use_traits)) {
                $lines[] = sprintf('%suse %s;', $t, x_join($use_traits, ', '));
                $lines[] = null;
            }

            //table
            $lines[] = $t . '/**';
            $lines[] = $t . ' * Model table.';
            $lines[] = $t . ' *';
            $lines[] = $t . ' * @var string';
            $lines[] = $t . ' */';
            $lines[] = $t . sprintf("protected \$table = '%s';", $table);

            //reference
            $lines[] = null;
            $lines[] = $t . '/**';
            $lines[] = $t . ' * Model reference.';
            $lines[] = $t . ' *';
            $lines[] = $t . ' * @var string';
            $lines[] = $t . ' */';
            $lines[] = $t . sprintf("protected \$model_ref = '%s';", $model_ref);

            #helper function
            $__protected = function (string $name, array $arr, string $acc='protected') use (&$t) {
                return $t . "$acc \$$name = " . ltrim(x_php_str($arr, $this->_max_length, $t)) . ';';
            };

            /*
            //fields
            $lines[] = null;
            $lines[] = $t . '/**';
            $lines[] = $t . ' * Model fields.';
            $lines[] = $t . ' *';
            $lines[] = $t . ' * @var array';
            $lines[] = $t . ' *\/';
            $lines[] = $__protected('fields', $model_field_names);

            //relations
            if (x_is_list($relation_names, 0)){
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * Model relations.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' *\/';
                $lines[] = $__protected('relations', $relation_names);
            }
            */

            //options
            $model_options = $this->getModelConfig($model_ref, $model_config, 'options', $is_assoc=1, $is_list=0, $is_optional=1);
            if (x_is_assoc($model_options)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * Model options.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('options', $model_options);
            }

            //access
            $model_access = $this->getModelConfig($model_ref, $model_config, 'access', $is_assoc=1, $is_list=0, $is_optional=1);
            if (x_is_assoc($model_access)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * Model access rules.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('access', $model_access);
            }

            //rules
            if (x_is_assoc($model_rules)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * Model validation rules.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('rules', $model_rules);
            }

            //guarded
            if (!empty($guarded)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * The attributes that aren\'t mass assignable.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('guarded', $guarded);
            }

            //hidden
            if (!empty($hidden)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * The attributes that should be hidden for arrays.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('hidden', $hidden);
            }

            //fillable
            if (!empty($fillable)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * The attributes that are mass assignable.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('fillable', $fillable);
            }

            //appends
            if (!empty($appends)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * The accessors to append to the model\'s array form.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('appends', $appends);
            }

            //with
            if (!empty($with)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * The relationships that should always be loaded.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('with', $with);
            }

            //casts
            if (!empty($casts)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * The attributes that should be cast.';
                $lines[] = $t . ' *';
                $lines[] = $t . ' * @var array';
                $lines[] = $t . ' */';
                $lines[] = $__protected('casts', $casts);
            }

            //morphable - morphTo
            if (x_array_get("$model_ref.options.morphable", $config, false)) {
                $lines[] = null;
                $lines[] = $t . '/**';
                $lines[] = $t . ' * @return \Illuminate\Database\Eloquent\Relations\MorphTo';
                $lines[] = $t . ' */';
                $lines[] = $t . 'public function ' . $model_name . 'able(){';
                $lines[] = $t . $t . sprintf('return $this->morphTo();');
                $lines[] = $t . '}';
            }

            //relation methods
            if (x_is_assoc($relations)) {

                //relation types
                $relation_types = [
                    'belongsTo', 'belongsToMany',
                    'hasMany', 'hasManyThrough',
                    'hasOne', 'hasOneOrMany', 'hasOneThrough',
                    'morphMany', 'morphOne', 'morphOneOrMany',
                    'morphPivot', 'morphTo', 'morphToMany',
                ];

                //reorder relations by types
                $tmp = [];
                foreach ($relation_types as $type) {
                    if (x_has_key($relations, $type) && !in_array($type, $tmp)) {
                        $tmp[$type] = $relations[$type];
                    }
                }
                $relations = $tmp;

                //buffer relations
                foreach ($relations as $type => $value) {
                    foreach ($value as $method => $relation) {

                        //ignore system managed
                        if (in_array($type, ['belongsTo', 'hasMany'])) {
                            $local_field = $type == 'belongsTo' ? $relation[1] : ($type == 'hasMany' ? $relation[3] : null);
                            if (!is_null($local_field) && $this->fieldHasAttr($model_ref, $model_config, $local_field, 'system', true)) {
                                continue;
                            }
                        }

                        //set buffer values
                        switch ($type) {

                            //morphOne, morphMany
                            case 'morphOne':
                            case 'morphMany':
                                //relation values
                                $tmp_foreign_ref = $relation;
                                $tmp_foreign_class = x_array_get("$tmp_foreign_ref.class_name", $config, null, 1);
                                $tmp_foreign_name = x_array_get("$tmp_foreign_ref.name", $config, null, 1);
                                $tmp_foreign_morph = $tmp_foreign_name . 'able';

                                //method returns
                                $method_returns = $t . $t . "return \$this->$type('$tmp_foreign_class', '$tmp_foreign_morph');";

                                break;

                            default:
                                //method returns
                                $method_returns = $t . $t . sprintf("return \$this->$type('%s');", x_join(array_slice($relation, 0, $type == 'belongsToMany' ? 4 : 3), "', '"));

                                break;
                        }

                        //comment
                        $lines[] = null;
                        $lines[] = $t . '/**';
                        $lines[] = $t . ' * @return \Illuminate\Database\Eloquent\Relations\\' . ucfirst($type);
                        $lines[] = $t . ' */';

                        //method
                        $lines[] = $t . "public function $method(){";
                        $lines[] = $method_returns;
                        $lines[] = $t . '}';
                    }
                }
            }

            //class end
            $lines[] = '}';

            //setup model
            $this->_models[$model_ref] = [
                'path' => $class_path,
                'lines' => $lines,
            ];
        }
    }

    //empty models folder
    public function emptyModels()
    {
        //get models folder
        $dir = $this->_models_dir;

        //output
        x_dump(...[
            '',
            '[models builder] - empty models folder:',
            '   - ' . x_trim(str_replace($this->_base_path, '', $dir), '/'),
        ]);

        //delete folder contents
        x_dir_delete_contents($dir);
    }

    //backup models folder
    public function backupModels()
    {
        //output
        x_dump('', '[models builder] - backup models');

        //backup models folder
        app()->make('BackupService')->backup(
            $this->_backup_name,
            $this->_models_dir,
            $this->_max_backups,
            $backup_zip=true,
            $zip_keep=false
        );
    }

    //create models
    public function createModels()
    {
        //output
        x_dump('', '[migrations builder] - creating migration files..');

        //get models
        $models = $this->_models;
        $dir = $this->_models_dir;

        //create models
        foreach ($models as $key => $value) {
            $eol = PHP_EOL;
            $path = $value['path'];
            $lines = $value['lines'];

            //php file content - buffer to string
            $content = x_join($lines, $eol);

            //output
            x_dump('- create: ' . x_trim(str_replace($dir, '', $path), '/'));

            //write content to path
            x_file_put($path, $content);
        }
    }

    //build classes
    public function build(bool $backup=false)
    {
        //output
        x_dump('', '[models builder] - build started..');

        //setup models if not setup
        if (empty($this->_models)) {
            $this->setupModels();
        }
        //dd($this->getConfig()['fstats/fs_match']);

        //backup models
        if ($backup) {
            $this->backupModels();
        }

        //empty models folder
        $this->emptyModels();

        //create models
        $this->createModels();

        //output
        x_dump('', '[models builder] - build done.');
    }
}
