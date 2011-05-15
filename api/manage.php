<?php
require 'config-global.php';
require 'config-local.php';

if(in_array('dump-schema', $argv)) {
    
    system('mysqldump --user='.DBConfig::$dbuser.
        ' --password='.DBConfig::$dbpass.
        ' --no-data --skip-triggers --compact '.
        DBConfig::$dbname.' > schema.sql');
        
} elseif(in_array('dump-data', $argv)) {
    
    system('mysqldump --user='.DBConfig::$dbuser.
        ' --password='.DBConfig::$dbpass.
        ' --no-create-db --no-create-info --complete-insert --compact '.
        DBConfig::$dbname.' > data.sql');
    
} elseif(in_array('load-sql', $argv)) {

    system('mysql --user='.DBConfig::$dbuser.
        ' --password='.DBConfig::$dbpass.
        ' '.
        DBConfig::$dbname.' < '.$argv[2]);
    
} else {
    
    echo 'Commands:
  dump-schema
  dump-data
  load-sql [filename]    
    ';
    
}
