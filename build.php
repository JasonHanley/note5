<?php

// NOTES:
// - php must be in your path
// - git must be in your path if you want to use 'update'

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

$debug = false;
$debugString = '';

if($argc > 1) {
  
  // Check for debugging mode
  if(in_array('debug', $argv)) {
    $debug = true;
    $debugString = 'debug';
  }
}

// Always build manifest
$files = array();

touch('web/js/dummy.js');

addFiles($files, 'favicon.ico');
addFiles($files, 'css/*.css', true);
addFiles($files, 'css/smoothness/*.css', true);
addFiles($files, 'js/*.js', true);
addFiles($files, 'js/libs/json2-min.js', true);
addFiles($files, 'js/libs/localstorage.js', true);
addFiles($files, 'js/libs/modernizr-1.6.min.js');
addFiles($files, 'images/*');
addFiles($files, 'css/smoothness/images/*');
array_push($files, 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js');
array_push($files, 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js');
//array_push($files, 'http://www.google-analytics.com/ga.js');
//array_push($files, 'http://www.google-analytics.com/__utm.gif');

//array_push($files, "\nFALLBACK:");
//array_push($files, 'api/* offline.html');
//array_push($files, 'http://www.google-analytics.com/* offline.html');

//array_push($files, "\nNETWORK:");
//array_push($files, '*');

$manifestFile = fopen('web/cache.manifest', 'wb');
if($manifestFile) {
  fputs($manifestFile, "CACHE MANIFEST\n");
  foreach($files as $filename) {
    fputs($manifestFile, $filename."\n");
  }
  fclose($manifestFile);
  echo "Manifest file created.\n";
} else {
  echo "Error opening Manifest file.\n";
}

// Always rebuild index
shell_exec('php index.php '.$debugString.' > web/index.html');
echo "Index rebuilt.\n";
