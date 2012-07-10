<?php

namespace li3_asset_pipeline\tests\cases\extensions\template\asset;

use Assetic\Filter\LessphpFilter;
use Assetic\Filter\CoffeeScriptFilter;
use li3_asset_pipeline\extensions\template\Asset;
use li3_asset_pipeline\extensions\template\asset\Locator;
use li3_asset_pipeline\extensions\template\asset\Pipeline;
use lithium\core\Libraries;
use Symfony\Component\Finder\Finder;

class PipelineTest extends \lithium\test\Unit {
	
    private $locator;
    private $paths;
	private $root;
	
    public function setUp()
    {
		Pipeline::config(array(
			'default' => array(
				'test' => array(
					'paths' => array(
						'/fixtures/assets1',
						'/fixtures/assets2',
						'/fixtures/assets3'
					),
					'cache' => Libraries::get('app', 'resources').'/tmp/cache',
					'write' => Libraries::get('app', 'webroot'),
					'debug' => 'false',
					'processors' => array(
						'php' => new \lithium\template\View(),
						'less' => new LessphpFilter(),
						'coffee' => new CoffeeScriptFilter('/usr/local/bin/coffee', '/usr/local/bin/node'),
						'js' => new \Assetic\Filter\Yui\JsCompressorFilter(LITHIUM_APP_PATH . '/vendor/nervo/yuicompressor/yuicompressor.jar'),
						'css' => new \Assetic\Filter\Yui\CssCompressorFilter(LITHIUM_APP_PATH . '/vendor/nervo/yuicompressor/yuicompressor.jar'),
						'sass' => new \Assetic\Filter\Sass\SassFilter()
					),
					'strategies' => array(
						'Combine',
						'Minify',
						'Fingerprint'
					),
					//'adapter' => 'S3',
					'adapter' => 'File'
				)
			)
		));
		
		$config = Libraries::get('li3_asset_pipeline');
		$this->root = $config['path'];
        $this->locator = new Locator($this->paths = array(
            $this->root.'/fixtures/assets1',
            $this->root.'/fixtures/assets2',
            $this->root.'/fixtures/assets3',
        ), array('less', 'sass', 'coffee'));
    }
	
	function testInsertAsset() {
		$assets = Asset::getPipelineAssets('sub/recursive_require_style','css');
		$written = Pipeline::write('default', $assets, array('type' => 'css', 'target' => 'css/master.css'));
	}
}

?>