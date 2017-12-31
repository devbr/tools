<?php
/**
 * Devbr\Cli\Main
 * PHP version 7
 *
 * @category  Tools
 * @package   Cli
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.2
 * @link      http://dbrasil.tk/devbr
 */

namespace Devbr\Cli;

/**
 * Devbr\Cli\Main Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://dbrasil.tk/devbr
 */
class Main
{

	private $timer = 0;
	private static $configDir = '';
	private static $vendorDir = '';
	private static $baseDir = '';


	/**
	 * Constructor
	 *
	 * @param array $argv Command line array
	 */
	function __construct($argv)
	{
		echo "  Command Line Tool!\n";
		if (php_sapi_name() !== 'cli') {
            exit('It\'s no cli!');
		}

		//Constants:
		$this->timer = microtime(true);

		//Configurations
		$this->setup();

		//Command line settings...
		echo $this->request($argv);

		exit("\n  Finished in ".number_format((microtime(true)-$this->timer)*1000, 3)." ms.\n");
	}

	//CORE Request
	function request($rqst)
	{
		array_shift($rqst);
		$ax = $rqst;
		foreach ($rqst as $a) {
			array_shift($ax);
			if (strpos($a, '-h') !== false || strpos($a, '?') !== false) {
				return self::help();
			}
			if (strpos($a, 'optimize:') !== false) {
				return (new Optimizer(substr($a, 9), $ax))->run();
			}
			if (strpos($a, 'install') !== false) {
				return $this->cmdInstall(substr($a, 7), $ax);
			}
			if (strpos($a, 'update') !== false) {
				return $this->cmdUpdate(substr($a, 6), $ax);
			}
			if (strpos($a, 'key:') !== false) {
				return (new Key(substr($a, 4), $ax))->run();
			}
			if (strpos($a, 'make:') !== false) {
				return (new Make(substr($a, 5), $ax))->run();
				//return $this->cmdMake(substr($a, 5), $ax);
			}

			//Plugins
			if (strpos($a, 'table:') !== false) {
				return (new Plugin\Table(substr($a, 6), $ax))->run();
			}
		}
		//or show help...
		return self::help();
	}

	/**
	 * It's same as cmdUpdate
	 * @param string $v   segment of command
	 * @param array $arg all others command line argumments
	 *
	 * @return string Display user data
	 */
	function cmdInstall($v, $arg)
	{
		return $this->cmdUpdate($v, $arg);
	}

	/**
	 * Update command
	 * @param string $v   segment of command
	 * @param array $arg all others command line argumments
	 *
	 * @return string Display user data
	 */

	function cmdUpdate($v, $arg)
	{
		$report = [];
		$vendors = scandir(self::vendorDir); //.php/Composer

		foreach($vendors as $vendor){
            		$vendorPath = self::vendorDir.'/'.$vendor;

            		if ($vendor == '.' || $vendor == '..' || !is_dir($vendorPath)) {
                		continue;
            		}

            		$vendorFiles = scandir($vendorPath); //.php/Composer/devbr

			//varre todos os componentes "DEVBR"
			foreach ($vendorFiles as $componente) {
                		$componentePath = "$vendorPath/$componente"; //.php/Composer/devbr/html

				if ($componente == '.' || $componente == '..' || !is_dir($componentePath)) {
					continue;
				}

				if (is_dir("$componentePath/Config")) {
					//Coping all files (and directorys) in /Config
					$copy = static::copyDirectoryContents("$componentePath/Config", self::configDir, false, self::configDir);
                    			$report["$vendor/$componente"] = $copy;

					//Return to application installer
					echo "\n - Install $vendor/$componente:";

                    			//Copied
                    			$copied = count($copy['copied']);
                    			if ($copied > 0) {
                        			echo " $copied file(s) copied.";
                    			}
                    
                    			//Permissions 
                    			if (isset($copy['error']['permission'])) {
                        			echo " I can't allow to copy one or more files.";
                    			}

                    			//Files exists
                    			if (isset($copy['error']['overwrite'])) {
                        			echo ' '.($copied > 0 ? 'Some f':'F').'iles already existed.';                                
                    			}
				}
			}
            echo "\n";
		}

		//Saving a log file
		file_put_contents(self::configDir.'/install.log.json', json_encode($report, JSON_PRETTY_PRINT));
	}


	/**
	 * 	
	 * @return [type] [description]
	 */
	static function getConfigDir()
	{
		return static::configDir
	}


	/**
	 * 	
	 * @return [type] [description]
	 */
	static function getVendorDir()
	{
		return static::vendorDir
	}


