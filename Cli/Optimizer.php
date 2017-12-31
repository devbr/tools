<?php
/**
 * Devbr\Cli\Optimizer
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

use Config\Devbr\Cli;

/**
 * Devbr\Cli\Optimizer Class
 *
 * @category  Tools
 * @package   Cli
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://dbrasil.tk/devbr
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

            case 'jss':
                $this->cmdJss();
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
        $this->cmdJss();
    }

    /**
     * Jointer command
     * @return void Configurated files are saved
     */
    private function cmdJss()
    {
        if ($jss = $this->config->get('jss')) {
            echo "\n - Compiled JS+CSS -\n";

            if (!$this->subCmd) {
                foreach ($jss as $k => $v) {
                    echo "\n - $k:\n";
                    $this->jssCompiler($v);
                }
                return;
            }

            if (isset($jss->{$this->subCmd})) {
                echo "\n - ".$this->subCmd."\n";
                
                $this->jssCompiler($jss->{$this->subCmd});
                return;
            } else {
                echo "\n Err: Not found!\n";
                return;
            }
        }
    }

    /**
     * Jointer JS & CSS
     * @param  object $config configuration data
     * @return void         save file
     */
    private function jssCompiler($config)
    {
        if (!isset($config->filename)) {
            echo "ERROR!!\n";
            return false;
        }

        $content = "var JSS = Array();\n";
        $width = 4096;

        //CSS
        if (isset($config->css)) {
            $tmp = $this->minify(false,
                                  $config->css,
                                  $this->yuicompressor,
                                  $this->config->baseDir);

            $tmp = explode("\n", str_replace("'", "\'", $tmp));

            foreach ($tmp as $k => $v) {
                $v = trim($v);
                if ($v == '') {
                    continue;
                }

                if (substr($v, -1) == '\\') {
                    $v .= '\\';
                }

                $content .= 'JSS['.$k.'] = \''.$v."';\n";
            }
        }

        //Função para montagem do STYLE
        $content .= 'for(var i in JSS){var etmp=document.createElement("STYLE");etmp.type="text/css";etmp.innerHTML=JSS[i];document.head.appendChild(etmp);}document.getElementsByClassName("container")[0].style.display="block";document.getElementById("loader").style.display="none";'."\n";

        //Javascripts
        if (isset($config->js)) {
            $content .= $this->minify(false,
                              $config->js,
                              $this->yuicompressor,
                              $this->config->baseDir);
        }

        file_put_contents($config->filename, $content);
        echo "\n\n Saving: ".$config->filename."\n";
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
    private function minify($filename = false, $files, $yuicompressor, $baseDir, $width = null)
    {
        $content = '';
        if ($width === null) {
            $extra = '';
        } else {
            $extra = '--line-break '.intval($width);
        }

        foreach ($files as $file) {
            if (file_exists($baseDir.$file)) {
                $result = exec("java -jar $yuicompressor $extra $baseDir$file ");
                $content .= (defined('_MODE') && _MODE == 1 ? "/* $file */" : '')."$result\n";
                echo "\n    Add: $file";
            } else {
                echo "\n    Err: $file - not found.";
            }
        }
            //return minyfied data
        if ($filename === false) {
            return $content;
        }

            echo "\n Saving: $filename\n";
            file_put_contents($baseDir.$filename, $content);
            echo "\n Minified ..\n";
    }
}
