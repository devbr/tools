<?php
/**
 * Lib\Cli\Main
 * PHP version 7
 *
 * @category  Tools
 * @package   Cli
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.2
 * @link      http://paulorocha.tk/devbr
 */

namespace Lib\Cli;

use Lib;

/**
 * Lib\Cli\Main Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Main
{

    private $timer = 0;

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
        $devbr = _APP.'Composer/devbr/';
        $dir = scandir($devbr);
        $o = '';

        foreach ($dir as $k) {
            if ($k == '.' || $k == '..') {
                continue;
            }
            if (is_file($devbr.$k.'/install.php')) {
                $o .= include $devbr.$k.'/install.php';
            }
        }
        return $o;
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
    static function checkAndOrCreateDir($dir, $create = false, $perm = '0777')
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
     * return a help information text
     *
     * @return string a help text...
     */
    static function help()
    {
        return '

      Usage: php index.php [command:type] [options]

      key:generate              Generate new keys
      key:list                  List all installed Cyphers

      make:controller <name>    Create a controller with <name>
      make:model <name>         Create a model with <name>
      make:html <name>          Create a html file with <name>

      optimize:scan [save name] Scan CSS&JS source files 
                                Optional indicates "save" and a 
                                "name" for save in config file the scan

      optimize:css              Optimize CSS configurated files
      optimize:js               Optimize JS configurated files
      optimize:all              Optimize ALL configurated files

      -h or ?                   Show this help
      ';
    }
}
