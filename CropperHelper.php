<?php
namespace webvimark\extensions\Cropper;

use Yii;
use yii\web\UploadedFile;

class CropperHelper
{
        /**
         * If <input type="file" name="someInputName"> then "someInputName"
         *
         * @var string
         */
        public $fileInputName;

        /**
	 * For example Yii::$app->baseUrl . '/slider/images/'
	 *
         * @var string
         */
        public $imagesWebPath;

        /**
	 * For example Yii::getPathOfAlias('webroot.front.img.slider')
         * @var string
         */
        public $uploadFolder;

        /**
	 * If defined temp images will be stored in this folder
	 * For example Yii::getPathOfAlias('webroot.front.img.tmp')
	 *
         * @var string
         */
        public $tmpFolder;

	/**
	 * __construct
	 *
	 * @param string      $fileInputName
	 * @param string      $imagesWebPath
	 * @param string      $uploadFolder
	 * @param string|null $tmpFolder
	 */
        public function __construct($fileInputName, $imagesWebPath, $uploadFolder, $tmpFolder = null)
        {
                $this->fileInputName = $fileInputName;
                $this->imagesWebPath = rtrim($imagesWebPath, '/');
                $this->uploadFolder  = $uploadFolder;
                $this->tmpFolder     = $tmpFolder ? $tmpFolder : $uploadFolder;
        }

        /**
         * crop 
         * 
         * @param string $file_output 
         * @return string
         */
        public function crop($file_output)
        {
                $x = $_GET["x"];
                $y = $_GET["y"];
                $w = $_GET["w"];
                $h = $_GET["h"];

                $tmp = explode('/', $file_output);
                $newFileName = end($tmp);

                $file_input = $this->_getData('tmpFile');
                list($originalWidth, $originalHeight, $imageExt) = getimagesize($file_input);

                // Find absolute coordinates
                $shrinkedWidth = $_GET["shrinked-width"];
                $multiplier = $originalWidth / $shrinkedWidth;

                $x *= $multiplier;
                $y *= $multiplier;
                $w *= $multiplier;
                $h *= $multiplier;

                $extensions = array('','gif','jpeg','png');
                $ext = $extensions[$imageExt];

                if (! $ext) 
                {
                        echo json_encode(array(
                                'error'   => true,
                                'message' => 'Wrong file format',
                        ));
                        Yii::$app->end();
                }

                // Load the original image.
                $func = 'imagecreatefrom'.$ext;
                $img = $func($file_input);

                imagealphablending($img, true);

                // Create a blank canvas for the cropped image.
                $img_cropped = imagecreatetruecolor($w, $h);
                imagesavealpha($img_cropped, true);
                imagealphablending($img_cropped, false);
                $transparent = imagecolorallocatealpha($img_cropped, 0, 0, 0, 127);
                imagefill($img_cropped, 0, 0, $transparent);

                // Crop the image and store the data on the blank canvas.
                imagecopyresampled($img_cropped, $img, 0, 0, $x, $y, $w, $h, $w, $h); // or imagecopy()

                $func = 'image'.$ext;
                $func($img_cropped, $file_output);

                // Free memory.
                imagedestroy($img);
                imagedestroy($img_cropped); 

                // If param "resultSize" defined in widget
                if ( isset($_GET['result-size-w']) ) 
                        $this->resize($file_output, $_GET['result-size-w'], $_GET['result-size-h']);

                echo $this->imagesWebPath . '/' . $newFileName;
        }

        /**
         * resize 
         * 
         * @param string $filename 
         * @param int $newWidth 
         * @param int $newHeight 
         */
        public function resize($filename, $newWidth, $newHeight)
        {
                list($width, $height, $imageExt) = getimagesize($filename);

                $extensions = array('','gif','jpeg','png');
                $ext = $extensions[$imageExt];

                $thumb = imagecreatetruecolor($newWidth, $newHeight);
                imagesavealpha($thumb, true);
                imagealphablending($thumb, false);
                $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                imagefill($thumb, 0, 0, $transparent);

                // Load the original image.
                $func = 'imagecreatefrom'.$ext;
                $source = $func($filename);

                imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                $func = 'image'.$ext;
                $func($thumb, $filename);

                imagedestroy($source);
                imagedestroy($thumb);
        }

