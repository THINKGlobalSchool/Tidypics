<?php
	/**
	 * Elgg tidypics library of resizing functions
	 * 
	 */

	 
	/**
	 * Create thumbnails using PHP GD Library
	 *
	 * @param ElggFile holds the image that was uploaded
	 * @param string   folder to store thumbnail in
	 * @param string   name of the thumbnail
	 * @return bool    true on success
	 */
	function tp_create_gd_thumbnails($file, $prefix, $filestorename)
	{
		global $CONFIG;
		
		$mime = $file->getMimeType();
		
		$image_sizes = get_plugin_setting('image_sizes', 'tidypics');
		if (!$image_sizes) {
			register_error(elgg_echo('tidypics:nosettings'));
			return false;
		}
		$image_sizes = unserialize($image_sizes);
		
		// Generate thumbnails
		$thumbnail = tp_gd_resize(	$file->getFilenameOnFilestore(),
									$image_sizes['thumb_image_width'],
									$image_sizes['thumb_image_height'], 
									true); 

		if ($thumbnail) {
			$thumb = new ElggFile();
			$thumb->setMimeType($mime);
			$thumb->setFilename($prefix."thumb".$filestorename);
			$thumb->open("write");
			if ($thumb->write($thumbnail)) {
				$file->thumbnail = $prefix."thumb".$filestorename;
			} else {
				$thumb->delete();
			}
			$thumb->close();
			unset($thumb);
		}
		unset($thumbnail);
		
		$thumbsmall = tp_gd_resize(	$file->getFilenameOnFilestore(),
									$image_sizes['small_image_width'],
									$image_sizes['small_image_height'], 
									true); 

		
		if ($thumbsmall) {
			$thumb = new ElggFile();
			$thumb->setMimeType($mime);
			$thumb->setFilename($prefix."smallthumb".$filestorename);
			$thumb->open("write");
			if ($thumb->write($thumbsmall)) {
				$file->smallthumb = $prefix."smallthumb".$filestorename;
			} else {
				$thumb->delete();
			}
			$thumb->close();
			unset($thumb);
		}
		unset($thumbsmall);

		$thumblarge = tp_gd_resize(	$file->getFilenameOnFilestore(),
									$image_sizes['large_image_width'],
									$image_sizes['large_image_height'], 
									false); 
		
		if ($thumblarge) {
			$thumb = new ElggFile();
			$thumb->setMimeType($mime);
			$thumb->setFilename($prefix."largethumb".$filestorename);
			$thumb->open("write");
			if ($thumb->write($thumblarge)) {
				$file->largethumb = $prefix."largethumb".$filestorename;
			} else {
				$thumb->delete();
			}
			$thumb->close();
			unset($thumb);
		}
		unset($thumblarge);

		return true;
	}

	/**
	 * Gets the jpeg contents of the resized version of an already uploaded image - original from Elgg filestore.php 
	 * (Returns false if the uploaded file was not an image)
	 *
	 * @param string $input_name The name of the file input field on the submission form
	 * @param int $maxwidth The maximum width of the resized image
	 * @param int $maxheight The maximum height of the resized image
	 * @param true|false $square If set to true, will take the smallest of maxwidth and maxheight and use it to set the dimensions on all size; the image will be cropped.
	 * @return false|mixed The contents of the resized image, or false on failure
	 */
	function tp_gd_resize($input_name, $maxwidth, $maxheight, $square = false, $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0) {
		
		// Get the size information from the image
		if ($imgsizearray = getimagesize($input_name)) {
		
			// Get width and height
			$width = $imgsizearray[0];
			$height = $imgsizearray[1];
			$newwidth = $width;
			$newheight = $height;
			
			// Square the image dimensions if we're wanting a square image
			if ($square) {
				if ($width < $height) {
					$height = $width;
				} else {
					$width = $height;
				}
				
				$newwidth = $width;
				$newheight = $height;
				
			}
			
			if ($width > $maxwidth) {
				$newheight = floor($height * ($maxwidth / $width));
				$newwidth = $maxwidth;
			}
			if ($newheight > $maxheight) {
				$newwidth = floor($newwidth * ($maxheight / $newheight));
				$newheight = $maxheight; 
			}
			
			$accepted_formats = array(
											'image/jpeg' => 'jpeg',
											'image/png' => 'png',
											'image/gif' => 'gif'
									);
			
			// If it's a file we can manipulate ...
			if (array_key_exists($imgsizearray['mime'],$accepted_formats)) {

				$function = "imagecreatefrom" . $accepted_formats[$imgsizearray['mime']];
				$newimage = imagecreatetruecolor($newwidth,$newheight);
				
				if (is_callable($function) && $oldimage = $function($input_name)) {
 				
					// Crop the image if we need a square
					if ($square) {
						if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
							$widthoffset = floor(($imgsizearray[0] - $width) / 2);
							$heightoffset = floor(($imgsizearray[1] - $height) / 2);
						} else {
							$widthoffset = $x1;
							$heightoffset = $y1;
							$width = ($x2 - $x1);
							$height = $width;
						}
					} else {
						if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
							$widthoffset = 0;
							$heightoffset = 0;
						} else {
							$widthoffset = $x1;
							$heightoffset = $y1;
							$width = ($x2 - $x1);
							$height = ($y2 - $y1);
						}
					}
					
					if ($square) {
						$newheight = $maxheight;
						$newwidth = $maxwidth;
					}
					
					imagecopyresampled($newimage, $oldimage, 0,0,$widthoffset,$heightoffset,$newwidth,$newheight,$width,$height);
					
					ob_start();
					imagejpeg($newimage, null, 90);
					$jpeg = ob_get_clean();
					return $jpeg;
					
				}
				
			}
			
		}
			
		return false;
	}


	/**
	 * Create thumbnails using PHP ImageMagick Library
	 *
	 * @param ElggFile holds the image that was uploaded
	 * @param string   folder to store thumbnail in
	 * @param string   name of the thumbnail
	 * @return bool    true on success 
	 */
	function tp_create_imagick_thumbnails($file, $prefix, $filestorename)
	{
		$image_sizes = get_plugin_setting('image_sizes', 'tidypics');
		if (!$image_sizes) {
			register_error(elgg_echo('tidypics:nosettings'));
			return false;
		}
		$image_sizes = unserialize($image_sizes);

		$thumb = new ElggFile();


		// tiny thumbnail
		$thumb->setFilename($prefix."thumb".$filestorename);
		$thumbname = $thumb->getFilenameOnFilestore();
		$rtn_code = tp_imagick_resize(	$file->getFilenameOnFilestore(),
										$thumbname,
										$image_sizes['thumb_image_width'],
										$image_sizes['thumb_image_height'], 
										true);
		if (!$rtn_code)
			return false;
		$file->thumbnail = $prefix."thumb".$filestorename;


		// album thumbnail
		$thumb->setFilename($prefix."smallthumb".$filestorename);
		$thumbname = $thumb->getFilenameOnFilestore();
		$rtn_code = tp_imagick_resize(	$file->getFilenameOnFilestore(),
										$thumbname,
										$image_sizes['small_image_width'],
										$image_sizes['small_image_height'], 
										true); 
		if (!$rtn_code)
			return false;
		$file->smallthumb = $prefix."smallthumb".$filestorename;


		// main image
		$thumb->setFilename($prefix."largethumb".$filestorename);
		$thumbname = $thumb->getFilenameOnFilestore();
		$rtn_code = tp_imagick_resize(	$file->getFilenameOnFilestore(),
										$thumbname,
										$image_sizes['large_image_width'],
										$image_sizes['large_image_height'], 
										false); 
		if (!$rtn_code)
			return false;
		$file->largethumb = $prefix."largethumb".$filestorename;

		unset($thumb);

		return true;
	}

	
	/**
	 * Resize using PHP ImageMagick Library
	 *
	 * Gets the jpeg contents of the resized version of an already uploaded image
	 * (Returns false if the uploaded file was not an image)
	 *
	 * @param string $input_name The name of the file input field on the submission form
	 * @param string $output_name The name of the file to be written
	 * @param int $maxwidth The maximum width of the resized image
	 * @param int $maxheight The maximum height of the resized image
	 * @param true|false $square If set to true, will take the smallest of maxwidth and maxheight and use it to set the dimensions on all size; the image will be cropped.
	 * @return false|mixed The contents of the resized image, or false on failure
	 */
	function tp_imagick_resize($input_name, $output_name, $maxwidth, $maxheight, $square = false, $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0) {
		
		// Get the size information from the image
		$imgsizearray = getimagesize($input_name);
		if (!$imgsizearray)
			return false;
		

		// Get width and height
		$width = $imgsizearray[0];
		$height = $imgsizearray[1];
		$newwidth = $width;
		$newheight = $height;
		
		// initial guess at final dimensions for new image (doesn't check for squareness yet
		if ($newwidth > $maxwidth) {
			$newheight = floor($newheight * ($maxwidth / $newwidth));
			$newwidth = $maxwidth;
		}
		if ($newheight > $maxheight) {
			$newwidth = floor($newwidth * ($maxheight / $newheight));
			$newheight = $maxheight; 
		}
		
		// Handle squareness for both original and new image
		if ($square) {
			if ($width < $height) {
				$height = $width;
			} else {
				$width = $height;
			}
			
			if ($maxheight == $maxwidth) {
				// if input arguments = square, no need to use above calculations (which can have round-off errors)
				$newwidth = $maxwidth;
				$newheight = $maxheight;
			} else {
				if ($newwidth < $newheight) {
					$newheight = $newwidth;
				} else {
					$newwidth = $newheight;
				}
			}
		}
		
		
		// Crop the original image - this needs to be checked over
		if ($square) {
			if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
				$xoffset = floor(($imgsizearray[0] - $width) / 2);
				$yoffset = floor(($imgsizearray[1] - $height) / 2);
			} else { // assume we're being passed good croping coordinates
				$xoffset = $x1;
				$yoffset = $y1;
				$width = ($x2 - $x1);
				$height = $width;
			}
		} else {
			if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
				$xoffset = 0;
				$yoffset = 0;
			} else {
				$xoffset = $x1;
				$yoffset = $y1;
				$width = ($x2 - $x1);
				$height = ($y2 - $y1);
			}
		}


		try {
			$img = new Imagick($input_name);
		} catch (ImagickException $e) {
			return false;
		}
		
		$img->cropImage($width, $height, $xoffset, $yoffset);
		
		// use the default IM filter (windowing filter), I think 1 means default blurring or number of lobes
		$img->resizeImage($newwidth, $newheight, imagick::FILTER_LANCZOS, 1);
		$img->setImagePage($newwidth, $newheight, 0, 0);
		
		if ($img->writeImage($output_name) != true) {
			$img->destroy();
			return false;
		}
		
		$img->destroy();
		
		return true;
	}

	/**
	 * Create thumbnails using ImageMagick executables
	 *
	 * @param ElggFile holds the image that was uploaded
	 * @param string   folder to store thumbnail in
	 * @param string   name of the thumbnail
	 * @return bool    true on success 
	 */
	function tp_create_imagick_cmdline_thumbnails($file, $prefix, $filestorename)
	{
		global $CONFIG;
		
		$mime = $file->getMimeType();
		
		$image_sizes = get_plugin_setting('image_sizes', 'tidypics');
		if (!$image_sizes) {
			register_error(elgg_echo('tidypics:nosettings'));
			return array();
		}
		$image_sizes = unserialize($image_sizes);
		
		$thumblarge = tp_imagick_cmdline_resize($file->getFilenameOnFilestore(), 
								"largethumb", 
								$image_sizes['large_image_width'], 
								$image_sizes['large_image_height'], 
								false); 
									
		$thumbsmall = tp_imagick_cmdline_resize($file->getFilenameOnFilestore(), 
								"smallthumb", 
								$image_sizes['small_image_width'], 
								$image_sizes['small_image_height'], 
								true); 

		$thumbnail = tp_imagick_cmdline_resize($file->getFilenameOnFilestore(), 
								"thumb", 
								$image_sizes['thumb_image_width'], 
								$image_sizes['thumb_image_height'], 
								true);
		
		if ($thumbnail) {
			$thumb = new ElggFile();
			$thumb->setMimeType($mime);
			$thumb->setFilename($prefix."thumb".$filestorename);
			$file->thumbnail = $prefix."thumb".$filestorename;
		}
		
		if ($thumbsmall) {
			$thumb = new ElggFile();
			$thumb->setMimeType($mime);
			$thumb->setFilename($prefix."smallthumb".$filestorename);
			$file->smallthumb = $prefix."smallthumb".$filestorename;
		}
		
		if ($thumblarge) {
			$thumb = new ElggFile();
			$thumb->setMimeType($mime);
			$thumb->setFilename($prefix."largethumb".$filestorename);
			$file->largethumb = $prefix."largethumb".$filestorename;
		}

		return array(	"thumbnail" => $thumbnail,
						"thumbsmall" => $thumbsmall,
						"thumblarge" => $thumblarge);
	}

	/*
	 * Gets the jpeg contents of the resized version of an already uploaded image
	 * (Returns false if the uploaded file was not an image)
	 *
	 * @param string $input_name The name of the file input field on the submission form
	 * @param string $prefix The text to prefix to the existing filename
	 * @param int $maxwidth The maximum width of the resized image
	 * @param int $maxheight The maximum height of the resized image
	 * @param true|false $square If set to true, will take the smallest of maxwidth and maxheight and use it to set the dimensions on all size; the image will be cropped.
	 * @return false|mixed The contents of the resized image, or false on failure
	 */
	function tp_imagick_cmdline_resize($input_name, $prefix, $maxwidth, $maxheight, $square = false, $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0) {

		$params = array(
			"input_name"=>$input_name,
			"output_name"=>$output_name,
			"maxwidth"=>$maxwidth,
			"maxheight"=>$maxheight,
			"square"=>$square,
			"x1"=>$x1,
			"y1"=>$y1,
			"x2"=>$x2,
			"y2"=>$y2);
		
		$path = pathinfo($input_name);
		$output_name = $path["dirname"] . "/$prefix" . $path["filename"] . "." . $path["extension"];
		
		// Get the size information from the image
		if ($imgsizearray = getimagesize($input_name)) {

			// Get width and height
			$width = $imgsizearray[0];
			$height = $imgsizearray[1];
			$newwidth = $width;
			$newheight = $height;
			
			// Square the image dimensions if we're wanting a square image
			if ($square) {
				if ($width < $height) {
					$height = $width;
				} else {
					$width = $height;
				}
				
				$newwidth = $width;
				$newheight = $height;
				
			}

			if ($width > $maxwidth) {
				$newheight = floor($height * ($maxwidth / $width));
				$newwidth = $maxwidth;
			}
			if ($newheight > $maxheight) {
				$newwidth = floor($newwidth * ($maxheight / $newheight));
				$newheight = $maxheight;
			}

			$accepted_formats = array(
										'image/jpeg' => 'jpeg',
										'image/png' => 'png',
										'image/gif' => 'gif'
										);
			// If it's a file we can manipulate ...
			if (array_key_exists($imgsizearray['mime'],$accepted_formats)) {

				// Crop the image if we need a square
				if ($square) {
					if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
						$widthoffset = floor(($imgsizearray[0] - $width) / 2);
						$heightoffset = floor(($imgsizearray[1] - $height) / 2);
					} else {
						$widthoffset = $x1;
						$heightoffset = $y1;
						$width = ($x2 - $x1);
						$height = $width;
					}
				} else {
					if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
						$widthoffset = 0;
						$heightoffset = 0;
					} else {
						$widthoffset = $x1;
						$heightoffset = $y1;
						$width = ($x2 - $x1);
						$height = ($y2 - $y1);
					}
				}
				
				// Resize and return the image contents!
				if ($square) {
					$newheight = $maxheight;
					$newwidth = $maxwidth;
				}
				$im_path = get_plugin_setting('convert_command', 'tidypics');
				if(!$im_path) {
					$im_path = "/usr/bin/";
				}
				if(substr($im_path, strlen($im_path)-1, 1) != "/") $im_path .= "/";
				$command = $im_path . "convert \"$input_name\" -resize ".$newwidth."x".$newheight."^ -gravity center -extent ".$newwidth."x".$newheight." \"$output_name\"";
				system($command);
				return $output_name;

			}
		}

		return false;
	}

?>