<?php
require_once 'PHPUnit/Framework.php';
require_once '../lib/Imaged.php';
require_once 'files.php';

class ResampleTest extends PHPUnit_Framework_TestCase
{

    public function test()
    {
        $img = new Imaged(JPG_1);
    }

}