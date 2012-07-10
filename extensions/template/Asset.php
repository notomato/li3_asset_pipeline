<?php

namespace li3_asset_pipeline\extensions\template;

use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;
use li3_asset_pipeline\extensions\template\asset\Pipeline;
use li3_asset_pipeline\extensions\template\asset\Locator;
use lithium\core\Libraries;

/**
 * The Asset class handles converting a source string into a Assetic asset object
 * that can then be inserted into a pipeline. 
 */
class Asset extends \lithium\core\Object {
	
	/**
	 * Takes a source to an asset and returns an AssetCollection if it can be found,
	 * false otherwise.
	 *
	 * @return \Assetic\Asset\FileAsset|boolean 
	 */
	public static function getPipelineAssets($source, $type = null) {
		
		if (null === $type) {
            $type = pathinfo($source, PATHINFO_EXTENSION);
        }
		if (!$type) {
			throw new \RuntimeException('Must specify type for asset ' . $source);
		}
		
		$config = Pipeline::config('default');
		$locator = new Locator(
			static::getPaths(),
			array_keys($config['processors'])
		);
		$locatedAssets = $locator->locatePipelinedAssets($source, $type);
		$assets = new AssetCollection();
		if ($locatedAssets) {
			foreach ($locatedAssets as $formula) {
				$fileAsset = new FileAsset(
                    $formula['root'].'/'.$formula['file'],
                    static::getProcessors($formula['file']),
                    $formula['root'],
                    $formula['file']
                );
                $fileAsset->setTargetPath($formula['file']);
                $assets->add($fileAsset);
			}
		}
		return $assets;
	}
	
	/**
	 * Get all classes to search for assets. This will include the configured assets folder in
	 * all available libraries, as well as the main app. The default folder is 'assets' but can
	 * be set to another folder, or an array of folders in Pipeline configuration.
	 *
	 * @return string 
	 */
	public static function getPaths() {
		$config = Pipeline::config('default');
		$assetPaths = array();
		$libraries = Libraries::get();
		foreach ($libraries as $library) {
			$paths = isset($config['paths']) ? (array) $config['paths'] : array('/assets');
			foreach ($paths as $path) {
				$assetPaths[] = $library['path'] . $path;
			}
		}
		return $assetPaths;
	}
	
	/**
	 * Get an array of processors to be applied to a source from the list of configured processors. 
	 * Assets should be named with extensions from right to left with the processors to be applied, 
	 * for example `application.css.less` will pass through the less filter (compulsory) and css 
	 * filter/compressor (if configured).
	 *
	 * @param type $source
	 * @return type
	 * @throws \RuntimeException 
	 */
	public static function getProcessors($source) {
		
		$name = $source;
		$processors = array();
		$config = Pipeline::config('default');
		
		if (!pathinfo($name, PATHINFO_EXTENSION)) {
			throw new \RuntimeException('No extension specified for asset ' . $name);
		}
		
		while(pathinfo($name, PATHINFO_EXTENSION)) {
			
			$info = pathinfo($name);
			
			if ($info['extension']) {
				if (!isset($config['processors'][$info['extension']])) {
					if (!in_array($info['extension'], array('css','js'))) {
						throw new \RuntimeException('Filter not configured for assets of type ' . $info['extension']);
					}
				} else {
					$processors[$info['extension']] = $config['processors'][$info['extension']];
				}
			}
			
			if ($info['extension']) {
				$name = $info['filename'];
			} else {
				$name = '';
			}
			
		}
		return $processors;
	}
}

?>