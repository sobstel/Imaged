<?php
require_once 'PHPUnit/Framework.php';
require_once '../lib/Imaged.php';
require_once 'files.php';

class LoadingTest extends PHPUnit_Framework_TestCase
{

    public function testCanBeInitializedViaBothConstructorAndStaticCreateMethod()
    {
        $img = new Imaged(JPG_1);
        $this->assertType('Imaged', $img);

        $img = Imaged::create(JPG_1);
        $this->assertType('Imaged', $img);
    }

    public function testSupportsJpeg()
    {
        $img = Imaged::create(JPG_1);
        $this->assertEquals(IMAGETYPE_JPEG, $img->getType());

        $img = Imaged::create(JPG_2);
        $this->assertEquals(IMAGETYPE_JPEG, $img->getType());
    }

    public function testSupportsPng()
    {
        $img = Imaged::create(PNG_1);
        $this->assertEquals(IMAGETYPE_PNG, $img->getType());
    }

    public function testSupportsGif()
    {
        $img = Imaged::create(GIF_1);
        $this->assertEquals(IMAGETYPE_GIF, $img->getType());
    }

    public function testExceptionCode11OnFileNotFound()
    {
        try
        {
            new Imaged(NOT_IMAGE);
        }
        catch (Imaged_Exception $e)
        {
            $this->assertEquals($e->getCode(), 11, 'Wrong filename should result in exception code 11');
        }
    }

    public function testExceptionCode11OnFileIsNotImage()
    {
        try
        {
            new Imaged(NOT_IMAGE);
        }
        catch (Imaged_Exception $e)
        {
            $this->assertEquals($e->getCode(), 11, 'File is not an image should result in exception code 11');
        }
    }

    public function testExceptionCode12OnUnsupportedImageFormat()
    {
        try
        {
            new Imaged(JPG_1);
        }
        catch (Imaged_Exception $e)
        {
            $this->assertEquals($e->getCode(), 12, 'Unsupported image format does not result in exception code 12');
        }
    }

}