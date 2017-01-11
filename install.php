<?php
if (php_sapi_name() !== 'cli') {
    exit('It\'s no cli!');
}

//Configurations - you can change...
$name = 'Tools';
$file = 'Lib/Cli/Optimizer.php';
$configPath = defined('_CONFIG') ? _CONFIG : dirname(dirname(dirname(__DIR__))).'/Config/';

//Checkin
if (is_file($configPath.$file)) {
    return "\n  - $name configuration file already exists!";
}
if (!is_dir($configPath)) {
    return "\n\n  - Configuration file for $name not instaled!\n\n";
}

//Gravando o arquivo de configuração no CONFIG da aplicação
if (!checkAndOrCreateDir(dirname($configPath.$file), true)) {
    return "\n  - Without permission to create or write \"$configPath$file\"!\n\n";
}
file_put_contents($configPath.$file,
    file_get_contents(__DIR__.'/Cli/config.php'));

//Return to application installer
return "\n  - $name instaled!";



/**
 * Check or create a directory
 * @param  string  $dir    path of the directory
 * @param  boolean $create False/true for create
 * @param  string  $perm   indiucates a permission - default 0777
 *
 * @return bool          status of directory (exists/created = false or true)
 */
function checkAndOrCreateDir($dir, $create = false, $perm = '0777')
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
