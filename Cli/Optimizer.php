<?php
/**
 * Lib\Cli\Optimizer
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

use Config\Lib\Cli;

/**
 * Lib\Cli\Optimizer Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Optimizer
{
    private $config = null;
    private $cmd    = null;
    private $subCmd = false;
    private $arg    = null;
    private $yuicompressor = null;


    /**
     * Constructor
     * @param string $cmd command
     * @param array $arg others command line args
     */
    function __construct(
        $cmd = null,
        $arg = null
    ) {
    
        $this->config = Cli\Optimizer::this();
        $this->cmd = strtolower($cmd);
        $this->arg = $arg;
        $this->yuicompressor = __DIR__.'/yc.jar';
    }

    /**
     * Run command
     *
     * @return void return from MAIN CLTool
     */
    function run()
    {
        if (strpos($this->cmd, ':') !== false) {
            $tmp = explode(':', $this->cmd);
            $this->cmd = $tmp[0];
            $this->subCmd = $tmp[1];
        } else {
            $this->subCmd = false;
        }

            //Select
        switch ($this->cmd) {
            case 'css':
                $this->cmdCss();
                break;

            case 'js':
                $this->cmdJs();
                break;
            
            case 'all':
                $this->cmdAll();
                break;

            default:
                echo "\n\n  Command \"optimize:".$this->cmd."\" not exists!";
                exit(Main::help());
            break;
        }
            return;
    }


    /**
     * [cmdCss description]
     * @return [type] [description]
     */
    private function cmdCss()
    {
        echo "\n - CSS -\n";

        if ($css = $this->config->get('css')) {
            if (!$this->subCmd) {
                foreach ($css as $k => $v) {
                    echo "\n - $k:\n";
                    $this->minify($v->filename, $v->add, $this->yuicompressor, $this->config->baseDir);
                }
                return;
            }

            if (isset($css->{$this->subCmd})) {
                echo "\n - ".$this->subCmd."\n";
                $this->minify($css->{$this->subCmd}->filename,
                  $css->{$this->subCmd}->add,
                  $this->yuicompressor,
                  $this->config->baseDir);
            } else {
                echo "\n Err: Not found!\n";
                return;
            }
        }
    }

    /**
     * [cmdJs description]
     * @return [type] [description]
     */
    private function cmdJs()
    {
        echo "\n - JS -\n";
        if ($js = $this->config->get('js')) {
            if (!$this->subCmd) {
                foreach ($js as $k => $v) {
                    echo "\n - $k:\n";
                    $this->minify($v->filename, $v->add, $this->yuicompressor, $this->config->baseDir);
                }
                return;
            }

            if (isset($js->{$this->subCmd})) {
                echo "\n - ".$this->subCmd."\n";
                $this->minify($js->{$this->subCmd}->filename,
                  $js->{$this->subCmd}->add,
                  $this->yuicompressor,
                  $this->config->baseDir);
            } else {
                echo "\n Err: Not found!\n";
                return;
            }
        }
    }

    /**
     * [cmdAll description]
     * @return [type] [description]
     */
    private function cmdAll()
    {
        $this->cmdCss();
        $this->cmdJs();
    }

    // ----------------------------------------------------------------------

    /**
     * Minifier
     * @param  string $filename      Path to save
     * @param  array  $files         Array of files
     * @param  string $yuicompressor Path of java YuiComporessor
     * @param  string $baseDir       Path of base directory
     * @return void                void
     */
    private function minify($filename, $files, $yuicompressor, $baseDir)
    {
        $content = '';

        foreach ($files as $file) {
            if (file_exists($baseDir.$file)) {
                $result = exec("java -jar $yuicompressor $baseDir$file");
                $content .= "/* $file */$result\n";
                echo "\n    Add: $file";
            } else {
                echo "\n    Err: $file - not found.";
            }
        }
        echo "\n Saving: $filename\n";

        file_put_contents($baseDir.$filename, $content);
        echo "\n Minified ..\n";
    }
}
