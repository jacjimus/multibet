<?php

//base path
function x_base_path()
{
    //check base_path method
    if (!function_exists('base_path')) {
        $base_path = trim($_SERVER['DOCUMENT_ROOT']);
        if (!$base_path) {
            $base_path = dirname(__FILE__);
        }
    } else {
        $base_path = base_path();
    }

    //result - base_path
    return $base_path;
}

//is path
function x_is_path($path)
{
    //tests illegal path
    $pattern = '/^[^*?"<>|:]*$/';

    //check if path is string
    if (!(is_string($path) && ($path = trim($path)))) {
        return null;
    }

    //check if path is valid
    if (preg_match($pattern, $path)) {
        $is_valid = true;
    } else {
        $is_valid = strpos($path, ':') === 1 && preg_match('/[a-zA-Z]/', $path[0]) && preg_match($pattern, substr($path, 2));
    }

    //result - valid path or false
    return $is_valid ? $path : false;
}

//normalize path
function x_normalize_path($path, bool $throwable=false)
{
    //test path
    if (!($tmp = x_is_path($path))) {
        if ($throwable) {
            throw new Exception(sprintf('Invalid path string! (%s)', $path));
        }

        return null;
    }
    $path = $tmp;

    //extract drive from path
    $s = DIRECTORY_SEPARATOR;
    $drive = '';
    if (strpos($path, ':') === 1 && preg_match('/[a-zA-Z]/', $path[0])) {
        if (strpos($path, '\\') !== false) {
            $s = '\\';
        }
        $drive = substr($path, 0, 2);
        $path = substr($path, 2);
    }

    //convert \ to /
    $temp = str_replace(['\\', '/'], '/', $path);

    //if path begins with '/' store to prepend after normalization
    $slash_prepend = $temp[0] === '/' ? '/' : '';

    //if path ends with '/' store to append after normalization
    $slash_append = strlen($temp) > 1 && $temp[-1] === '/' ? '/' : '';

    //normalize path
    if ($temp = x_trim($temp, '/')) {

        //parts buffer
        $buffer = [];

        //split path '/'
        $parts = explode('/', $temp);

        //parse parts
        foreach ($parts as $part) {
            //ignore empty or . path parts
            if (!($part = trim($part)) || $part === '.') {
                continue;
            }

            //add part to buffer
            $buffer[] = $part;
        }

        //join buffer '/'
        $temp = count($buffer) ? implode('/', $buffer) : '';
    }

    //get normalized path (append/prepend slashes)
    $path = sprintf('%s%s%s', $slash_prepend, $temp, $slash_append);

    //replace path '//' to '/'
    if ($path === '//') {
        $path = '/';
    }

    //replace path separator
    if ($s !== '/') {
        $path = str_replace('/', $s, $path);
    }

    //result - normalized path
    return $path;
}

//is dir
function x_is_dir($path, bool $throwable=false, &$norm_path=null, &$error=null)
{
    if (x_file_exists($path, $throwable, $norm_path, $error) && is_dir($norm_path)) {
        return $norm_path;
    }
    $error = "Directory does not exist! ($path)";
    if ($throwable) {
        throw new Exception($error);
    }

    return false;
}

//dir create
function x_mkdir($path, int $permissions=0775)
{
    //test path
    if (!($tmp = x_normalize_path($path))) {
        throw new Exception(sprintf('Invalid create directory path! (%s)', $path));
    }

    //normalized path
    $path = $tmp;

    //return dir path if already exists
    if (file_exists($path) && is_dir($path)) {
        return $path;
    }

    //create dir
    mkdir($path, $permissions, true);

    //check if dir was created
    if (!(file_exists($path) && is_dir($path))) {
        throw new Exception(sprintf('Failed to create directory! (%s)', $path));
    }

    //result - directory path
    return $path;
}

