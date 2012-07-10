<?php

namespace li3_asset_pipeline\extensions\strategy\template\asset;

use Assetic\Asset\AssetCollection;
use lithium\core\Libraries;
/**
 * This strategy concatenates assets but doesn't do any compression or fingerprinting.
 */
class Fingerprint extends \lithium\core\Object {

	public function write(\Assetic\Asset\AssetCollection $assetCollection, $options = array()) {
		xdebug_break();
		$hash = md5($assetCollection->dump());
		$pathinfo = pathinfo($options['target']);
		$assetCollection->setTargetPath(
			str_replace($pathinfo['filename'], $pathinfo['filename'].'-'.$hash, $options['target'])
		);
		return $assetCollection;
	}
	
	public function read() {
		
	}
}

?>