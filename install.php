<?php

if (php_sapi_name() !== 'cli' || !defined('_CONFIG')) {
    exit("\n\tI can not run out of system!\n");
}

$thisConfig = __DIR__.'/Config/';

if (!is_dir($thisConfig)) {
    return;
}

$namespace = @json_decode(file_get_contents(__DIR__.'/composer.json'))->name;
/* OPTIONAL
 * load composer.json and get the "name" of pack 
 * $appConfig = _CONFIG.$namespace;
 */
 
$appConfig = _CONFIG;

//Coping all files (and directorys) in /Config
$copy = \Lib\Cli\Main::copyDirectoryContents($thisConfig, $appConfig);

//Return to application installer
return "\n---".($copy === true ? " $namespace instaled!" : $copy);