//is protected dir
function x_is_protected_dir($path)
{
    //ignore invalid path
    if (!($path = x_normalize_path($path))) {
        return;
    }

    //protected directories
    $protected = [
        '/',

        '/app',
        '/app/Console/.*',
        '/app/Channels/.*',
        '/app/Events/.*',
        '/app/Exceptions/.*',
        '/app/Functions/.*',
        '/app/Http/.*',
        '/app/Jobs/.*',
        '/app/Models',
        '/app/Notifications/.*',
        '/app/Observers/.*',
        '/app/Providers/.*',
        '/app/Rules/.*',
        '/app/Services/.*',
        '/app/Traits/.*',

        '/bootstrap/.*',
        '/config',
        '/database/.*',
        '/public/.*',
        '/resources/.*',
        '/routes/.*',

        '/storage',
        '/storage/app',
        '/storage/app/public',
        '/storage/framework/.*',
        '/storage/logs',

        '/tests/.*',
        '/vendor/.*',
    ];

    //base path
    $base_path = x_base_path();

    //check if path is protected
    foreach ($protected as $item) {
        //get item + base_path
        $item_base = sprintf('%s/%s', x_rtrim($base_path, '/'), x_ltrim($item, '/'));

        //test pattern
        $pattern = '|^' . preg_quote($item_base, '|') . '$|';

        //check if path matches pattern
        if (preg_match($pattern, $path)) {
            return $item;
        }
    }
}

//dir delete (void)
function x_dir_delete($dir)
{
    //ignore dir dies not exist
    if (!x_is_dir($dir)) {
        return;
    }

    //check if dir is protected
    if (($tmp = x_is_protected_dir($dir))) {
        throw new Exception(sprintf('Path directory is protected! (%s - %s)', $tmp, $dir));
    }

    //scan directory contents
    foreach (scandir($dir) as $basename) {
        //ignore (./..) basename
        if (in_array($basename, ['.', '..'])) {
            continue;
        }

        //get path
        $path = $dir . '/' . $basename;

        //if dir - recursively delete
        if (is_dir($path)) {
            x_dir_delete($path);
        }

        //if file - delete
        elseif (is_file($path)) {
            x_file_delete($path);
        }
    }

    //delete folder
    rmdir($dir);

    //check if deleted
    if (x_is_dir($dir)) {
        throw new Exception(sprintf('Failed to delete directory! (%s)', $dir));
    }

    //result
    return 1;
}

//dir is empty
function x_dir_empty($dir)
{
    if (!x_is_dir($dir)) {
        return 'error';
    }
    foreach (scandir($dir) as $basename) {
        if (in_array($basename, ['.', '..'])) {
            continue;
        }

        return false;
    }

    return true;
}

//dir contents count
function x_dir_count($dir)
{
    if (!x_is_dir($dir)) {
        return null;
    }
    $count = 0;
    foreach (scandir($dir) as $basename) {
        if (in_array($basename, ['.', '..'])) {
            continue;
        }
        $count += 1;
    }

    return $count;
}

//file hash
function x_file_hash($path, string $hash_algo='md5', bool $binary=false)
{
    //ignore invalid path
    if (!x_is_file($path)) {
        return;
    }

    //check hash_algo
    if (!in_array($hash_algo, hash_algos())) {
        throw new Exception(sprintf('Unsupported hash algorithm! (%s)', $hash_algo));
    }

    //result - file hash
    return hash_file($hash_algo, $path, $binary);
}

//dir hash
function x_dir_hash($dir, string $hash_algo='md5', bool $binary=false)
{
    //ignore missing dir
    if (!x_is_dir($dir)) {
        return;
    }

    //check hash_algo
    if (!in_array($hash_algo, hash_algos())) {
        throw new Exception(sprintf('Unsupported hash algorithm! (%s)', $hash_algo));
    }

    //hash buffer
    $buffer = [];

    //Directory instance
    $directory = dir($dir);

    //hash folder contents
    while (false !== ($basename = $directory->read())) {
        //ignore (./..) basename
        if (in_array($basename, ['.', '..'])) {
            continue;
        }

        //get path
        $path = $dir . '/' . $basename;

        //if dir - recursively delete
        if (is_dir($path)) {
            $buffer[] = x_dir_hash($path, $hash_algo, $binary);
        }

        //if file - delete
        elseif (is_file($path)) {
            $buffer[] = x_file_hash($path, $hash_algo, $binary);
        }
    }

    //close Directory instance
    $directory->close();

    //hash buffer to string
    $buffer = implode('', $buffer);

    //result - buffer string hash
    return hash($hash_algo, $buffer, $binary);
}

