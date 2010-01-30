<?php
/**
 * Imaged for PHP5
 * 
 * Copyright (c) 2008, Przemek Sobstel (sobstel.org). 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 *     * Neither the name of segfaultlabs nor the names of its contributors 
 *       may be used to endorse or promote products derived from this software 
 *       without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package Imaged
 * @version SVN: $Id: $
 * @author Przemek Sobstel http://sobstel.org
 * @link http://github.com/sobstel/Imaged
 */
 
class Imaged
{

	const FIT_ONEOF = 1;
	const FIT_WIDTH = 2;
	const FIT_HEIGHT = 4;
	const FIT_BOTH = 6; // FIT_WIDTH | FIT_HEIGHT

    const RESAMPLE_DOWN = 1;
    const RESAMPLE_UP = 2;
    const RESAMPLE_BOTH = 3;
	
	const TOP = 'top';
	const BOTTOM = 'bottom';
	const LEFT = 'left';
	const RIGHT = 'right';
	const CENTER = 'center';
			
	/** 
	 * @var array Mapping image type to image creating function name
	 */
	static protected $typeToCreateFunc = array(
		IMAGETYPE_JPEG => 'imagecreatefromjpeg',
		IMAGETYPE_PNG => 'imagecreatefrompng', 
		IMAGETYPE_GIF => 'imagecreatefromgif',
	);
	
	/** 
	 * @var array Mapping image type to image writing function name
	 */
	static protected $typeToWriteFunc = array(
		IMAGETYPE_JPEG => 'imagejpeg',
		IMAGETYPE_PNG => 'imagepng', 
		IMAGETYPE_GIF => 'imagegif',
	);	

	/** 
	 * @var array Mapping extension to image type
	 */	
	static protected $extensionToType = array(
		'jpeg' => IMAGETYPE_JPEG,	
		'jpg' => IMAGETYPE_JPEG,
		'png' => IMAGETYPE_PNG,
		'gif' => IMAGETYPE_GIF, 
	);	
	
	/**
	 * @var resource Image (GD) resource
	 */
	protected $ir;
	
	/**
	 * 
	 * @var resource Original image (GD) resource
	 */
	protected $orgIr;
	
	/** 
	 * @var string Image filename
	 */
	protected $filename;
	
	/** 
	 * @var int Image type
	 */
	protected $type;
	
	/**
	 * Constructor
	 * 
	 * @var string Image filename
	 */
	public function __construct($filename)
	{
		// read image
		$info = @getimagesize($filename);
		if (!$info)
		{
			throw new Imaged_Exception('Provided file does not exist or is not an image', 11);
		}	

		// read image type (IMAGETYPE_*)
		$type = $info[2];
		
		// is supported image type?
		if (!isset(self::$typeToCreateFunc[$type]))
		{		
			throw new Imaged_Exception('Unsupported image type (while reading)', 12);
		}

		// determine function name
		$func = self::$typeToCreateFunc[$type];
		
		// create image
		$this->orgIr = $this->ir = $func($filename);
		$this->filename = $filename;
		$this->type = $type;	
	}
	
	/**
	 * Creates Imaged object
     * 
	 * @param Image filename
	 * @return Imaged
	 */
	static public function create($filename)
	{
		return new self($filename);
	}
		
