<?php
/**
 * Lib\Cli\Make
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
 * Lib\Cli\Make Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Make
{
    private $cmd            = null;
    private $arg            = null;
    private $configTemplate = null;
    private $phpRoot        = null;

    /**
     * Constructor
     * @param string $cmd command
     * @param array $arg others command line args
     */
    function __construct(
        $cmd = null,
        $arg = null
    ) {
    
        $this->cmd = strtolower($cmd);
        $this->arg = $arg;
        $this->configTemplate = (defined('_CONFIG') ? _CONFIG : __DIR__.'/').'Template/';
        $this->phpRoot = defined('_APP') ? _APP : __DIR__.'/';
    }

    /**
     * Run command
     *
     * @return void return from MAIN CLTool
     */
    function run()
    {
        if (isset($this->arg[0])) {
            $this->arg[0] = str_replace('\\', '/', $this->arg[0]);
        } else {
            return "\n\n  ERROR: indique o NOME do arquivo!\n";
        }

        $type = strtolower(trim($this->cmd));

        if ($type != 'controller' && $type != 'model' && $type != 'html') {
            echo "\n\n  Command \"make:".$this->cmd."\" not exists!";
            exit(Main::help());
        }

        return $this->createFile($this->arg[0], $type);
    }


    /**
     * Create Resource by Template
     * @param  string $name path to create file
     * @param  string $type selector of the template
     *
     * @return string       Display data
     */
    private function createFile(
        $name,
        $type = 'controller'
    ) {
    
        //$name = $type == 'html'?strtolower($name):$name;
        $path = $this->phpRoot.ucfirst($type).'/';
        $ext = $type == 'html'?'.html':'.php';

        $fileName = $this->phpRoot.$name.$ext;

        if (file_exists($fileName)) {
            return "\n\n  WARNNING: this file already exists!\n  ".$fileName."\n\n";
        }


        if (!Main::checkAndOrCreateDir(dirname($fileName), true)) {
            return "\n\n  WARNNING: access denied in directory '".dirname($fileName)."'\n\n";
        }

        //get template
        $file = file_get_contents($this->configTemplate.$type.'.tpl');

        //replace %namespace% and %name%
        $file = str_replace('%name%', ucfirst(basename($name)), $file);
        $namespace = '';
        foreach (explode('/', dirname($name)) as $namespc) {
            if ($namespc == '.') {
                break;
            }
            $namespace .= ucfirst($namespc).'\\';
        }
        $file = str_replace('%namespace%', trim($namespace, '\\'), $file);

        //saving the file
        $ok = file_put_contents($fileName, $file);

        if ($ok) {
            return "\n\n  Arquivo '".$fileName."' criado com sucesso!\n\n";
        } else {
            return "\n\n  Não foi possível criar '".$name."'!\n\n";
        }
    }
}
