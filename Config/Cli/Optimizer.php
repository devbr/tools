<?php
/**
 * Config\Devbr\Cli
 * PHP version 7
 *
 * @category  Tools
 * @package   Config
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.2
 * @link      http://dbrasil.tk/devbr
 */

namespace Config\Cli;

/**
 * Optimizer Config Class
 *
 * @category Tools
 * @package  Config
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://dbrasil.tk/devbr
 */
class Optimizer
{
    public $baseDir =  null;
    private $css =      null;
    private $js =       null;
    private $jss =      null;

    private static $configFile = false;
    private static $node = false;

    /**
     * Constructor
     * @param string|null $file path of config json file
     */
    function __construct(string $file = null)
    {
        if ($file == null) {
            $file = static::$configFile;
        }
        if ($file == false) {
            $file = static::$configFile = substr_replace(__FILE__, '.json', '-4');
            $this->baseDir = \Config\App::Web();
        }

        if (!file_exists($file)) {
            @$this->js->name->filename = "js/main.min.js";
            @$this->js->name->add = ["js/source/main.js"];
            
            @$this->css->name->filename = "css/main.min.css";
            @$this->css->name->add = ["css/source/main.css"];
            
            $this->save();
        }
        $this->load();
    }

    /**
     * Get Instance - static this()
     * @param  string|null $file path of config json file
     *
     * @return object            Self class object
     */
    static function this(string $file = null)
    {
        if (!static::$node) {
            static::$node = new self($file);
        }
        return static::$node;
    }

    /**
     * Set params
     * @param string $item  item name
     * @param mixed $value mixed value inserted in
     *
     * @return object this
     */
    function set(string $item, $value)
    {
        $this->$item = $value;
        return $this;
    }

    /**
     * Get params
     * @param  string $item Name of parameter
     *
     * @return mixed       Contents of 'item' value
     */
    function get(string $item)
    {
        return isset($this->$item) ? $this->$item : false;
    }

    /**
     * Save to Json config file
     * @param  string|null $file path to save
     * @return bool|number           number of bytes saved in file or FALSE.
     */
    function save(string $file = null)
    {
        if ($file == null) {
            $file = static::$configFile;
        }

        $a = null;
        foreach ($this as $k => $v) {
            $a[$k] = $v;
        }
        return file_put_contents($file, json_encode($a, JSON_PRETTY_PRINT));
    }

    /**
     * Load configuration json file
     * @param  string|null $file path and filename
     * @return bool            success (treu/false)
     */
    function load(string $file = null)
    {
        if ($file == null) {
            $file = static::$configFile;
        }
        if (!file_exists($file)) {
            return false;
        }

        $a = json_decode(file_get_contents($file));

        if (isset($a->css)) {
            $this->css = $a->css;
        }

        if (isset($a->js)) {
            $this->js = $a->js;
        }
        
        if (isset($a->jss)) {
            $this->jss = $a->jss;
        }
        
        return true;
    }
}
