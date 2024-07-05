<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Tools\File;
use Kodelines\Tools\Image;
use Kodelines\Exception\RuntimeException;
use Kodelines\Exception\ValidatorException;

class Upload {

  /**
   * contiene extensione file in upload
   *
   * @var string
   */
  private $extension;

  /**
   * contiene folder da caricare
   *
   * @var string
   */
  private $folder;

  /**
   * La base folder del caricamento, di default è uploads() su construct
   *
   * @var string
   */
  public $baseFolder;



  /**
   * Contiene configurazioni correnti sovrascrivibili da costruttore
   *
   * @var array
   */
  private $config = [];


  /**
   * Se a true settata da funsione imageOnly() prima di start blocca upload di files non immagini
   *
   * @var bool
   */
  private $imageOnly = false;

  /**
   * Contiene il filename per rinominare l'upload successivo
   *
   * @var string
   */
  private $fileName = null;

  /**
   * Se settato rimpiazza questo file con upload corrente se andato a buon fine
   *
   * @var string
   */
  private $replace = null;

  /**
   * Il costruttore carica configurazioni di default e possono essergli passati valori per sovrascrivere
   *
   * @param array $config
   */
  public function __construct($config = []) {
  
      //Assegno variabili config default sovrascrivibli con funzione config
      $this->config = array_replace_recursive(config('upload'),$config);

      $this->baseFolder = uploads();

  }

  /**
   * Inizia caricamento file in una cartella fa controlli e smista in base a tipo file
   *
   * @param mixed           $file       può contenere sia una stringa in base64 che un oggetto $_FILES['file']
   * @param string          $folder     path assoluta della cartella di destinazione del file
   * @return string               Ritorna funzione ma è sempre una stringa con il nome file se non trova una Exception
   */
  public function start($file, string $folder): string {

    //Controlla cartella destinazione e cartella temporanea
    $this->folder = $this->baseFolder  . $folder . '/';

    if (!is_dir($this->folder) || !is_writable($this->folder)) {
      throw new RuntimeException('Dir "'.$this->folder.'" does not exists or is not writeable');
    }

    //Se è una stringa controlla file base64
    if(is_string($file)) {
      return $this->base64($file);
    }

    return $this->file($file);

}


  /**
   * Carica un file da una stringa base64
   *
   * @param string $file
   * @return string 
   */
	private function base64(string $file): string {

		  if(!$uploaded = base64_decode(preg_replace('/data:(.*?);base64,/', '', $file))) {   
        throw new ValidatorException('trying_to_upload_wrong_base64'); 
      }

			if(!$mime_type = finfo_buffer(finfo_open(), $uploaded, FILEINFO_MIME_TYPE)) {
        throw new ValidatorException('file_mime_type_not_detected');
      }

			if(!$this->extension = File::mime2ext($mime_type)) {
				throw new ValidatorException('wrong_file_uploaded');
			}

      if(!$this->isAllowed()) {
        throw new ValidatorException('file_not_allowed');
      }

      if($this->isImage()) {

        //Return image operations 
        return $this->imageBase64($uploaded);

      }

      //Se non è immagine e c'è imageOnly attivo a questo punto muore
      if($this->imageOnly == true) {
        throw new ValidatorException('upload_only_images_allowed',$file);
      }

      if(!empty($this->fileName)) {
        $fileName = $this->fileName . '.' . $this->extension;
      } else {
        $fileName = uniqid('file-',true) .'.'. $this->extension; // rename file as a unique name
      }
      

			//move image to temp folder
			file_put_contents($this->folder . $fileName,$uploaded);

      if(!empty($this->replace) && file_exists($this->folder . $this->replace)) {
        unlink($this->folder . $this->replace);
      }  

	  	return $fileName;
	}


  /**
   * Upload a file from $_FILES['file'] return string with the name
   *
   * @param array $file
   * @return string
   */
  private function file(array $file): string {

    if(!isset($file['name']) || !isset($file['tmp_name'])) {
      throw new ValidatorException('wrong_file_uploaded');
    }

    if($file['size'] > $this->config('max_size')) {
      throw new ValidatorException('file_too_large');
    }
    

    if(!$this->extension = File::extension($file['name'],$file['name'])) {
      throw new ValidatorException('wrong_file_uploaded');
    }
    
    if(!$this->isAllowed()) {
      throw new ValidatorException('file_not_allowed',$file['name']);
    }

    //Check if is image
    if($this->isImage()) {

      //Return image operations 
      return $this->imageFile($file);

    }

    //Se non è immagine e c'è imageOnly attivo a questo punto muore
    if($this->imageOnly == true) {
      throw new ValidatorException('upload_only_images_allowed',$file['name']);
    }

    //rename filename if config need it
    if(!empty($this->fileName)) {
      $fileName = $this->fileName . '.' . $this->extension;
    } elseif($this->config['files']['random_names'] === true) {
      $fileName = uniqid('file-',true) . '.' . $this->extension;
    } else {
      $fileName = $file['name'];
    }

    //move image to temp folder
    move_uploaded_file($file['tmp_name'], $this->folder . $fileName);

    if(!empty($this->replace) && file_exists($this->folder . $this->replace)) {
      unlink($this->folder . $this->replace);
    }

    return $fileName;

  }

  /**
   * Carica immagine da $_FILES['file]
   *
   * @param array $file
   * @return string
   */
  private function imageFile(array $file): string {

       
      list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);

      //TODO: testare che succede a disabilitare random names e caricare file con stesso nome

      //rename image if config need it
      if(!empty($this->fileName)) {
        $imageName = $this->fileName . '.' . $this->extension;
      } elseif($this->config['images']['random_names'] === true) {
        $imageName = uniqid('img-',true) . '.' . $this->extension;
      } else {
        $imageName = $file['name'];
      }