	/**
	 * [getBaseDir description]
	 * @return [type] [description]
	 */
	static function getBaseDir()
	{
		return static::baseDir
	}



	/**
	 * [setup description]
	 * @return [type] [description]
	 */
	private function setup()
	{
		$composer_psr4 = dirname(dirname(dirname(__DIR__))).'/composer/autoload_psr4.php';
		if (!file_exists($composer_psr4)) {
			exit("\nI can't find Composer data!");
		}

		$composer = require_once $composer_psr4;
		if (!isset($composer['Config\\'][0])) {
			exit("\nI can't find Composer data!");
		}

		//APP_CONFIGPATH defined in "index.php" or in bootstrape
    	if(!defined('_CONFIGPATH')){
      		define('_CONFIGPATH', isset($composer[''][0]) //Composer fallBack
		       ? $composer[''][0].'/Config' 
		       : dirname($vendorDir).'/Config');
    	}
    	static::configDir = _CONFIGPATH;
    	static::vendorDir = $vendorDir;
    	static::baseDir = $baseDir;
	}


	// Checa um diretório e cria se não existe - retorna false se não conseguir ou não existir
	/**
	 * Check or create a directory
	 * @param  string  $dir    path of the directory
	 * @param  boolean $create False/true for create
	 * @param  string  $perm   indiucates a permission - default 0777
	 *
	 * @return bool          status of directory (exists/created = false or true)
	 */
	static function checkAndOrCreateDir($dir, $create = false, $perm = 0777)
	{
		if (is_dir($dir) && is_writable($dir)) {
			return true;
		} elseif ($create === false) {
			return false;
		}

		@mkdir($dir, $perm, true);
		@chmod($dir, $perm);

		if (!is_writable($dir)) {
			return false;
		}
		
		return true;
	}

	/**
	 * Copy entire content of the $dir[ectory]
	 * @param  string $dir    Origin
	 * @param  string $target Destination
	 * @return bool         True/false success
	 */
	static function copyDirectoryContents($dir, $target, $overwrite = true, $base = '')
	{
		$dir = rtrim($dir, "\\/ ").'/';
		$target = rtrim($target, "\\/ ").'/';
		$report = ['error'=>[],'copied'=>[]];

		if (!static::checkAndOrCreateDir($target, true, 0777)) {
			$report['error']['permission'] = $taget;
			return $report;
		}

		foreach (scandir($dir) as $file) {
			if ($file == '.' || $file == '..') {
				continue;
			}

			if (is_dir($dir.$file)) {
				if (!static::checkAndOrCreateDir($target.$file, true, 0777)) {
					$report['error']['permission'] = $taget.$file;
					return $report;
				} else {
					$copy = static::copyDirectoryContents($dir.$file, $target.$file, $overwrite, $base);
					$report = array_merge_recursive($report, $copy);
				}
			} elseif (is_file($dir.$file)) {
				if ($overwrite === false && file_exists($target.$file)) {
					$report['error']['overwrite'][] = str_replace($base.'/', '', $target.$file);
					continue;
				}
				if (!copy($dir.$file, $target.$file)) {
					$report['error']['permission'] = $target.$file;
					return $report;
				} else {
					$report['copied'][] = str_replace($base.'/', '', $target.$file);
				}
			}
		}
		return $report;
	}
	
	/**
	 * Remove Directory 
	 * 
	 * @param  string $src pack of directory to remove
	 * 
	 * @return void        void
	 */
	static private function removeDirectory($src) 
	{
		$dir = opendir($src);
		while(false !== ( $file = readdir($dir)) ) {
			if ($file != '.' && $file != '..') {
				$full = $src . '/' . $file;

				if ( is_dir($full) ) {
					self::removeDirectory($full);
				} else {
					unlink($full);
    			}
			}
		}
		closedir($dir);
		rmdir($src);
	}

	/**
	 * return a help information text
	 *
	 * @return string a help text...
	 */
	static function help()
	{
		return '
  Usage: php index.php [command:type] [options]

  key:generate                        Generate new keys
  key:list                            List all installed Cyphers

  make:controller <namespace/name>    Create a controller
  make:model <namespace/name>         Create a model
  make:html <namespace/name>          Create a html

  optimize:scan [save name]           Scan CSS&JS source files 
                                      Optional indicates "save" and a "name" 
                                      for save in config file the scan

  optimize:css                        Optimize CSS configurated files
  optimize:js                         Optimize JS configurated files
  optimize:all                        Optimize ALL configurated files

  -h or ?                             Show this help
        ';
	}
}
