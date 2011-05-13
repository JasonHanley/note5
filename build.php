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

array_push($files, '# Version '.time());

addFiles($files, 'favicon.ico');
addFiles($files, 'apple-touch-icon.png');
addFiles($files, 'apple-touch-icon-precomposed.png');
addFiles($files, 'css/style.css', true);
addFiles($files, 'js/*.js', true);
addFiles($files, 'images/*');

array_push($files, "\nFALLBACK:");
array_push($files, '/ offline.html');

array_push($files, "\nNETWORK:");
array_push($files, '*');

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
shell_exec('php web/index-src.php '.$debugString.' > web/index.html');
echo "Index rebuilt.\n";
