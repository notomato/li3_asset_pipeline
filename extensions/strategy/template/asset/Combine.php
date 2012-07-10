<?php

namespace li3_asset_pipeline\extensions\strategy\template\asset;

use Assetic\Asset\AssetCollection;

/**
 * This strategy concatenates assets but doesn't do any compression or fingerprinting.
 */
class Combine extends \lithium\core\Object {

	public function write(\Assetic\Asset\AssetCollection $assetCollection, $options = array()) {
		xdebug_break();
		return $assetCollection;
	}
}

?>