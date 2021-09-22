<?php

namespace App\Services\Models;

use App\Traits\HasLastError;
use Exception;

class ModelsConfig
{
    //uses trait HasLastError
    use HasLastError;

    //model file pattern (example-model_path/example-model_name.php)
    public const MODEL_FILE_PATTERN = '/^[\/-0-9_a-z]+\.php$/i';

    //model name pattern (example-model_name)
    public const MODEL_NAME_PATTERN = '/^[a-z][-0-9_a-z]+$/i';

    //model ref pattern (example_model_path.example_model_name)
    public const MODEL_REF_PATTERN = '/^[a-z][-0-9_a-z\/]*[0-9_a-z]+$/';

    //model namespace pattern
    public const MODEL_NAMESPACE_PATTERN = '/^App\\\Models(\\\[A-Z][0-9a-z]+)+$/';

    //get models config dir
    public function getConfigDir()
    {
        return base_path() . '/database/models';
    }

    //get models app dir
    public function getModelsDir()
    {
        return base_path() . '/app/Models';
    }

    //validate model name
    public function isModelName(string $str, bool $throwable=false)
    {
        if (preg_match(self::MODEL_NAME_PATTERN, $str = trim($str))) {
            return true;
        }

        return $this->lastError(sprintf('String "%s" does not match model name pattern!', $str));
    }

    //validate model $ref string
    public function isModelRef(string $ref, bool $throwable=false)
    {
        if (preg_match($p = self::MODEL_REF_PATTERN, $ref)) {
            return true;
        }

        return $this->lastError(sprintf('String $ref does not match model ref pattern "%s" (%s)', $p, $ref), $throwable);
    }

    //validate model namespace
    public function isModelNamespace(string $str, bool $throwable=false)
    {
        if (preg_match(self::MODEL_NAMESPACE_PATTERN, $str = trim($str))) {
            return true;
        }

        return $this->lastError(sprintf('String "%s" does not match model namespace pattern!', $str));
    }

    //check if $ref model config exists
    public function isModelConfigRef(string $ref, array $config, bool $throwable=false)
    {
        //check config
        if (!x_is_assoc($config)) {
            return $this->lastError('Invalid model config data!', $throwable);
        }

        //check if ref config exists
        if (!(is_string($ref) && ($ref = trim($ref)) && x_has_key($config, $ref))) {
            return $this->lastError(sprintf('Model config ref does not exist! (%s)', $ref), $throwable);
        }

        //check if ref config fields exist
        if (!(x_has_key($config[$ref], 'fields') && x_is_assoc($config[$ref]['fields']))) {
            return $this->lastError(sprintf('Invalid model config ref fields! (%s)', $ref), $throwable);
        }

        //result - exists
        return true;
    }

    //normalize model name - converts to singular snake_case (i.e. example-model_names = example_model_name)
    public function toModelName(string $str)
    {
        $this->isModelName($str, 1);

        return x_snake(x_singular($str), 1);
    }

    //get ref from model path
    public function getModelPathRef(string $path, string $model_name=null)
    {
        if (!($tmp_path = x_normalize_path($path))) {
            throw new Exception(sprintf('Invalid model config file path! (%s)', $path));
        }
        $path_ref = trim(str_replace([$this->getConfigDir(), '.php'], '', $tmp_path), '/');
        $buffer = [];
        $arr = x_split('/', $path_ref, $count);
        foreach ($arr as $i => $item) {
            $this->isModelName($item, 1);
            $item = x_snake($item, 1);
            if ($i == $count - 1 && ($model_name = trim($model_name)) && $item != $model_name) {
                $item = $model_name;
            }
            $buffer[] = $item;
        }

        return x_join($buffer, '/');
    }

    //get table name from model ref
    public function getModelRefTable(string $ref, bool $is_pivot=false)
    {
        $this->isModelRef($ref = trim($ref), 1);
        $arr = x_split('/', $ref, $count);
        $x = $count - 1;
        $buffer = [];
        foreach ($arr as $i => $item) {
            $this->isModelName($item, 1);
            $item = x_snake($item, 1);
            if ($i == $x) {
                //if last item is similar to previous (unset previous)
                if ($i && $item == $buffer[$i - 1]) {
                    unset($buffer[$i - 1]);
                }

                //make last item plural if not pivot
                $item = $is_pivot ? $item : x_plural($item);
            }
            $buffer[] = $item;
        }
        $table = x_join($buffer, '_');

        return $table;
    }

    //get namespace from model ref (sets &$model_class)
    public function getModelRefNamespace(string $ref, string &$model_class=null)
    {
        $this->isModelRef($ref = trim($ref), 1);
        $arr = x_split('/', $ref, $count);
        $model_class = null;
        $x = $count - 1;
        $buffer = [];
        foreach ($arr as $i => $item) {
            $this->isModelName($item, 1);
            $item = x_studly($item, 1);
            if ($i == $x) {
                //model class is last item
                $model_class = $item;

                //ignore last item buffer
                continue;
            }
            $buffer[] = $item;
        }
        $namespace = 'App\Models' . (empty($buffer) ? '' : '\\' . x_join($buffer, '\\'));

        return $namespace;
    }

    //get model class path from ref
    public function getModelClassPath(string $ref)
    {
        $namespace = $this->getModelRefNamespace($ref, $class);
        $path = lcfirst(str_replace('\\', '/', $namespace) . '/' . $class . '.php');

        return base_path() . '/' . $path;
    }
}
