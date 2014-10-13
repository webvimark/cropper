Cropper for Yii 2
=====
Image cropper

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist webvimark/cropper "*"
```

or add

```
"webvimark/cropper": "*"
```

to the require section of your `composer.json` file.

Usage
-----

```php
<?= Cropper::widget([
	'acceptUrl'     => Url::to(['/slider/slider-image/crop']),
	'fileInputName' => 'cropperFileUpload',
	'imageSelector' => '#slider-image',
	'resultSize'    => array($model->slider->width, $model->slider->height),
	'cropParams'=>array(
//		'minSize'     => array($model->slider->width, $model->slider->height),
		'setSelect'   => array(0,0,$model->slider->width, $model->slider->height),
		'aspectRatio' => $model->slider->width/$model->slider->height,
	),
	'customParams'=>array(
		'modelId'=>$model->id,
	),
]) ?>
```
