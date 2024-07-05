<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Image
{

	
	/**
	 * Image types constants
	 *
	 * @var array
	 */
	private static $constImageFormat = [
		IMAGETYPE_GIF => 'gif',
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_PNG => 'png',
		IMAGETYPE_WEBP => 'webp'
	];


	/**
	 * Get width, height, file name, if webp exists
	 *
	 * @param string $path
	 * @return array
	 */
	public static function getAttributes(string $path): array 
	{

		$values = [];

		if(!empty($path) && file_exists($path) && $extension = self::extension($path)) {


			//Create Image
			switch($extension) {
	
				case 'jpeg' : $image = imagecreatefromjpeg($path); $values['alpha'] = false; break;
	
				case 'jpg': $image = imagecreatefromjpeg($path); $values['alpha'] = false; break;
	
				case 'png': $image = imagecreatefrompng($path); $values['alpha'] = true; break;
	
				case 'gif': $image = imagecreatefromgif($path); $values['alpha'] = true; break;
	
				case 'webp': $image = imagecreatefromwebp($path); $values['alpha'] = true; break;
	
				default : $image = null;
	
			}

			if(!empty($image)) {

				//Start Resizing
				$values['width'] = imagesx($image);

				$values['height'] = imagesy($image);

				//Clean up / Destroy the image object.
				imagedestroy($image);

			}

		} 

		return $values;
	}

	/**
	 * Converte immagine in vari formati
	 *
	 * @param string $to			Formato da convertire
	 * @param string $imagePath		Indirizzo file immagine
	 * @param boolean $replace		Rimpiazza immagine originale o no
	 * @param integer $quality		Ridimensionamento qualità opzionale
	 * @return void
	 */
	public static function convert(string $to, string $imagePath, bool $replace = false, int $quality = 100): bool
	{

		if (!file_exists($imagePath) || !$extension = self::extension($imagePath)) {
			return false;
		}
	
		//Create Image
		switch($extension) {

			case 'jpeg' : $image = imagecreatefromjpeg($imagePath); break;

			case 'jpg': $image = imagecreatefromjpeg($imagePath); break;

			case 'png': $imageAlpha = imagecreatefrompng($imagePath); break;

			case 'gif': $imageAlpha = imagecreatefromgif($imagePath); break;

			case 'webp': $imageAlpha = imagecreatefromwebp($imagePath); break;

			default : return false;

		}

		


		//Replace extension (the file extension could be different from the real)
		$newImagePath = str_replace(File::extension($imagePath), $to, $imagePath);


		//Transparent image needs different treatment for transparency
		if(isset($imageAlpha)) {

			if(!$imageAlpha) {
				return false;
			}

			$w = imagesx($imageAlpha);
			$h = imagesy($imageAlpha);

			// create a canvas

			$image = imagecreatetruecolor ($w, $h);
			imageAlphaBlending($image, false);
			imageSaveAlpha($image, true);

			// By default, the canvas is black, so make it transparent

			$trans = imagecolorallocatealpha($image, 0, 0, 0, 127);
			imagefilledrectangle($image, 0, 0, $w - 1, $h - 1, $trans);

			// copy png to canvas

			imagecopy($image, $imageAlpha, 0, 0, 0, 0, $w, $h);

		}

		if (!isset($image) || $image == false) {
			return false;
		}	

		switch($to) {

			case 'jpeg' : imagejpeg($image, $newImagePath, $quality); break;

			case 'jpg': imagejpeg($image, $newImagePath, $quality); break;

			case 'png': 

				//Png needs quality divided
				$quality = $quality / 10;
				
				imagepng($image, $newImagePath, $quality);
				
				break;

			case 'gif': imagegif($image, $newImagePath, $quality); break;

			case 'webp': imagewebp($image, $newImagePath, $quality); break;

			default : return false;

		}

		//Clean up / Destroy the image object.
		imagedestroy($image);


		//If replace is active delete the old file
		if ($replace) {
			unlink($imagePath);
		}



		return true;
	}

	/**
	 * Ridimensiona immagine mantenendo proporzioni con max x e max y
	 *
	 * @param string $imagePath
	 * @param integer $maxX
	 * @param integer $maxY
	 * @param string $newImagePath
	 * @param integer $quality
	 * @return void
	 */
	public static function resize(string $imagePath, int $maxX, int $maxY, string $newImagePath, int $quality = 100): bool {

		if(!file_exists($imagePath) || !$extension = self::extension($imagePath)) {
			return false;
		}

		//Create Image
		switch($extension) {

			case 'jpeg' : $image = imagecreatefromjpeg($imagePath); break;

			case 'jpg': $image = imagecreatefromjpeg($imagePath); break;

			case 'png': $image = imagecreatefrompng($imagePath); break;

			case 'gif': $image = imagecreatefromgif($imagePath); break;

			case 'webp': $image = imagecreatefromwebp($imagePath); break;

			default : return false;

		}

		if(!$image) {
			return false;
		}

		//Start Resizing
		$originalX = imagesx($image);

		$originalY = imagesy($image);

		if ($originalX / $originalY > $maxX / $maxY) {
			$newX = (int)round($maxX,0);
			$newY = (int)round(($originalY / $originalX ) * $maxX,0);
		} else {
			$newX = (int)round(($originalX / $originalY) * $maxY,0);
			$newY = (int)round($maxY,0);
		}

		$newImage = imagecreatetruecolor($newX, $newY);

		if($extension == "png" || $extension=="gif" || $extension == "webp") {
			imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
			imagealphablending($newImage, false);
			imagesavealpha($newImage, true);
		}

		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newX, $newY, $originalX, $originalY);


		//Save Image
		switch($extension) {

			case 'jpeg' : imagejpeg($newImage, $newImagePath, $quality); break;

			case 'jpg': imagejpeg($newImage, $newImagePath, $quality); break;

			case 'png': 

				//Png needs quality divided
			    $quality = $quality / 10; 
				
				imagepng($newImage, $newImagePath, $quality); 
				
				break;

			case 'gif': imagegif($newImage, $newImagePath, $quality); break;

			case 'webp': imagewebp($newImage, $newImagePath, $quality);

			default : return false;

		}

		//Clean up / Destroy the image object.
		imagedestroy($image);

		imagedestroy($newImage);

		return true;
	


	}


	/**
	 * Given specific $path to detect current image extension
	 *
	 * @param string $path
	 * @return bool|string
	 */
    private static function extension(string $path):bool|string
    {
        $extension = exif_imagetype($path);

        if (!array_key_exists($extension, self::$constImageFormat)) {
            return false;
        }

        return self::$constImageFormat[$extension];
    }


	/**
	 * Rigenera massivamente file webp dentro una cartella anche ricorsivamente
	 *
	 * @param string $folder
	 * @return mixed
	 */
	public static function generateWebp(string $folder):void {

		$dir = $folder . '/';    

		foreach(Folder::read($dir) as $file) {

		  if(is_dir($dir . $file . '/')) {

			self::generateWebp($dir . $file);

			continue;
		  }
	
		  if(File::isImage($file)) {
	
			$filename = File::name($file);
	
			if(!file_exists($dir  . $filename . '.webp')) {
			  self::convert('webp', $dir . $file, false, 90); 
			}
		   
		  }
	
	  
		}
	
	
	}

	
}

?>