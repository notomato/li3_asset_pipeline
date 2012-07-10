<?php

use li3_asset_pipeline\extensions\template\asset\Pipeline;

Pipeline::config(array(
	'default', array(
		'development' => array(
			'strategy' => 'simple',
			'processors' => array(
				'less' => '',
				'coffee' => '',
				'sass' => ''
			)
		),
		'test' => array(
			'enabled' => 'false'
		),
		'production' => array(
			'strategy' => 'fingerprint',
			'processors' => array(
				'less' => '',
				'coffee' => '',
				'sass' => '',
				'css' => '',
				'js' => '',
				'php' => function($source, $data) {
					$view = new View(array('loader' => 'Simple', 'renderer' => 'Simple'));
					$view->render('element', $data, array('element' => file_get_contents($source)));
				}
			),
		)
	)
));

?>