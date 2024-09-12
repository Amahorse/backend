<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Tools\File;
use Kodelines\Tools\Image;
use Kodelines\Tools\Folder;
use Kodelines\Exception\RuntimeException;

class Thumbnails
{

  /**
   * Contiene configurazioni correnti sovrascrivibili da costruttore
   *
   * @var array
   */
  private $config = [];


  /**
   * Varabile statica per evtare check di cartelle già controllate in caso di upload massivi
   *
   * @var array
   */
  private static $checkedFolders = [];


  /**
   * Il costruttore carica configurazioni di default e possono essergli passati valori per sovrascrivere
   *
   * @param array $config
   */
  public function __construct($config = []) {
  
	//Assegno variabili config default sovrascrivibli con funzione config
	$this->config = array_merge(config('thumbnails'),$config);


	return $this;

  }

  
  /**
   * Controlla se esistono e crea le cartelle per le thumbnails in caso non esistessero
   *
   * @param string $baseFolder	cartella base destinazione
   * @return bool
   */
  public function checkFolders(string $baseFolder) {

	//Controllo in array checcked folders per vedere se è già stata controllata
	if(in_array($baseFolder,self::$checkedFolders)) {
		return true;
	}

	//Base directory check
	if(!is_dir($baseFolder) || !is_writeable($baseFolder)) {
		return false;	
	}

	//Original directory check
	if(!is_dir($baseFolder . 'original')) { 
		if(!Folder::create($baseFolder, 'original',0777,true)) {
			return false;
		}
	}
	
	//Thumbnails directory check
	foreach($this->config as $folder => $params) {

		if(!is_dir($baseFolder .  $folder)) {
			if(!Folder::create($baseFolder, $folder,0777,true)) {
				return false;
			}
		}
	}

	//Faccio push in array cartelle controllate
	array_push(self::$checkedFolders,$baseFolder);

	return true;

  }

  	  /**
	   * Genera le thumbnails
	   *
	   * @param string 	$image 			path immagine
	   * @param string  $folder 		cartella dove salvare le thumbnails
	   * @param integer $quality 		qualità del resize
	   * @param boolean $generateWebp	opzione per definire se generare anche versione webp
	   * @return bool
	   */
	public function generate(string $image,string $folder, int $quality = 100, bool $generateWebp = false) {


		//Controllo cartelle
		if(!$this->checkFolders($folder)) {
			throw new RuntimeException('Folders for uploads does not exists and cannot be created');
		}

		if(!file_exists($image)) { 
			throw new RuntimeException('File "'.$image.'" for thumbnails does not exists');
		}

		$imageName = basename($image);


		//Copy to original folder, control if set because in regenerate we dont need it
		if(!file_exists($folder . 'original/' . $imageName)) {
			copy($image, $folder . 'original/' . $imageName);
		}

		if($generateWebp && !file_exists($folder . 'original/' . File::name($imageName) . '.webp')) {
			Image::convert('webp', $folder . 'original/' . $imageName, false, $quality);
		}

		//Resize image
		foreach ($this->config as $type => $dimension) {
			
			Image::resize($image, $dimension['x'], $dimension['y'], $folder . $type . '/'  . $imageName,$quality);

			if($generateWebp) {
				Image::convert('webp', $folder . $type . '/' . $imageName, false, $quality);
			}
		}

		return true;

	}



	/**
	 * Cancella thumbnails dentro le varie cartelle
	 *
	 * @param string $image		Nome immagine dentro la cartella
	 * @param string $folder	dove salvare le immagini
	 * @return void
	 */
	public function delete(mixed $image, string $folder)
	{

		if(empty($image)) {
			return false;
		}

		$directory = _DIR_UPLOADS_ . $folder;

		$webp = File::name($image) . '.webp';

		foreach($this->config as $type => $size) {

			if (file_exists($directory  . $type . '/' . $image)) {
				unlink($directory  . $type . '/' . $image);
			}

			if (file_exists($directory  . $type . '/' . $webp)) {
				unlink($directory  . $type . '/' . $webp);
			}

		}

		if (file_exists($directory . 'original/' . $image)) {
			unlink($directory . 'original/' . $image);
		}

		if (file_exists($directory . 'original/' . $webp)) {
			unlink($directory . 'original/' . $webp);
		}

	}


	

	
}

?>