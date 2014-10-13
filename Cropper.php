<?php
namespace webvimark\extensions\Cropper;

use yii\base\Exception;
use yii\base\Widget;
use yii\helpers\Json;

/**
 * Cropper 
 * 
<code>
        <input type="file" name="cropperFileUpload">

        <?php $this->widget('ext.Cropper.Cropper', array(
                'acceptUrl'     => Yii::app()->createUrl('/site/api'),
                'fileInputName' => 'cropperFileUpload',
        )); ?>

</code>
 *
 * @author vi mark <webvimark@gmail.com> 
 * @license MIT
 */
class Cropper extends Widget
{
        /**
         * Where you wish to accept and proceed uploaded files 
         * by default widget does it itself
         * Example: Yii::app()->createUrl('/site/index')
         * 
         * @var string
         */
        public $acceptUrl;
        /**
         * If <input type="file" name="someInputName"> then "someInputName"
         *
         * @var string
         */
        public $fileInputName;
        /**
         * If you want final image resized to some fixed size
         * array('width', 'height')
         * 
         * @var array
         */
        public $resultSize;
        /**
         * cropParams 
         * 
         * @var array
         */
        public $cropParams;
        /**
         * imageSelector 
         * 
         * @var string
         */
        public $imageSelector;
        /**
         * customParams 
         * 
         * @var array
         */
        public $customParams = array();
        /**
         * @var string
         */
        public $viewFile = 'index';

        /**
         * init 
         */
        public function run()
        {
                if ( ! $this->fileInputName ) 
                        throw new Exception('Define file input name');

		CropperAsset::register($this->view);

		return $this->render($this->viewFile, array(
			'acceptUrl'     => $this->acceptUrl,
			'fileInputName' => $this->fileInputName,
			'cropParams'    => Json::encode($this->cropParams),
			'imageSelector' => $this->imageSelector,
			'customParams'  => Json::encode($this->customParams),
			'resultSize'    => Json::encode($this->resultSize),
                ));
        }

}
