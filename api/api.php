<?php
include 'config-global.php';
include 'config-local.php';
include 'util.php';

$ref = $_SERVER['HTTP_REFERER'];
//echo $ref;
if(strpos($ref, Security::$URL) === false && strpos($ref, 'http://projects') === false)
  die('Error.');

set_exception_handler('exception_handler');

$dbh = new PDO('mysql:host='.DBConfig::$dbhost.';dbname='.DBConfig::$dbname, DBConfig::$dbuser, DBConfig::$dbpass);

$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

class Actions {
  public static $Error = -1; 
  public static $Init = 1;
};

function logAction($version, $type, $attrs) {
  global $dbh;
  
  $sth = $dbh->prepare('INSERT INTO actions (version, type, attrs) VALUES (?, ?, ?)');
  $sth->execute(array($version, $type, $attrs));
}

$action = $_GET['action'];
if($action == 'log') {
  $json = $_GET['data'];
  //echo $json;
  $params = json_decode($json, true);
  //print_r($params);
  
  logAction($params['version'], $params['type'], $json);
  
  echo '<br /><br />Log success.';
}
else {
  echo 'Undefined action.';
}

$dbh = null;