        /**
         * deleteTmpImage
         */
        public function deleteTmpImage()
        {
                @unlink($this->_getData('tmpFile'));
                Yii::$app->session->set('__cropper', null);
        }

	/**
	 * Delete all temp images
	 */
	public function deleteAllTmpImages()
	{
		$prefix = Yii::$app->user->isGuest ? 'cropped___guest___' : 'cropped___' . Yii::$app->user->id . '___';

		$files = glob($this->tmpFolder ."/{$prefix}*");

		if ( is_array($files) )
		{
			foreach($files as $file)
				@unlink($file);
		}

	}

        /**
         * getTmpImage 
         * 
         * @return UploadedFile|null
         */
        public function getTmpImage()
        {
                return isset($_FILES[$this->fileInputName]) ? UploadedFile::getInstanceByName($this->fileInputName) : null;
        }

        /**
         * saveTmpImage 
         * 
         * @param UploadedFile $file
         */
        public function saveTmpImage($file)
        {
                $prefix = Yii::$app->user->isGuest ? 'cropped___guest___' : 'cropped___' . Yii::$app->user->id . '___';

//                 Clean tmp files
//                $files = glob($this->tmpFolder ."/{$prefix}*");
//                foreach($files as $file)
//                        @unlink($file);

                if ( $file ) 
                {
                        // Tmp file
                        $fileName = $prefix . '_' .$file->name;
                        $filePath = $this->tmpFolder . '/' . $fileName;

                        if ( $file->saveAs($filePath) )
                        {
                                $this->_setData('tmpFile', $filePath);
                                $this->_setData('originalName', $file->name);

                                echo json_encode(array(
                                        'success' => true,
                                        'file'    => $this->imagesWebPath . '/' .$fileName,
                                ));
                                Yii::$app->end();
                        }
                }
                else
                {
                        echo json_encode(array(
                                'error'   => true,
                                'message' => 'Upload error',
                        ));
                        Yii::$app->end();
                }
        }

        /**
         * throwError 
         * 
         * @param string $message 
         */
        public function throwError($message)
        {
                echo json_encode(array(
                        'error'   => true,
                        'message' => $message,
                ));
                Yii::$app->end();
        }

        /**
         * uploadParentImage 
         */
        public function uploadParentImage($imagesWebPath)
        {
                if ( ! isset($_FILES[$this->fileInputName]) ) 
                        return;

                $prefix = Yii::$app->user->isGuest ? 'cropped___guest___' : 'cropped___' . Yii::$app->user->id . '___';

                // Clean tmp files
                $files = glob($this->tmpFolder ."/{$prefix}*");
                foreach($files as $file)
                        @unlink($file);

                $file = UploadedFile::getInstanceByName($this->fileInputName);

                if ( $file ) 
                {
                        // Tmp file
                        $fileName = $prefix . '_' .$file->name;
                        $filePath = $this->tmpFolder . '/' . $fileName;

                        if ( $file->saveAs($filePath) )
                        {
                                $this->_setData('tmpFile', $filePath);
                                $this->_setData('originalName', $file->name);

                                echo json_encode(array(
                                        'success' => true,
                                        'file'    => rtrim($imagesWebPath, '/') . '/' .$fileName,
                                ));
                                Yii::$app->end();
                        }
                }
                else
                {
                        echo json_encode(array(
                                'error'   => true,
                                'message' => 'Upload error',
                        ));
                        Yii::$app->end();
                }
        }

        /**
         * Save data in session
         * 
         * @param string $key 
         * @param string $val 
         */
        public function _setData($key, $val)
        {
                $current = Yii::$app->session->get('__cropper');
                $current = is_array($current) ? $current : array();

                if ( $val === null ) 
                {
                        unset($current[$key]);
                        $data = $current;
                }
                else
                {
                        $data = array_merge($current, array($key => $val));
                }

		Yii::$app->session->set('__cropper', $data);
        }

        /**
         * Get data from session
         * 
         * @param string $key 
         * @return string
         */
        public function _getData($key)
        {
                $current = Yii::$app->session->get('__cropper');
                $current = is_array($current) ? $current : array();

                return isset($current[$key]) ? $current[$key] : '';
        }
}