//path hash
function x_path_hash($path, string $hash_algo='md5', bool $binary=false)
{
    if (x_is_file($path)) {
        return x_file_hash($path, $hash_algo, $binary);
    } elseif (x_is_dir($path)) {
        return x_dir_hash($path, $hash_algo, $binary);
    }

    throw new Exception(sprintf('Invalid hash path! (%s)', $path));
}

//path remove base path
function x_path_no_base(string $path, string $base=null, bool $base_path=true)
{
    if (!($path = x_normalize_path($path))) {
        return null;
    }
    $base = ($base = x_normalize_path($base)) ? $base : ($base_path ? base_path() : '');

    return trim(str_replace($base, '', $path), '/');
}

//file exists
function x_file_exists($path, bool $throwable=false, &$norm_path=null, &$error=null)
{
    $error = null;
    $norm_path = $path = x_normalize_path($path);
    if ($path && file_exists($path)) {
        return true;
    }
    $error = "Path does not exist! ($path)";
    if ($throwable) {
        throw new Exception($error);
    }

    return false;
}

//is file
function x_is_file($path, bool $throwable=false, &$norm_path=null, &$error=null)
{
    if (x_file_exists($path, $throwable, $norm_path, $error) && is_file($norm_path)) {
        return $norm_path;
    }
    $error = "File does not exist! ($path)";
    if ($throwable) {
        throw new Exception($error);
    }

    return false;
}

//file delete (void)
function x_file_delete($path)
{
    //ignore if not existing
    if (!x_is_file($path)) {
        return;
    }

    //get path dir
    $dir = dirname($path);

    //check if dir is protected
    if (($tmp = x_is_protected_dir($dir))) {
        throw new Exception(sprintf('Path directory is protected! (%s - %s)', $tmp, $dir));
    }

    //delete file (unlink)
    unlink($path);

    //check if deleted
    if (x_is_file($path)) {
        throw new Exception(sprintf('Failed to delete (unlink) file! (%s)', $path));
    }
}

//is link
function x_is_link($path, bool $throwable=false, &$norm_path=null, &$error=null)
{
    if (x_file_exists($path, $throwable, $norm_path, $error) && is_link($norm_path)) {
        return $norm_path;
    }
    $error = "Symbolic link does not exist! ($path)";
    if ($throwable) {
        throw new Exception($error);
    }

    return false;
}

//link delete (void)
function x_link_delete($path)
{
    //ignore if not existing
    if (!x_is_link($path)) {
        return;
    }

    //get path dir
    $dir = dirname($path);

    //check if dir is protected
    if (($tmp = x_is_protected_dir($dir))) {
        throw new Exception(sprintf('Path directory is protected! (%s - %s)', $tmp, $dir));
    }

    //delete file (unlink)
    unlink($path);

    //check if deleted
    if (x_is_link($path)) {
        throw new Exception(sprintf('Failed to delete (unlink) symlink! (%s)', $path));
    }
}

//symlink
function x_symlink($target, $link)
{
    //check link
    if (!($tmp = x_normalize_path($link))) {
        throw new Exception(sprintf('Invalid symlink link! (%s)', $link));
    }

    //check target
    if (!file_exists($target)) {
        throw new Exception(sprintf('Symlink target does not exist! (%s)', $target));
    }

    //delete existing link
    x_link_delete($link = $tmp);

    //create symlink
    if (!symlink($target, $link)) {
        throw new Exception(sprintf('Symlink "%s" - "%s" failed!', $target, $link));
    }

    //success
    return true;
}

