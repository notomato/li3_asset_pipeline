<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_asset_pipeline\extensions\adapter\template\asset;

use lithium\core\Libraries;

class File extends \lithium\core\Object {

	/**
	 * Class constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'prefix' => ''
		);
		parent::__construct($config + $defaults);
	}

	public function write($assets) {
		xdebug_break();
		$write = $this->_config['write'];
		$path = LITHIUM_APP_PATH . '/webroot';
		
		return function($self, $params) use ($assets, $path) {
			xdebug_break();
			$assetWriter = new \Assetic\AssetWriter($path);
			// if config['debug']
				// output all indivudually
			// else 
			return $assetWriter->writeAsset($assets);
		};
	}

	/**
	 * Read value(s) from the cache.
	 *
	 * This adapter method supports multi-key reads. By specifying `$key` as an
	 * array of key names, this adapter will attempt to return an array of data
	 * containing key/value pairs of the requested data.
	 *
	 * @param string|array $key The key to uniquely identify the cached item.
	 * @return closure Function returning cached value on successful read, `false` otherwise.
	 */
	public function read($source) {
		return function($self, $params) {
			
		};
	}

	/**
	 * Clears user-space cache
	 *
	 * @return mixed True on successful clear, false otherwise
	 */
	public function clear($source) {
		// Locate files in $source
		// foreach delete...
		return apc_clear_cache('user');
	}
}

?>