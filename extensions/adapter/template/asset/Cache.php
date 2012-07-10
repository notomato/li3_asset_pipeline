<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_asset_pipeline\extensions\adapter\template\asset;

class Cache extends \lithium\core\Object {

	/**
	 * Class constructor.
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		$defaults = array(
			'adapter' => 'default',
			'expiry' => '+1 year'
		);
		parent::__construct($config + $defaults);
	}

	/**
	 * Write value(s) to the cache.
	 *
	 * This adapter method supports multi-key write. By specifying `$key` as an
	 * associative array of key/value pairs, `$data` is ignored and all keys that
	 * are cached will receive an expiration time of `$expiry`.
	 *
	 * @param string|array $key The key to uniquely identify the cached item.
	 * @param mixed $data The value to be cached.
	 * @param null|string $expiry A strtotime() compatible cache time. If no expiry time is set,
	 *        then the default cache expiration time set with the cache configuration will be used.
	 * @return closure Function returning boolean `true` on successful write, `false` otherwise.
	 */
	public function write($key, $data, $expiry = null) {
		$expiry = ($expiry) ?: $this->_config['expiry'];

		return function($self, $params) use ($expiry) {
			$cachetime = (is_int($expiry) ? $expiry : strtotime($expiry)) - time();
			$key = $params['key'];

			if (is_array($key)) {
				return apc_store($key, $cachetime);
			}
			return apc_store($params['key'], $params['data'], $cachetime);
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
	public function read($key) {
		return function($self, $params) {
			return apc_fetch($params['key']);
		};
	}

	/**
	 * Clears user-space cache
	 *
	 * @return mixed True on successful clear, false otherwise
	 */
	public function clear() {
		return apc_clear_cache('user');
	}
}

?>