//move path
function x_move(string $source, string $dest, bool $overwrite=true)
{
    //check if source path exists
    if (!file_exists($source)) {
        throw new Exception(sprintf('Move source path does not exist! (%s)', $source));
    }

    //test destination path
    if (!($tmp = x_normalize_path($dest))) {
        throw new Exception(sprintf('Invalid move destination path! (%s)', $dest));
    }

    //normalized destination path
    $dest = $tmp;

    //get destination folder
    $dest_dir = dirname($dest);

    //create destination folder if not exist
    x_mkdir($dest_dir);

    //check if destination folder is writable
    if (!is_writable($dest_dir)) {
        throw new Exception(sprintf('Move destination folder is not writable! (%s)', $dest_dir));
    }

    //check if destination exists
    $dest_exists = is_file($source) && x_is_file($dest) || is_dir($source) && x_is_dir($dest);

    //test overwrite
    if ($dest_exists && !$overwrite) {
        throw new Exception(sprintf('Move destination path already exists! (%s - %s)', $source, $dest));
    }

    //overwrite delete destination
    if ($dest_exists && $overwrite) {
        if (is_file($source)) {
            x_file_delete($dest);
        } elseif (is_dir($source)) {
            x_dir_delete($dest);
        }
    }

    //rename path (move)
    if (!rename($source, $dest)) {
        throw new Exception(sprintf('Move path rename failed! (%s - %s)', $source, $dest));
    }

    //result - true if successful
    return true;
}

//copy path
function x_copy(string $source, string $dest, bool $overwrite=true)
{
    //check if source is symlink
    if (is_link($source)) {
        return x_symlink(readlink($source), $dest);
    }

    //check if source path exists
    if (!file_exists($source)) {
        throw new Exception(sprintf('Copy source path does not exist! (%s)', $source));
    }

    //test destination path
    if (!($tmp = x_normalize_path($dest))) {
        throw new Exception(sprintf('Invalid copy destination path! (%s)', $dest));
    }

    //normalized destination path
    $dest = $tmp;

    //copying file
    if (is_file($source)) {
        //create destination folder if not exist
        x_mkdir($dest_dir = dirname($dest));

        //check if destination folder is writable
        if (!is_writable($dest_dir)) {
            throw new Exception(sprintf('Copy destination folder is not writable! (%s)', $dest_dir));
        }

        //skip copy
        $skip_copy = false;

        //check existing
        $dest_exists = x_is_file($dest);

        //check if destination exists
        if ($dest_exists) {
            //get source path
            $source_hash = x_file_hash($source);

            //get dest path
            $dest_hash = x_file_hash($dest);

            //skip copy if hash match
            if ($source_hash == $dest_hash) {
                $skip_copy = true;
            }
        }

        //ignore skipped
        if ($skip_copy) {
            return true;
        }

        //test overwrite
        if ($dest_exists && !$overwrite) {
            throw new Exception(sprintf('Copy destination path already exists! (%s - %s)', $source, $dest));
        }

        //overwrite delete destination
        if ($dest_exists && $overwrite) {
            x_file_delete($dest);
        }

        //copy file
        if (!copy($source, $dest)) {
            throw new Exception(sprintf('Copy failed! (%s - %s)', $source, $dest));
        }
    }

    //copying folder
    elseif (is_dir($source)) {
        //create dest folder if not exist
        x_mkdir($dest);

        //get Directory instance
        $directory = dir($source);

        //recursively copy folder contents
        while (false !== ($basename = $directory->read())) {
            //ignore (./..) basename
            if (in_array($basename, ['.', '..'])) {
                continue;
            }

            //get source path
            $source_path = $source . '/' . $basename;

            //get dest path
            $dest_path = $dest . '/' . $basename;

            //skip copy
            $skip_copy = false;

            //check if destination exists
            if (file_exists($dest_path)) {
                //get source path
                $source_hash = x_path_hash($source_path);

                //get dest path
                $dest_hash = x_path_hash($dest_path);

                //skip copy if hash match
                if ($source_hash == $dest_hash) {
                    $skip_copy = true;
                }
            }

            //ignore skipped
            if ($skip_copy) {
                continue;
            }

            //recursively copy
            x_copy($source_path, $dest_path, $overwrite);
        }

        //close Directory instance
        $directory->close();
    }

    //result - true if successful
    return true;
}

