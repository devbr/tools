<?php

if (php_sapi_name() !== 'cli') {
    ret('It only runs in CLI mode!');
}

$composer_psr4 = dirname(dirname(__DIR__)).'/composer/autoload_psr4.php';
if (!file_exists($composer_psr4)) {
    ret("I can't find Composer data!");
}

$composer = require_once $composer_psr4;
if (!isset($composer['Config\\'][0])) {
    ret("I can't find Composer data!");
}

//load composer.json and get the "name" of pack 
$name = @json_decode(file_get_contents(__DIR__.'/composer.json'))->name;

$namespace = explode('/', $name);

$appConfig = $composer['Config\\'][0].'/';

foreach ($namespace as $value) {
    $appConfig .= ucfirst($value).'/';
}

$thisConfig = __DIR__.'/Config/';

if (!is_dir($thisConfig)) {
    ret("I can't find 'Config' directory in this pack!");
}

if (is_dir($appConfig)) {
    ret("Configuration already exists - ignored.");
}

//Coping all files (and directorys) in /Config
$copy = copyDirectoryContents($thisConfig, $appConfig);

//Return to application installer
ret($copy === true ? " $name instaled!" : $copy);

// THE END ...



/**
 * Check or create a directory
 *
 * @param  string  $dir    path of the directory
 * @param  boolean $create False/true for create
 * @param  string  $perm   indiucates a permission - default 0777
 *
 * @return bool          status of directory (exists/created = false or true)
 */

if (!function_exists('checkAndOrCreateDir')) {
    function checkAndOrCreateDir($dir, $create = false, $perm = 0777)
    {
        if (is_dir($dir) && is_writable($dir)) {
            return true;
        } elseif ($create === false) {
            return false;
        }

            @mkdir($dir, $perm, true);
            @chmod($dir, $perm);

        if (is_writable($dir)) {
            return true;
        }
            return false;
    }

/**
 * Copy entire content of the $dir[ectory]
 *
 * @param  string $dir    Origin
 * @param  string $target Destination
 *
 * @return bool         True/false success
 */
    function copyDirectoryContents($dir, $target)
    {
        $dir = rtrim($dir, "\\/ ").'/';
        $target = rtrim($target, "\\/ ").'/';

        if (!checkAndOrCreateDir($target, true, 0777)) {
            return "ERROR: can't create directory '$taget'!";
        }

        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            if (is_dir($dir.$file)) {
                if (!checkAndOrCreateDir($target.$file, true, 0777)) {
                    return "ERROR: can't create directory '$taget$file'!";
                } else {
                    $copy = copyDirectoryContents($dir.$file, $target.$file);
                    if ($copy !== true) {
                        return $copy;
                    }
                }
            } elseif (is_file($dir.$file)) {
                if (!copy($dir.$file, $target.$file)) {
                    echo "\n ERROR: can't copy '$target$file'!";
                }
            }
        }
        return true;
    }

/**
 * Return with message
 *
 * @param string $msg message
 *
 * @return void print and exit
 */
    function ret($msg)
    {
        echo "\n - $msg";
        return true;
    }
}
