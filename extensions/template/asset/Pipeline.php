<?php

namespace li3_asset_pipeline\extensions\template\asset;

use Assetic\Asset\AssetCollection;
use lithium\core\Libraries;

/**
 * Valid configuration options
 *     - strategy: Should be one of minify, compress, combine, or fingerprint.
 *  
 */
class Pipeline extends \lithium\core\Adaptable {
	
	protected static $_configurations = array();
	protected static $_strategies = 'strategy.template.asset';
	protected static $_adapters = 'adapter.template.asset';
	
	public static function write($name, AssetCollection $assets, $options = array()) {
		$options += array('conditions' => null, 'strategies' => true);
		$settings = static::config();

		if (!isset($settings[$name])) {
			return false;
		}
		$conditions = $options['conditions'];

		if (is_callable($conditions) && !$conditions()) {
			return false;
		}
		
		if ($options['strategies']) {
			$options = array(
				'class' => __CLASS__, 
				'config' => $settings[$name], 
				'type' => $options['type'],
				'target' => $options['target']
			);
			$assets = static::applyStrategies(__FUNCTION__, $name, $assets, $options);
		}

		$method = static::adapter($name)->write($assets);
		$params = compact('assets');
		return static::_filter(__FUNCTION__, $params, $method, $settings[$name]['filters']);
	}
	
	public static function read($name, $key, array $options = array()) {
		$options += array('conditions' => null, 'strategies' => true, 'write' => null);
		$settings = static::config();

		if (!isset($settings[$name])) {
			return false;
		}
		$conditions = $options['conditions'];

		if (is_callable($conditions) && !$conditions()) {
			return false;
		}
		$key = static::key($key);
		$method = static::adapter($name)->read($key);
		$params = compact('key');
		$filters = $settings[$name]['filters'];
		$result = static::_filter(__FUNCTION__, $params, $method, $filters);

		if ($result === null && ($write = $options['write'])) {
			$write = is_callable($write) ? $write() : $write;
			list($expiry, $value) = each($write);
			$value = is_callable($value) ? $value() : $value;

			if (static::write($name, $key, $value, $expiry)) {
				$result = $value;
			}
		}

		if ($options['strategies']) {
			$options = compact('key') + array('mode' => 'LIFO', 'class' => __CLASS__);
			$result = static::applyStrategies(__FUNCTION__, $name, $result, $options);
		}
		return $result;
	}
	
	public static function clear($name) {
		
	}
}

?>