<?php

namespace App\Services;

use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ZipService
{
    //zip
    public function zip(string $source, string $dest)
    {
        if (!extension_loaded('zip')) {
            throw new Exception('Zip extension not loaded!');
        }
        if (!file_exists($source)) {
            throw new Exception(sprintf('Zip source does not exist! (%s)', $source));
        }

        //zip open
        $zip = new ZipArchive;
        if (!$zip->open($dest, ZIPARCHIVE::CREATE)) {
            throw new Exception("Error opening zip '$dest'!");
        }

        //zip
        $result = null;

        //source path
        $source = str_replace('\\', '/', realpath($source));

        //source is dir
        if (is_dir($source) === true) {
            //get files iterator
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::SELF_FIRST
            );

            //iterate files
            foreach ($files as $file) {
                //loop file
                $file = str_replace('\\', '/', $file);

                //ignore '.' and '..' folders
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                //get file
                $file = realpath($file);

                //file is dir
                if (is_dir($file) === true) {
                    $result = $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $result = $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }

        //source is file
        elseif (is_file($source) === true) {
            $result = $zip->addFromString(basename($source), file_get_contents($source));
        }

        //zip close
        $zip->close();

        //result
        return $result;
    }

    //extract
    public function extract(string $zip_file, string $dest=null, string $path=null)
    {
        //zip open
        $zip = new ZipArchive;
        if (!$zip->open($zip_file)) {
            throw new Exception("Error opening zip '$zip_file'!");
        }

        //zip extract
        $result = null;
        if ($path = x_normalize_path($path)) {
            if ($zip->locateName($path)) {
                $name = basename($dest);
                $zip->renameName($path_name = basename($path), $name);
                $result = $zip->extractTo(dirname($dest), $name);
                $zip->renameName($name, $path_name);
            } else {
                x_dump("Error: Failed to locate zip path '$path' in '$zip_file'!");
            }
        } else {
            if (!($dest = x_normalize_path($dest))) {
                $dest = dirname($zip_file);
            }
            $result = $zip->extractTo($dest);
        }

        //zip close
        $zip->close();

        //result
        return $result;
    }
}