/*
*	directory scanner
*	calls closure with each directory item path as only argument
*/
function x_scan_dir(
    string $dir,
    Closure $handler, //function($path){}
    bool $recurse=true,
    bool $include_files=true,
    bool $include_dirs=false,
    string $file_pattern=null,
    $break_on=false,
    int $scandir_sort=SCANDIR_SORT_ASCENDING, //SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE
    bool $file_pattern_dir=false
) {
    //validate directory
    if (!is_dir($dir)) {
        throw new Exception(sprintf('Argument $dir is not a directory! (%s)', $dir));
    }

    //validate closure
    if (!is_a($handler, 'Closure')) {
        throw new Exception('Argument $handler is not a Closure!');
    }

    //closure result
    $result = null;

    //scan directory
    $file_pattern = is_string($file_pattern) && strlen($file_pattern = trim($file_pattern)) ? $file_pattern : null;
    foreach (scandir($dir, $scandir_sort) as $basename) {
        //ignore (./..) basename
        if (in_array($basename, ['.', '..'])) {
            continue;
        }

        //get path
        $path = $dir . '/' . $basename;

        //if path is folder
        if (is_dir($path)) {

            //file_pattern_dir: ignore file pattern test fail
            if ($file_pattern_dir && $file_pattern && !preg_match($file_pattern, $path)) {
                continue;
            }

            //check inclusion
            if ($include_dirs) {
                //call $handler($path) (store result)
                $result = $handler($path);

                //break if $result matches $break_on
                if ($result === $break_on) {
                    break;
                }
            }

            //if $recurse is enabled
            if ($recurse) {
                //recurse folder (store last closure result)
                $result = x_scan_dir(
                    $path,
                    $handler,
                    $recurse,
                    $include_files,
                    $include_dirs,
                    $file_pattern,
                    $break_on,
                    $scandir_sort
                );

                //break if $result matches $break_on
                if ($result === $break_on) {
                    break;
                }
            }
        }

        //if path is file
        elseif (is_file($path)) {

            //ignore file pattern test fail
            if ($file_pattern && !preg_match($file_pattern, $path)) {
                continue;
            }

            //check inclusion
            if ($include_files) {

                //call $handler($path) (store result)
                $result = $handler($path);

                //break if $result matches $break_on
                if ($result === $break_on) {
                    break;
                }
            }
        }
    }

    //result - last $handler result
    return $result;
}

//directory scanner - get paths
function x_scan_dir_get(
    string $dir,
    bool $recurse=true,
    bool $include_files=true,
    bool $include_dirs=false,
    string $file_pattern=null,
    $break_on=false,
    int $scandir_sort=SCANDIR_SORT_ASCENDING //SCANDIR_SORT_DESCENDING, SCANDIR_SORT_NONE
) {
    //paths array
    $paths = [];

    //scan handler
    $handler = function ($path) use (&$paths) {
        $paths[] = $path; //add path to paths array
    };

    //scan directory
    x_scan_dir(
        $dir,
        $handler,
        $recurse,
        $include_files,
        $include_dirs,
        $file_pattern,
        $break_on,
        $scandir_sort
    );

    //result - paths array
    return $paths;
}

//file put (flags: FILE_APPEND | LOCK_EX | FILE_USE_INCLUDE_PATH)
function x_file_put(string $path, string $content, int $flags=0)
{
    $path = x_normalize_path($path, 1); //normalized path
    x_mkdir(dirname($path)); //create path folder if not exist

    //save contents
    if (($bytes_written = file_put_contents($path, $content, $flags)) === false) {
        throw new Exception(sprintf('Error saving file contents! (%s)', $path));
    }

    //return number of bytes written
    return $bytes_written;
}

//delete path (file/dir)
function x_path_delete(string $path)
{
    //normalize path (throws exception on error)
    $path = x_normalize_path($path, 1);

    //if path is file - delete file
    if (is_file($path)) {
        x_file_delete($path);
    }

    //if path is directory - delete directory recursively
    elseif (is_dir($path)) {
        x_dir_delete($path);
    }

    //check if path was deleted
    if (file_exists($path)) {
        throw new Exception(sprintf('Error deleting path! (%s)', $path));
    }

    //return true on success
    return true;
}

