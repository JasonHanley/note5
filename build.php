<?php

function addFiles(&$files, $pattern, $autoversion = false) {
  $abspath = dirname(__FILE__).'/web';
  $abspathLen = strlen($abspath)+1;
  $filesToAdd = glob($abspath.'/'.$pattern);
  
  foreach($filesToAdd as $filename) {
    $newfilename = $filename;
    $path = pathinfo($filename);
    if($autoversion) {
      $ver = '..'.filemtime($filename).'.';
      $newfilename = $path['dirname'].'/'.$path['filename'].$ver.$path['extension']; 
    }
    array_push($files, substr($newfilename, $abspathLen));
  }
}

if($argc > 1) {
  if(in_array('manifest', $argv)) {
    
    // Build manifest file
    $files = array();

    addFiles($files, 'favicon.ico');
    addFiles($files, 'css/*.css', true);
    addFiles($files, 'css/smoothness/*.css', true);
    addFiles($files, 'js/*.js', true);
    addFiles($files, 'js/libs/modernizr-1.6.min.js');
    addFiles($files, 'js/libs/json2-min.js', true);
    addFiles($files, 'js/libs/localstorage.js', true);
    addFiles($files, 'images/*', true);
    addFiles($files, 'css/smoothness/images/*', true);
    array_push($files, 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js');
    array_push($files, 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js');
    
    $manifestFile = fopen('web/cache.manifest', 'wb');
    if($manifestFile) {
      fputs($manifestFile, "CACHE MANIFEST\n");
      foreach($files as $filename) {
        fputs($manifestFile, $filename."\n");
      }
      echo 'Manifest file created.';
    } else {
      echo 'Error opening Manifest file.';
    }
  }
}
