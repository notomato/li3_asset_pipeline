<?php

namespace li3_asset_pipeline\extensions\strategy\template\asset;

use Assetic\Asset\AssetCollection;

/**
 * This strategy concatenates assets but doesn't do any compression or fingerprinting.
 */
class Minify extends \lithium\core\Object {

	public function write(\Assetic\Asset\AssetCollection $assetCollection, $options = array()) {
		xdebug_break();
		if (!isset($options['config']['processors'][$options['type']])) {
			throw new \lithium\core\ConfigException('Processor not configured for type: '.$options['type']);
		}
		$assetCollection->ensureFilter($options['config']['processors'][$options['type']]);
		return $assetCollection;
	}
	
	public function flush($assets) {
		
	}
}

?>