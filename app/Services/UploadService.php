<?php

namespace App\Services;

use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Image;

class UploadService
{
    //attrs
    protected $_store;

    //construct
    public function __construct()
    {
        $this->_store = Storage::disk('public');
    }

    //store
    public function store()
    {
        return $this->_store;
    }

    //store dir
    public function storeDir(string $path=null)
    {
        $dir = rtrim($this->store()->path(''), '/');
        if ($path = x_normalize_path($path)) {
            $dir .= '/' . ltrim($path, '/');
        }

        return $dir;
    }

    //config
    public function config(string $path=null, $default=null)
    {
        return x_array_get($path, x_arr(config('services.upload')), $default);
    }

    //upload dir
    public function dir(bool $temp=false, &$store_dir=null)
    {
        if (!($dir = x_normalize_path($this->config('dir')))) {
            $dir = 'uploads';
        }
        $store_dir = $this->storeDir();

        return $dir . ($temp ? '/temp' : '');
    }

    //upload path
    public function path(string $basename=null, string $ext=null, bool $temp=false, &$store_dir=null)
    {
        if (!(strlen($basename = x_tstr($basename)) && ($basename = x_tstr(basename($basename))))) {
            $basename = x_uuid();
        }
        if (strlen($ext = ltrim(x_tstr($ext), '.')) && !preg_match('/\.' . preg_quote($ext, '/') . '$/', $basename)) {
            $basename .= ".$ext";
        }

        return $this->dir($temp, $store_dir) . "/$basename";
    }

    //relative upload path
    public function rpath(string $path)
    {
        if ($path = x_normalize_path($path)) {
            return $path = ltrim(str_replace($this->storeDir(), '', $path), '/');
        }
    }

    //get file type config
    public function fileType(string $type)
    {
        static $cache;

        //check type string - ignore empty
        if (!($type = x_tstr($type))) {
            return;
        }

        //cached config
        if (!is_array($cache)) {
            $cache = [];
        }
        if (array_key_exists($type, $cache)) {
            return $cache[$type];
        }

        //get config
        $config = $this->config("file_types.$type");

        //check config - ignore invalid
        if (x_is_assoc($config)) {

            //config extend
            if (isset($config['extend']) && ($extend = x_tstr($config['extend'])) != $type) {

                //unset config extend
                unset($config['extend']);

                //get extended config
                $extend_config = $this->fileType($extend);

                //merge extended config
                if (x_is_assoc($extend_config)) {
                    $config = array_merge($extend_config, $config);
                }
            }

            //result
            return $cache[$type] = $config;
        }
    }

    //get file type options
    public function fileOptions(string $type)
    {
        //get type config
        if (!(x_is_assoc($config = $this->fileType($type)))) {
            return;
        }

        //set vars
        $max = (1024 * 5);
        $mimes = [];
        $dimens = [];
        $dimens_min = [];
        $dimens_max = [];
        $rules = ['file'];

        //max
        if (isset($config['max']) && ($tmp = x_num($config['max'])) >= 1) {
            $rules[] = "max:$tmp";
            $max = $tmp;
        }

        //mimes
        if (isset($config['mimes']) && x_is_assoc($tmp = $config['mimes']) >= 1) {
            $rules[] = 'mimes:' . implode(',', array_unique(array_values($tmp)));
            $mimes = $tmp;
        }

        //dimensions - min
        if (isset($config['dimensions_min'])) {
            $tmp = $config['dimensions_min'];
            if (x_is_list($tmp, 0) && ($val = x_num($tmp[0])) >= 1) {
                $dimens[] = "min_width=$val";
                $dimens_min[] = $val;
                if (isset($tmp[1]) && ($val = x_num($tmp[1])) >= 1) {
                    $dimens[] = "min_height=$val";
                    $dimens_min[] = $val;
                }
            } elseif (($val = x_num($tmp)) >= 1) {
                $dimens[] = "min_width=$val,min_height=$val";
                $dimens_min = [$val, $val];
            }
        }

        //dimensions - max
        $resize = isset($config['resize']) && $config['resize'];
        if (isset($config['dimensions_max'])) {
            $tmp = $config['dimensions_max'];
            if (x_is_list($tmp, 0) && ($val = x_num($tmp[0])) >= 1) {
                if (!$resize) {
                    $dimens[] = "max_width=$val";
                }
                $dimens_max[] = $val;
                if (isset($tmp[1]) && ($val = x_num($tmp[1])) >= 1) {
                    if (!$resize) {
                        $dimens[] = "max_height=$val";
                    }
                    $dimens_max[] = $val;
                }
            } elseif (($val = x_num($tmp)) >= 1) {
                if (!$resize) {
                    $dimens[] = "max_width=$val,max_height=$val";
                }
                $dimens_max = [$val, $val];
            }
        }

        //dimensions
        if (count($dimens)) {
            $rules[] = 'dimensions:' . implode(',', $dimens);
        }
        $dimensions = count($dimens_min) || count($dimens_max) ? [
            'min' => $dimens_min,
            'max' => $dimens_max,
            'resize' => $resize,
        ] : null;

        //result
        return [
            'rules' => $rules,
            'max' => $max,
            'mimes' => $mimes,
            'dimensions' => $dimensions,
        ];
    }

