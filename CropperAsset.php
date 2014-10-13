<?php

namespace webvimark\extensions\Cropper;

use yii\web\AssetBundle;

class CropperAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets';
		$this->css = ['jquery.Jcrop.min.css'];
		$this->js = ['jquery.Jcrop.min.js'];

		parent::init();
	}
}