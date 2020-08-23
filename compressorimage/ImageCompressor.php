<?php
namespace app\helpers\imagick;

use app\helpers\IidUploadFiles;

class ImageCompressor
{
    const memoryLimit = 1000;
    const supportedFiles = [
      'png', 'jpg', 'jpeg', 'gif'
    ];

    public $width;
    public $height;

    /**
     * @param $fileInfo array have $_FILES format
     * @param $inputName
     * @return mixed
     * @throws \ImagickException
     */
    public function compressImage($fileInfo, $inputName)
    {
        if ( empty($this->height) ) {
            throw new \Exception("Height not set.");
        }

        if ( empty($this->width) ) {
            throw new \Exception("Width not set.");
        }

        $src = $fileInfo['tmp_name'][$inputName] ?? '';

        if ( empty($src) ) {
            throw new \Exception("FileEmpty");
        }

        $im = new \Imagick();

        $fileExist = $im->pingImage($src);
        if ( !$fileExist ) {
            throw new \Exception("Файл не найден");
        }
        try {
            $im->readImage($src);
        } catch (\ImagickException $e) {
            throw new \Exception($e->getMessage());
        }

        $format = strtolower($im->getImageFormat());
        if ( !in_array($format, self::supportedFiles) ) {
            throw new \Exception("not supported format = ".$format);
        }
        $im->setImageCompression(true);
        $im->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
        $im->setImageCompressionQuality(0);
        $im->resizeImage(
            min($im->getImageWidth(),  $this->width),
            min($im->getImageHeight(), $this->height),\Imagick::FILTER_CATROM , 1,TRUE );
        $im->setImageFormat('png');
        $im->setOption('png:exclude-chunk', 'tIME');
        $im->setResourceLimit(\Imagick::RESOURCETYPE_MEMORY , self::memoryLimit);

        $nameFile = uniqid().'.'.strtolower($im->getImageFormat());
        $savedImagePath = sys_get_temp_dir().'/'.$nameFile;

        if( !$im->writeImage($savedImagePath) ) {
            throw new \Exception('Не смог сохранить изображение');
        }

        $pngquant = new \app\helpers\Pngquant();
        try {
            $compressedFilePath = $pngquant->compress_png($savedImagePath);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $fileSize = filesize($compressedFilePath);
        $pathInfo = pathinfo($compressedFilePath);
//        $dirPath = $pathInfo ['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $fullFileName = $filename.'.'.$extension;

        $fileInfo['name'][$inputName] = $fullFileName;
        $fileInfo['tmp_name'][$inputName] = $compressedFilePath;
        $fileInfo['size'][$inputName] = $fileSize;

        return $fileInfo;
    }
}