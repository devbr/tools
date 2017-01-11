<?php
/**
 * Config\CLTool
 * PHP version 7
 *
 * @category  CLT
 * @package   Config
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.2
 * @link      http://paulorocha.tk/devbr
 */

namespace Config\CLTool;

/**
 * CSS Static Class
 *
 * @category CLT
 * @package  Config
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Optimizer
{
	private $css = ['path'=>'',
					'source'=>'source/',
					'bkp'=>'bkp/',
					'file'=>'all.css'];

	private $cssPack = ['default'=>[]];

	private $js = ['path'=>'',
					'source'=>'source/',
					'bkp'=>'bkp/',
					'file'=>'all.js'];

	private $jsPack = ['default'=>[]];


	private static $jsonFile = false;
	private static $node = false;

	/**
	 * Constructor
	 * @param string|null $file path of config json file
	 */
	function __construct(string $file = null)
	{
		if($file == null) $file = static::$jsonFile;
		if($file == false) {
			static::$jsonFile = __DIR__.'/'.pathinfo(__FILE__, PATHINFO_FILENAME).'.json';
			$file = static::$jsonFile;

			if(defined('_WWW')) {
				$this->css['path'] = _WWW.'css/';
				$this->js['path'] = _WWW.'js/';
			}
		}

		if(!file_exists($file)) $this->save();
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
		if(!static::$node) static::$node = new self($file);
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
	 * @return bool|number 		     number of bytes saved in file or FALSE.
	 */
	function save(string $file = null)
	{
		if($file == null) $file = static::$jsonFile;

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
		if($file == null) $file = static::$jsonFile;
		if(!file_exists($file)) return false;

		$a = json_decode(file_get_contents($file));
		foreach ($a as $k => $v) {
			$this->$k = $v;
		}
		return true;
	}
}