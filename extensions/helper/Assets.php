<?php

namespace li3_asset_pipeline\extensions\helper;

use li3_asset_pipeline\extensions\template\asset;

class Assets extends \lithium\template\Helper {

	public function style($source) {
		
	}

	public function script($source) {
		$assets = Asset::getPipelineAssets($source);
		if ($assets) {
			Pipeline::insert($assets);
			Pipeline::flush();
		}
	}
	
	public function image() {
		
	}
}

?>