    //save upload
    public function save($value, string $type='file', string $attribute=null, bool $str_ignore=false, &$name=null, &$errors=[])
    {
        //set vars
        $errors = [];
        $file = null;
        $temp_file = null;
        $name = null;
        $type = x_tstr($type);
        $attribute = x_tstr($attribute);
        $trans_data = [
            'type' => $type,
            'value' => $value,
            'attribute' => $attribute,
        ];

        //file options
        if (!x_is_assoc($opts = $this->fileOptions($type))) {
            $errors[] = trans('validation.file_type', $trans_data);

            return false;
        }

        //string value
        if (is_string($value)) {

            //string must be url
            $url = x_tstr($value);
            $trans_data['url'] = $url;

            //validate url
            if (!x_is_url($url)) {

                //ignore non urls
                if ($str_ignore) {
                    return null;
                }

                //error
                $errors[] = trans('validation.url', $trans_data);

                return false;
            }

            //url get
            $res = Http::get($url);

            //url get failure
            if (!$res->successful()) {
                $trans_data['status'] = $res->status();

                try {
                    $res->throw();
                } catch (Exception $e) {
                    $err = ($res->serverError() ? 'Server' : 'Client') . ' Error';
                    $err .= "[$status]:" . $e->getMessage();
                    $trans_data['error'] = $err;
                }
                $errors[] = trans('validation.url_get', $trans_data);

                return false;
            }

            //check mime type
            $mime = $res->header('Content-Type');
            if (!(x_is_assoc($mimes = $opts['mimes']) && x_has_key($mimes, $mime))) {
                $trans_data['mime'] = $mime;
                $trans_data['values'] = implode(', ', array_keys($mimes));
                $errors[] = trans('validation.mimetypes', $trans_data);

                return false;
            }

            //set file ext
            $file_ext = $mimes[$mime];

            //check content size
            $size = strlen($contents = $res->body());
            $size = round($size/1024, 2); //to kb
            if (isset($opts['max']) && ($max = $opts['max']) >= 1 && $size > $max) {
                $trans_data['max'] = $max;
                $trans_data['size'] = $size;
                $errors[] = trans('validation.max.file', $trans_data);

                return false;
            }

            //save temp file
            $path = $this->path(null, $file_ext, 1);
            if (!$this->store()->put($path, $contents)) {
                $errors[] = trans('validation.file_save', $trans_data);

                return false;
            }

            //set file
            $file = new File($temp_file = $this->storeDir($path));

            //set name (basename)
            $name = basename($url);
            $name = ($p = strpos($name, '?')) !== false ? substr($name, 0, $p) : $name;
            if (!($name = x_tstr($name))) {
                $name = basename($temp_file);
            }
            if (!preg_match('/\.' . preg_quote($file_ext, '/') . '$/', $name)) {
                $name .= ".$file_ext";
            }
        }

        //file value
        else {
            $file = $value;
        }

        //validate file
        $attr = $attribute != '' ? $attribute : 'upload';
        $input = [$attr => $file];
        $rules = [$attr => $opts['rules']];
        if (!x_validate($input, $rules, null, $valid, $validator)) {
            if ($temp_file) {
                x_file_delete($temp_file);
            } //delete invalid temp file
            $errors = array_merge($errors, $validator->errors()->all());

            return false;
        }

        //save unsaved
        if (!$temp_file) {

            //save temp file
            $path = $this->path(null, $file->extension(), 1);
            if (!$this->store()->putFileAs(dirname($path), $file, basename($path))) {
                $errors[] = trans('validation.file_save', $trans_data);

                return false;
            }

            //set name (basename)
            $name = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : basename($file->path());

            //set file
            $file = new File($temp_file = $this->storeDir($path));
        }

        //image resize
        if (
            strpos($file->getMimeType(), 'image') !== false
            && x_array_get('dimensions.resize', $opts)
            && x_is_list($wh = x_array_get('dimensions.max', $opts), 0)
        ) {
            try {
                Image::make($path = $file->path())
                -> fit($wh[0], $wh[1], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }, 'top-left')
                -> save($path);
            } catch (Exception $e) {
                if ($temp_file) {
                    x_file_delete($temp_file);
                } //delete failed temp file
                $trans_data['error'] = $e->getMessage();
                $errors[] = trans('validation.resize', $trans_data);

                return false;
            }
        }

        //set upload path
        $path = $this->rpath($file->path());

        //add global uploads
        $global_uploads = x_arr(x_globals_get('uploads'));
        $glboal_uploads[] = $path;
        x_globals_set('uploads', $glboal_uploads);

        //result
        return $path;
    }

    //uploads cleanup
    public function cleanup()
    {
        return x_dir_delete($this->storeDir($this->dir(1)));
    }
}
