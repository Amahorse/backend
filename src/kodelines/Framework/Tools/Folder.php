<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Folder {

  /**
   * Clear all files in entire directory
   *
   * @param string $directory
   * @param boolean $mantainbase  if true keep system files
   * @return boolean
   */
  public static function clear(string $directory, bool $mantainbase = false):bool {

    if(mb_substr($directory,-1) == "/") {
      $directory = mb_substr($directory,0,-1);
    }


    if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
      return false;
    }

    $directoryHandle = opendir($directory);

    while ($contents = readdir($directoryHandle)) {

      //Skip index and htaccess
      if(($mantainbase) && (($contents == 'index.php') or ($contents == '.htaccess') or ($contents == '.gitignore'))) {continue;}

        if($contents != '.' && $contents != '..') {

          $path = $directory . "/" . $contents;

          if(is_dir($path)) {
  					self::Clear($path);
  					rmdir($path);
  				} else {
  					@unlink($path);
  				}

        }
    }

    closedir($directoryHandle);

    return true;
  }


  /**
   * Read a folder content and return array of file if exists
   *
   * @param string $directory
   * @return boolean|array
   */
  public static function read(string $directory): bool|array {

    // create an array to hold directory list
    $results = array();

    if(!is_dir($directory)) {
      return false;
    }

    // create a handler for the directory
    if(!$handler = opendir($directory)) {
      return [];
    }

    // open directory and walk through the filenames
    while ($content = readdir($handler))  {

      if($content == '..' || $content == '.') {
        continue;
      }

      $results[] = $content;
    }

  // tidy up: close the handler
  closedir($handler);

  // done!
  return $results;
  }

  /**
   * Create a folder
   *
   * @param string $path
   * @param string $folder
   * @param integer $permissions
   * @param boolean $index  
   * @return bool
   */
  public static function create(string $path, string $folder, $permissions = 0777, $index = false):bool {

    if(!Str::endsWith($path,'/')) {
      $path .= '/';
    }

    if(!is_writeable($path)) {
      return false;
    }

    if(is_dir($path . $folder) || file_exists($path . $folder)) {
      return true;
    }

    if (!mkdir($path . $folder, $permissions, true)) {
      return false;
    }

    if($index === true) {
      file_put_contents($path .  $folder . '/index.php','');
    }

    return true;

  }

  
  /**
   * Delete a folder
   *
   * @param string $directory
   * @return void
   */
  public static function delete(string $directory):bool {

    if(Str::endsWith($directory,'/')) {
      $directory = mb_substr($directory,0,-1);
    }

    if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)) {
      return false;
    }

    self::clear($directory);

    rmdir($directory);


    return true;
  }

  /**
   * Copy folder and content
   *
   * @param string $directory
   * @return void
   */
  public static function copy(string $sourceDirectory, string $destinationDirectory, string $childFolder = ''): void {
    $directory = opendir($sourceDirectory);

    if (is_dir($destinationDirectory) === false) {
        mkdir($destinationDirectory);
    }

    if ($childFolder !== '') {
        if (is_dir("$destinationDirectory/$childFolder") === false) {
            mkdir("$destinationDirectory/$childFolder");
        }

        while (($file = readdir($directory)) !== false) {

            if ($file === '.' || $file === '..') {
                continue;
            }

            if(Str::startsWith($file,'._')) {
              continue;
            }

            if (is_dir("$sourceDirectory/$file") === true) {
                self::copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            } else {
                copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            }
        }

        closedir($directory);

        return;
    }

    while (($file = readdir($directory)) !== false) {
      
        if ($file === '.' || $file === '..') {
            continue;
        }
        if(Str::startsWith($file,'._')) {
          continue;
        }


        if (is_dir("$sourceDirectory/$file") === true) {
            self::copy("$sourceDirectory/$file", "$destinationDirectory/$file");
        }
        else {
          if(file_exists("$sourceDirectory/$file")) {
            copy("$sourceDirectory/$file", "$destinationDirectory/$file");
          }
        }
    }

    closedir($directory);
  }


}

?>