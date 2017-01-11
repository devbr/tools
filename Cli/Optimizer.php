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
    private $arg    = null;


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
    }

    /**
     * Run command
     *
     * @return void return from MAIN CLTool
     */
    function run()
    {
        switch ($this->cmd) {
            case 'scan':
                $this->cmdScan();
                break;

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
     * Command SCAN
     *
     * @return array File list in source CSS/JS
     */
    private function cmdScan()
    {
        $css = $this->config->get('css');
        $a['css'] = $this->searchFile($css->path.$css->source);

        $js = $this->config->get('js');
        $a['js'] = $this->searchFile($js->path.$js->source);

        //Search for "SAVE"
        if (isset($this->arg[0]) && $this->arg[0] == 'save') {
            $name = 'default';

            //Search for "NAME"
            if (isset($this->arg[1])
                && is_string($this->arg[1])
                && $this->arg[1] != '') {
                $name = $this->arg[1];
            }

            $pj = $this->config->get('cssPack');
            $pj->$name = $a['css'];
            $this->config->set('cssPack', $pj);

            $pc = $this->config->get('jsPack');
            $pc->$name = $a['js'];
            $this->config->set('jsPack', $pc);

            $this->config->save();

            echo "\n -- Config file saved as \"$name\"!";
        }
        echo "\n".print_r($a, true);
    }

    private function cmdCss()
    {
        echo "\n - CSS -\n";
    }

    private function cmdJs()
    {
        echo "\n - JS -\n";
    }

    private function cmdAll()
    {
        $this->cmdCss();
        $this->cmdJs();
    }

    // ----------------------------------------------------------------------

    private function compressAndSaveCss()
    {
        //echo "\n -- ".$this->config->get('sourcePath');
        echo "\n -- ".print_r($this->searchCssSources('home', true), true);
        echo "\n -- ".print_r($this->searchJsSources(null, true), true);
        exit("\n\n  -- ok");
    }


    private function compressAndSaveJs()
    {
        echo "\n -- ".$this->config->get('sourcePath');
        exit("\n\n  -- ok");
    }


    private function searchCssSources(
        string $pack = null,
        bool $save = false
    ) {
    
        $pack = $pack == null ? 'default' : $pack;
        $css = $this->config->get('css');
        $in = $this->config->get('cssPack'); //exit(print_r($this->config));

        $in->$pack = $this->searchFile($css->path.$css->source);

        if ($save) {
            $this->config->set('cssPack', $in);
            $this->config->save();
        }

        return $in;
    }

    private function searchFile($dir)
    {
        $dir = scandir($dir, 1);
        $o = [];

        foreach ($dir as $n => $f) {
            if ($f == '.' || $f == '..') {
                continue;
            }
            $o[$n] = $f;
        }

        return $o;
    }

    private function searchJsSources(
        string $pack = null,
        bool $save = false
    ) {
    
        $pack = $pack == null ? 'default' : $pack;
        $js = $this->config->get('js');
        $in = $this->config->get('jsPack');

        $in->$pack = $this->searchFile($js->path.$js->source);

        if ($save) {
            $this->config->set('jsPack', $in);
            $this->config->save();
        }

        return $in;
    }
}