			//Control file dimensions
			if (($width < $this->config['images']['minX']) || ($height < $this->config['images']['minY'])) {
				throw new ValidatorException('image_too_small',$file['name']);
			}

			if (($width > $this->config['images']['maxX']) || ($height > $this->config['images']['maxY'])) {
				throw new ValidatorException('image_too_large',$file['name']);
			}

		//Con generazione thumbnails controlla cartella e ridimensiona in base a thumbnails definiti
    if($this->config['images']['generate_thumbnails'] === true) {

      //Controllo cartella file temporanei
			if(!is_dir(_DIR_TEMP_) || !is_writable(_DIR_TEMP_)) {
				throw new RuntimeException('Dir temp does not exists or is not writeable');
			}

      //Dichiaro path file temporaneo per operazioni generazioni thumbnails
      $temporary = _DIR_TEMP_ . $imageName;

      //move image to temp folder
      move_uploaded_file($file['tmp_name'], $temporary);

      $thumbnails = new Thumbnails($this->config['images']['thumbnails']);

      //Genero thumbnails
      $thumbnails->generate(
        $temporary,
        $this->folder,
        $this->config['images']['resize_quality'],
        $this->config['images']['generate_webp']
      );

      //Elimino file da cartella temporanea
      unlink($temporary);

    } else {
      
      //move image to definitive folder
      move_uploaded_file($file['tmp_name'], $this->folder . $imageName);

      if($this->config['images']['generate_webp'] === true && $this->extension !== 'webp') {

        Image::convert(
          'webp',
          $this->folder . $imageName, 
          false, 
          $this->config['images']['resize_quality']
        );

      }
    
    }

    if(!empty($this->replace)) {
      $thumbnails->delete($this->replace,$this->folder);
    }

    return $imageName;

  }

  /**
   * Carica immagine da stringa base 64
   *
   * @param string $file
   * @return string
   */
  private function imageBase64(string $file): string {


      if(!$data = getimagesizefromstring($file)) {
        throw new ValidatorException('image_not_valid');
      }

      //Rinomina immagine con nome univoco casuale in quanto
      if(!empty($this->fileName)) {
        $imageName = $this->fileName . '.' . $this->extension;
      } else {
        $imageName = uniqid('img-',true) .'.'. $this->extension; 
      }
     
      //Control file dimensions
      if (($data[0] < $this->config['images']['minX']) || ($data[1] < $this->config['images']['minY'])) {
        throw new ValidatorException('image_too_small');
      }

      if (($data[0] > $this->config['images']['maxX']) || ($data[1] > $this->config['images']['maxY'])) {
        throw new ValidatorException('image_too_large');
      }


      //Con generazione thumbnails controlla cartella e ridimensiona in base a thumbnails definiti
    if($this->config['images']['generate_thumbnails'] === true) {

      //Controllo cartella file temporanei, il file base64 va salvato prima delle operzioni
			if(!is_dir(_DIR_TEMP_) || !is_writable(_DIR_TEMP_)) {
				throw new RuntimeException('Dir temp does not exists or is not writeable');
			}

      //Dichiaro path file temporaneo
      $temporary = _DIR_TEMP_ . $imageName;

      //move image to temp folder
      file_put_contents($temporary, $file);

      $thumbnails = new Thumbnails($this->config['images']['thumbnails']);

      //Genero thumbnails
      $thumbnails->generate(
        $temporary,
        $this->folder,
        $this->config['images']['resize_quality'],
        $this->config['images']['generate_webp']
      );

      //Elimino file da cartella temporanea 
      unlink($temporary);

    } else {
      
      //move image to definitive folder
      file_put_contents($this->folder . $imageName, $file);

      if($this->config['images']['generate_webp'] === true && $this->extension !== 'webp') {

        Image::convert(
          'webp',
          $this->folder . $imageName, 
          false, 
          $this->config['images']['resize_quality']
        );

      }
    
    }

    if(!empty($this->replace)) {
      $thumbnails->delete($this->replace,$this->folder);
    }
 

    return $imageName;

}

  /**
   * Set $this image only param
   *
   * @return object
   */
  public function imageOnly(): object {

    $this->imageOnly = true;

    return $this;

  }

  /**
   * Fast config override before call start like $uploads->config(['images' => ['generate_thumbnails' => false, 'generate_webp' => false]])->start()
   *
   * @param array $config
   * @return object
   */
  public function config($config = []): object {

    $this->config = array_replace_recursive($this->config,$config);

    return $this;

  }

  /**
   * Setta il file name per l'upload successivo, se c'è l'estensione viene rimossa
   *
   * @parm strin $fileName Il nome del file senza estensione
   * @return object
   */
  public function fileName(string $fileName): object {

    $this->fileName = File::name($fileName);

    return $this;

  }

  /**
   * Check if the file is a image
   *
   * @return boolean
   */
  private function isImage() {
    return in_array($this->extension,['jpg','jpeg','png','webp','gif']);
  }

  /**
   * Check if file upload is allowed by file extentions in app config
   *
   * @return boolean
   */
  private function isAllowed() { 
    return in_array($this->extension,$this->config['allowed']);
  }

  /**
   * Create a new static upload instance
   *
   * @param array $config
   * @return object
   */
  public static function create($config = []): object {

    $instance = new Upload($config);

    return $instance;

  }

  /**
   * Setta un replace per l'upload corrente
   *
   * @param string $file
   * @return object
   */
  public function replace(string|null $file): object {

    $this->replace = $file;

    return $this;
  }

  /**
   * Setta la base folder diversa da uploads()
   *
   * @param string $folder
   * @return object
   */
  public function setBaseFolder(string $folder): object {

    $this->baseFolder = $folder;

    return $this;
  }


}

?>