//delete folder contents
function x_dir_delete_contents(string $dir)
{
    //check if dir exists
    if (!x_is_dir($dir)) {
        return;
    }

    //scan handler - delete path
    $handler = function ($path) {
        x_path_delete($path);
    };

    //scan folder contents
    x_scan_dir(
        $dir,
        $handler,
        $recurse=false,
        $include_files=true,
        $include_dirs=true,
        $file_pattern=null,
        $break_on=false,
        $scandir_sort=SCANDIR_SORT_ASCENDING
    );

    //result
    return 1;
}

//delete multiple path items
function x_delete(...$items)
{
    foreach ($items as $item) {
        //if item is list - recursively delete list items
        if (x_is_list($item, 0)) {
            x_delete(...$item);
        }

        //delete if item is an existing path
        if (is_string($item) && file_exists($item)) {
            x_path_delete($item);
        }
    }
}

//read file line by line
function x_file_readline(string $path, $callback, &$count_lines=null)
{
    $count_lines = null;
    if (!x_is_file($path)) {
        throw new Exception("File does not exist! ($path)");
    }
    if (!is_callable($callback)) {
        throw new Exception('File read line callback is not callable!');
    }
    $count_lines = count(file($path));
    $fo = fopen($path, 'r+');
    while (($line = stream_get_line($fo, 1024 * 1024, "\n")) !== false) {
        if ($callback($line) === false) {
            break;
        }
    }
}

//load array from file (includes php file - must return array)
function x_file_get_array(string $path)
{
    //check path
    if (!x_is_file($path)) {
        throw new Exception(sprintf('Array file does not exist! (%s)', $path));
    }

    //set result - import path
    ob_start();
    $result = @include $path;
    ob_end_clean();

    //check result
    if (!is_array($result)) {
        throw new Exception(sprintf('Array file result is invalid! (%s)', $path));
    }

    //return result
    return $result;
}

//get file mime type
function x_file_mime_type(string $file)
{
    $type = '';
    if (!($file = x_normalize_path($file))) {
        return;
    }
    if (x_is_url($file) && ($headers = get_headers($file, 1)) && is_array($headers) && isset($headers['Content-Type']) && ($type = trim($headers['Content-Type']))) {
        return $type;
    }
    if (is_file($file)) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file);
            finfo_close($finfo);
        } else {
            $type = mime_content_type($file);
        }
        if (!$type || in_array($type, ['application/octet-stream', 'text/plain'])) {
            $secondOpinion = exec('file -b --mime-type ' . escapeshellarg($file), $foo, $returnCode);
            if ($returnCode === 0 && $secondOpinion) {
                $type = $secondOpinion;
            }
        }
        if (!$type || in_array($type, ['application/octet-stream', 'text/plain'])) {
            $type = mime_content_type($file);
            $exifImageType = exif_imagetype($file);
            if ($exifImageType !== false) {
                $type = image_type_to_mime_type($exifImageType);
            }
        }
    }
    if (!$type) {
        $file_mimes = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadshee',
        ];
        $type = 'application/octet-stream';
        if (count($tmp = explode('.', trim($file, '.'))) > 1) {
            $ext = strtolower(array_reverse($tmp)[0]);
            if (array_key_exists($ext, $file_mimes)) {
                $type = $file_mimes[$ext];
            }
        }
    }

    return $type;
}

//get file ext
function x_file_ext(string $path)
{
    x_file_name($path, $ext, $basename);

    return $ext;
}

//get file name - no ext
function x_file_name($path, &$ext=null, &$basename=null)
{
    $ext = null;
    $basename = null;
    if (!($path = x_normalize_path($path))) {
        return null;
    }
    $basename = basename($path);
    if (!preg_match('/\.[a-z0-9]{2,3}$/i', $basename)) {
        return $basename;
    }
    if (($c = count($arr = explode($s = '.', $basename))) < 2) {
        return $basename;
    }
    $ext = trim(end($arr));
    $arr = array_slice($arr, 0, $c - 1);

    return trim(implode($s, $arr));
}
