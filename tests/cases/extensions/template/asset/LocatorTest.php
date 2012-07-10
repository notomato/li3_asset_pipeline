<?php

namespace li3_asset_pipeline\tests\cases\extensions\template\asset;

use Assetic\Filter\LessphpFilter;
use Assetic\Filter\CoffeeScriptFilter;
use li3_asset_pipeline\extensions\template\Asset;
use li3_asset_pipeline\extensions\template\asset\Locator;
use li3_asset_pipeline\extensions\template\asset\Pipeline;
use lithium\core\Libraries;
use Symfony\Component\Finder\Finder;

class LocatorTest extends \lithium\test\Unit {
	
    private $locator;
    private $paths;
	private $root;
	
    public function setUp()
    {
		Pipeline::config(array(
			'default' => array(
				'test' => array(
					'paths' => array('/fixtures/assets1','/fixtures/assets2','/fixtures/assets3'),
					'processors' => array(
						'php' => new \lithium\template\View(),
						'less' => new LessphpFilter(),
						'coffee' => new CoffeeScriptFilter(),
						'sass' => ''
					)
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

    public function testSimpleAssetsLocating()
    {
        $this->assertEqual(
            array(array(
                'include' => false,
                'file'    => 'css/application.css',
                'filter'  => null,
                'root'    => $this->root.'/fixtures/assets3'
            )),
            $this->locator->locatePipelinedAssets('application.css', 'css')
        );
		
		$this->assertEqual(
            array(array(
                'include' => false,
                'file'    => 'css/application.css',
                'filter'  => null,
                'root'    => $this->root.'/fixtures/assets3'
            )),
            $this->locator->locatePipelinedAssets('application', 'css')
        );
    }

    public function testAssetsOverriding()
    {
        $this->assertEqual(
            array(array(
                'include' => false,
                'file'    => 'css/overrider.css',
                'filter'  => null,
                'root'    => $this->root.'/fixtures/assets1'
            )),
            $this->locator->locatePipelinedAssets('overrider.css')
        );
    }

    public function testAssetNotFound()
    {
		$message = sprintf(
			'Asset "unexisting.css" could not be found anywhere in registered pipeline paths (%s)',
			implode(', ', $this->paths)
		);
		$this->expectException($message);
		$this->locator->locatePipelinedAssets('unexisting.css');
    }

    public function testDirectoryIndexAsset()
    {
        $this->assertEqual(
            array(array(
                'include' => false,
                'file'    => 'js/custom_library/index.js',
                'filter'  => null,
                'root'    => $this->root.'/fixtures/assets2'
            )),
            $this->locator->locatePipelinedAssets('custom_library', 'js')
        );
    }

    public function testPreprocessorAsset()
    {
        $this->assertEqual(
            array(array(
                'include' => false,
                'file'    => 'js/some_script.js.coffee',
                'filter'  => array('coffee'),
                'root'    => $this->root.'/fixtures/assets2'
            )),
            $this->locator->locatePipelinedAssets('some_script.js')
        );

        $this->assertEqual(
            array(array(
                'include' => false,
                'file'    => 'css/sub/some_less_style.css.less',
                'filter'  => array('less'),
                'root'    => $this->root.'/fixtures/assets1'
            )),
            $this->locator->locatePipelinedAssets('sub/some_less_style.css', 'css')
        );
    }

    public function testJsRequireDirective()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/sub/asset_one.js.coffee',
                    'filter'  => array('coffee'),
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/asset_three.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets3'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/sub/application.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                )
            ),
            $this->locator->locatePipelinedAssets('sub/application', 'js')
        );
    }

    public function testJsRequireSelfDirective()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/sub/asset_one.js.coffee',
                    'filter'  => array('coffee'),
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/sub/application_self.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/asset_three.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets3'
                )
            ),
            $this->locator->locatePipelinedAssets('sub/application_self.js')
        );
    }

    public function testRecursiveRequireDirective()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'css/recursive_require_style.css.less',
                    'filter'  => array('less'),
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'css/overrider.css',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'css/second_level_of_recursive_style.css.sass',
                    'filter'  => array('sass'),
                    'root'    => $this->root.'/fixtures/assets3'
                ),
                array(
                    'include' => false,
                    'file'    => 'css/sub/sub2/topbar.css',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
            ),
            $this->locator->locatePipelinedAssets('recursive_require_style', 'css')
        );
    }

    public function testDontRequireSameAssetTwice()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/some_asset.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/another_asset_that_require_same_asset.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/require_same_assets.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
            ),
            $this->locator->locatePipelinedAssets('require_same_assets.js')
        );
    }

    public function testCanIncludeSameAssetTwice()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/some_asset.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
                array(
                    'include' => true,
                    'file'    => 'js/some_asset.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/another_asset_that_include_same_asset.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/include_same_assets.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets2'
                ),
            ),
            $this->locator->locatePipelinedAssets('include_same_assets.js')
        );
    }

    public function testAttemptToRequireUnexistingAsset()
    {
		$message = sprintf(
			'Asset "unexisting.js" could not be found anywhere in registered pipeline paths (%s) in asset: %s',
			implode(', ', $this->paths), 'js/failed.js'
		);
		$this->expectException($message);
		$this->locator->locatePipelinedAssets('failed.js');
    }

    public function testRequireRelativePaths()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/application/main/sub/script.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets3'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/application/main/current_dir_script.js.coffee',
                    'filter'  => array('coffee'),
                    'root'    => $this->root.'/fixtures/assets3'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/application/main/asset_with_relative_paths.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets3'
                ),
            ),
            $this->locator->locatePipelinedAssets('application/main/asset_with_relative_paths.js')
        );
    }

    public function testRequireDirectory()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/directory/1script.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/2script.js.coffee',
                    'filter'  => array('coffee'),
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/index.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/tree.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
            ),
            $this->locator->locatePipelinedAssets('directory', 'js')
        );
    }

    public function testRequireTree()
    {
        $this->assertEqual(
            array(
                array(
                    'include' => false,
                    'file'    => 'js/directory/1script.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/2script.js.coffee',
                    'filter'  => array('coffee'),
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/index.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/sub/fourth.js.coffee',
                    'filter'  => array('coffee'),
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/sub/third.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
                array(
                    'include' => false,
                    'file'    => 'js/directory/tree.js',
                    'filter'  => null,
                    'root'    => $this->root.'/fixtures/assets1'
                ),
            ),
            $this->locator->locatePipelinedAssets('directory/tree', 'js')
        );
    }
}
