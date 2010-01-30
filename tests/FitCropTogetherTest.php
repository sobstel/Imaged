<?php
require_once 'PHPUnit/Framework.php';
require_once '../lib/Imaged.php';
require_once 'files.php';

class FitCropTogtherTest extends PHPUnit_Framework_TestCase
{

    public function testCrop()
    {
        $img = new Imaged(PNG_1);
        $img->fit(300, 300)
            ->crop(200, 200, Imaged::CENTER, Imaged::CENTER)
            ->write(tJPG);

        $img->restoreOriginal()
            ->crop(200, 200, Imaged::CENTER, Imaged::CENTER)
            ->fit(300, 300)
            ->write(tPNG);
    }

}