	/**
	 * Copies and resizes part of an image with resampling
	 * 
	 * Wrapper around imagecopyresampled(). Arguments order and number differ!
	 * 
	 * @param int New width
	 * @param int New height
	 * @param int Original image X position
	 * @param int Original image Y position
	 * @param int optional Original image width
	 * @param int optional Original image height
     * @param int
	 * @return Imaged
	 */
	public function resample($dstW, $dstH, $srcX = 0, $srcY = 0, $srcW = null, $srcH = null, $resampleMethod = self::RESAMPLE_BOTH)
	{
		// check destination dimensions
		if (($dstW < 1) || ($dstH < 1))
		{
			throw new Imaged_Exception('Both new width and new height must be greather than 0', 32);
		}

		// source width
		if (is_null($srcW))
		{
			$srcW = $this->getWidth();		
		}
		
		// source height
		if (is_null($srcH))
		{
			$srcH = $this->getHeight();	
		}	

        // do not resample down (when original image bigger)
        if (!($resampleMethod & self::RESAMPLE_DOWN))
        {
            if ($srcW > $dstW)
            {
                $dstW = $srcW;
            }
            if ($srcH > $dstH)
            {
                $dstH = $srcH;
            }
        }

        // do not resample up (when original image smaller)
        if (!($resampleMethod & self::RESAMPLE_UP))
        {
            if ($srcW < $dstW)
            {
                $dstW = $srcW;
            }
            if ($srcH < $dstH)
            {
                $dstH = $srcH;
            }
        }
		
		// new image
		$ir = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($ir, false);
        imagesavealpha($ir, true);
		
		// resize
		$result = imagecopyresampled($ir, $this->ir, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
		if (!$result)
		{
			throw new Imaged_Exception('Resampling failed - imagecopyresampled() returned false', 22);
		}
		
		$this->ir = $ir;

        return $this;
	}

    /**
     * Resizes image unproportionally
     *
     * @param int New width
     * @param int New height
     * @param int
     * @return Imaged
     */
    public function resize($width, $height, $resampleMethod = self::RESAMPLE_BOTH)
    {
        return $this->resample($width, $height, 0, 0, null, null, $resampleMethod);
    }
			
	/**
	 * Fits the image into a specified area by proportional scaling
	 *  
	 * fit(100, 100)
	 * fit(100, null)
	 * fit(null, 100)
	 *  
	 * @param int optional Width
	 * @param int optional Height
     * @param int optional Fit method
	 * @return Imaged
	 */
	public function fit($width = null, $height = null, $fitMethod = self::FIT_BOTH, $resampleMethod = self::RESAMPLE_BOTH)
	{
		if (is_null($width) && is_null($height))
		{
			throw new Imaged_Exception('Either width or height must be set', 31);
		}

		// original size
		$orgWidth = $this->getWidth();
		$orgHeight = $this->getHeight();

		// ratios
		$orgWhRatio = $orgWidth / $orgHeight;
		$orgHwRatio = 1 / $orgWhRatio;
						
		// fits to shorter dimension or only-set dimension (i.e. other set to null)
		if ($fitMethod & self::FIT_ONEOF)
		{
            if (is_null($width))
            {
                $fitMethod = self::FIT_HEIGHT;
            }
            elseif (is_null($height))
            {
                $fitMethod = self::FIT_WIDTH;
            }
            else
            {
                $fitMethod = ($orgHwRatio >= 1) ? self::FIT_WIDTH : self::FIT_HEIGHT;
            }
		}

		// if width not defined
		if (is_null($width))
		{
			$width = round($height * $orgHwRatio);
		}

		// if height not defined
		if (is_null($height))
		{
			$height = round($width * $orgWhRatio);
		}
        
		// change the height to fit the width
		if ($fitMethod & self::FIT_WIDTH)
		{
            $oldHeight = $height;
            $height = round($width * $orgHwRatio);
		}

        // change the width to fit the height
		if ($fitMethod & self::FIT_HEIGHT)
		{
            if (isset($oldHeight) && ($oldHeight < $height))
            {
                $height = $oldHeight;
            }

			$width = round($height * $orgWhRatio);
		}

		// resize
		$this->resample($width, $height, 0, 0, $orgWidth, $orgHeight, $resampleMethod);

		return $this;
	}

	/**
	 * Crops the image
	 *
	 * crop(width, height)
	 * crop(width, height, x, y)
	 *
	 * @param int Width
	 * @param int Height
	 * @param mixed X position, int or one of Imaged::LEFT, Imaged::RIGHT, Imaged::CENTER constants
	 * @param mixed Y position, int or one of Imaged::TOP, Imaged::BOTTOM, Imaged::CENTER constants
	 * @return Imaged
	 */
	public function crop($width, $height, $x = 0, $y = 0)
	{
		// x
		if (!is_int($x))
		{
			$orgWidth = $this->getWidth();

			if ($x === self::RIGHT)
			{
				$x = $orgWidth - $width;
			}
			elseif ($x === self::CENTER)
			{
				$x = round(($orgWidth - $width)/2);
			}

			if ($x < 0)
			{
				$x = 0;
			}
		}

		// y
		if (!is_int($y))
		{
			$orgHeight = $this->getHeight();

			if ($y === self::BOTTOM)
			{
				$y = $orgHeight - $height;
			}
			elseif ($y === self::CENTER)
			{
				$y = round(($orgHeight - $height)/2);
			}

			if ($y < 0)
			{
				$y = 0;
			}
		}

		// crop
		$this->resample($width, $height, $x, $y, $width, $height);

		return $this;
	}
		    
	/**
	 * Sends the image to the output or write to specified filename
	 * 
	 * Determines type by specified extension
	 *  
	 * @param mixed optional Filename or image type
	 * @param array optional Args as accepted by image* function
	 * @return Imaged
	 */
	public function write()
	{
        $funcArgs = func_get_args();

        if (!empty($funcArgs))
        {
            $filenameOrType = array_shift($funcArgs);

			if (is_string($filenameOrType)) // filename provided
			{
				$filename = $filenameOrType;
			}
			elseif (is_int($filenameOrType)) // type provided
			{
				$type = $filenameOrType;
			}
		}

        // args
        $args = $funcArgs;
				
		if (isset($filename)) // write to file
		{
			// determine filename
			if (is_null($filename))
			{
				$filename = $this->filename;
			}
		
			// determine file extension
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			
			// is supported type?
			if (!isset(self::$extensionToType[$extension]))
			{
				throw new Imaged_Exception('Unsupported image type (while writing)', 13);
			}
			
			// set type			
			$type = self::$extensionToType[$extension];
		}
		else // output to browser
		{
			// determine type
			if (is_null($type))
			{
				$type = $this->type;
			}			
		}
			
		// determine function
		$func = self::$typeToWriteFunc[$type];
		
		// func args
		$args = array_merge(array($this->ir, $filename), $args);		
				
		// get image
		$result = call_user_func_array($func, $args);
		
		return $this;
	}
	
	/**
	 * Undos all operations and reverts to original one from the start
	 * (state right after loading) 
	 * 
	 * @return Imaged
	 */
	public function restoreOriginal()
	{
		$this->ir = $this->orgIr;
		
		return $this;
	}		
	
	/**
     * Gets image resource
     *
	 * @return resource
	 */
	public function getResource()
	{
		return $this->ir;
	}
	
    /**
     * Sets image filename
     * 
     * @param string
     * @return Imaged
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

	/**
	 * Gets image filename
     *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	
	/**
	 * Gets image type (IMAGETYPE_*)
	 * 
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Gets image width
	 * 
	 * @return int
	 */
	public function getWidth()
	{
		return imagesx($this->ir);	
	}

	/**
	 * Gets image height
	 * 
	 * @return int
	 */
	public function getHeight()
	{
		return imagesy($this->ir);
	}
		
	/**
	 * Magic wrapper for GD image* functions
	 * 
	 * @param string
	 * @param array
	 * @return mixed Imaged|
	 */
	public function __call($method, $args = array())
	{
		// determine function name
		$func = 'image'.$method;
		if (!function_exists($func))
		{
			throw new Imaged_Exception('Unsupported method Imaged::'.$method.'()', 23);
		}
		
		// add image file handler at the beginning og args
		array_unshift($args, $this->ir);
		
		// execute function
		$result = call_user_func_array($func, $args);
		if (!is_bool($result))
		{
			return $result;
		}
		
		if ($result === false)
		{
			throw new Imaged_Exception('Operation failed - '.$func.'() returned false', 21);
		}
		
		return $this;
	}
	
	/**
	 * Converts object to string
	 *  
	 * @return string Image
	 */
	public function __toString()
	{
		ob_start();
		$this->write(); // outputs
		$output = ob_get_clean();
		
		return $output;
	}

	/**
	 * Destructor
     *
     * Frees resources from memory (if still present)
	 */
	public function __destruct()
	{
		if (is_resource($this->ir))
		{
			imagedestroy($this->ir);
		}		
		if (is_resource($this->orgIr))
		{
			imagedestroy($this->orgIr);
		}
	}	
	
}

/**
 * Imaged exception
 *
 * @package Imaged
 * @version SVN: $Id: $
 * @author Przemek Sobstel http://sobstel.org
 * @link http://segfaultlabs.com/imaged/
 */
class Imaged_Exception extends Exception
{
}
