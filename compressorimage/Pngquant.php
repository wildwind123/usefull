<?php


namespace app\helpers;


class Pngquant
{
    /**
     * https://pngquant.org/
     * @param $path_to_png_file string - path to any PNG file, e.g. $_FILE['file']['tmp_name']
     * @return string - content of PNG file after conversion
     */
    function compress_png($path_to_png_file)
    {

        if (!file_exists($path_to_png_file)) {
            throw new \Exception("File does not exist: $path_to_png_file");
        }

        $pathinfo = pathinfo($path_to_png_file);
        $dirPath = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        if ( strtolower($extension) != "png" ) {
            throw new \Exception("Wrong file format. Should be png for pngquant.");
        }
        $fullFileName = $filename.'.'.$extension;

        $compressedFilePrefix = 'compressed';

        //Run cmd
       shell_exec("cd $dirPath && pngquant $fullFileName --quality=10 --ext $compressedFilePrefix.png -f");

       $existFile = $dirPath.'/'.$filename.$compressedFilePrefix.'.png';

        if (!file_exists($existFile)) {
            throw new \Exception("File does not exist: $existFile");
        }

        return $existFile;
    }
}