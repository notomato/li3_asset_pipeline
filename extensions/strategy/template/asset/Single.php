<?php

namespace li3_asset_pipeline\extensions\strategy\template\asset;

use Assetic\Asset\AssetCollection;

/**
 * This strategy outputs each pipeline asset as a single link. This is default in development
 * or when debug is set to true.
 */
class Single extends \lithium\core\Object {

	public function write($name, AssetCollection $assetCollection) {
		// change asset collection filename to have md5 of getContent();
		return $assetCollection;
	}
}

?>