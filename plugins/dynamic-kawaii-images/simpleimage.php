<?php
// By Andrew Salko, based on article: 
// http://sanchiz.net/blog/resizing-images-with-php

class SimpleImage 
{
	var $image;
	var $image_type;
 	 
	function free()
	{
		imagedestroy($image);
	}

	function load($filename) 
	{
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		if( $this->image_type == IMAGETYPE_JPEG ) 
		{
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ) 
		{
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ) 
		{
			$this->image = imagecreatefrompng($filename);
		}
	}//load

	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) 
	{
		if( $image_type == IMAGETYPE_JPEG ) 
		{
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) 
		{
			imagegif($this->image,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) 
		{
			imagepng($this->image,$filename);
		}

		if( $permissions != null) 
		{
			chmod($filename,$permissions);
		}
	}//save


	function output($image_type=IMAGETYPE_JPEG) 
	{
		if( $image_type == IMAGETYPE_JPEG ) 
		{
			imagejpeg($this->image);
		} elseif( $image_type == IMAGETYPE_GIF ) 
		{
			imagegif($this->image);
		} elseif( $image_type == IMAGETYPE_PNG ) 
		{
			imagepng($this->image);
		}
	}//output

	function getWidth() 
	{
		return imagesx($this->image);
	}//getWidth

	function getHeight() 
	{
		return imagesy($this->image);
	}//getHeight

	function resizeToHeight($height) 
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}//resizeToHeight

	function resizeToWidth($width) 
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}

	function scale($scale) 
	{
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height);
	}//scale

	function resize($width,$height) 
	{
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->free();//clean-up current image
		$this->image = $new_image;
	}//resize

	//
	// Cuts image height to given size (from below) and resize result
	//
	function CutByHeightAndResize($cutHeight, $destWidth, $destHeight)
	{
		$new_image = imagecreatetruecolor($destWidth, $destHeight);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $destWidth, $destHeight, $this->getWidth(), $cutHeight);
		$this->free();//clean-up current image
		$this->image = $new_image;
	}

	//
	// Cuts image width and and resize result 
	// This function applied if we cannot use CutByHeightAndResize
	function CutByWidthAndResize($destWidth, $destHeight)
	{
		$new_image = imagecreatetruecolor($destWidth, $destHeight);

		//нужно понять, сколько обрезать с боков чтобы сохранить 
		//высоту и пропорции
		$koeffDest=$destWidth/$destHeight;//240/400=0.6

		$idealWidth=(int)($this->getHeight()*$koeffDest);//960*0.6 = 576
		$leftCut=(int)($this->getWidth()-$idealWidth)/2;//(640-576)/2=32  делим на два чтобы слева-справа одинаковый отступ

		//int imagecopyresampled (resource dst_im, resource src_im, int dstX, int dstY, int srcX, int srcY, int dstW, int dstH, int srcW, int srcH)
		imagecopyresampled($new_image, $this->image, 0, 0, $leftCut, 0, $destWidth, $destHeight, $idealWidth, $this->getHeight());
        $this->free();//clean-up current image
		$this->image = $new_image;		
	}//CutByWidthAndResize

}//SimpleImage

