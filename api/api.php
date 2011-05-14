<?php
require 'config-global.php';
require 'config-local.php';
require 'util.php';

//set_exception_handler('exception_handler');

$dbh = new PDO('mysql:host='.DBConfig::$dbhost.';dbname='.DBConfig::$dbname, DBConfig::$dbuser, DBConfig::$dbpass);

$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

class Actions {
    public static $Error = -1;
    public static $Init = 1;
};

function logAction($version, $type, $attrs) {
    global $dbh;

    $sth = $dbh->prepare('INSERT INTO action (version, type, attrs) VALUES (?, ?, ?)');
    $sth->execute(array($version, $type, $attrs));
}

$action = $_GET['action'];
if($action == 'log') {
    $json = $_GET['data'];
    //echo $json;
    $params = json_decode($json, true);
    //print_r($params);

    //logAction($params['version'], $params['type'], $json);

    //echo '<br /><br />Log success.';
} elseif($action == 'dt') { // Download .txt
    $filename = $_POST['fn'];
    $data = $_POST['data'];
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'.txt"');
    echo $data;

} elseif($action == 'logout') { // Disassociate this instance with a user

    $instanceId = $_REQUEST['instanceId'];
    $dbh->query('DELETE FROM user_instance WHERE instance="'.$instanceId.'"');
    
    header('Location: ' . WSConfig::$url);
    
} elseif($action == 'checklogin') { // See if this instance is already logged in
    
    $instanceId = $_REQUEST['instanceId'];
    
    // Check database to see whether this instance is already associated with a login
    $sth = $dbh->prepare('SELECT email
        FROM user_instance JOIN user ON user_instance.user_id=user.id
        WHERE user_instance.instance=?');
    $sth->execute(array($instanceId));
    $users = $sth->fetchAll();
    if($users && count($users)) {
        print $users[0]['email'];
    }
    
} elseif($action == 'glogin') { // Google Authentication (OpenID)

    $instanceId = $_REQUEST['instanceId'];
    
    require '../../lib/openid.php'; // http://gitorious.org/lightopenid / http://code.google.com/p/lightopenid/
    
    $openid = new LightOpenID;
    $openid->required = array('contact/email');
    if(!$openid->mode) {
        $openid->identity = 'https://www.google.com/accounts/o8/id';
        header('Location: ' . $openid->authUrl());
        die();
    } elseif($openid->mode == 'cancel') {
        
    } else {
        
        // If validated, store in database and return
        if($openid->validate()) {
            
            $sth = $dbh->query('SELECT id FROM user WHERE identity="'.$openid->identity.'"');
            $user = $sth->fetch();
            if($user) {
                $userId = $user['id'];
                $dbh->query('UPDATE user SET last_login="'.date('Y-m-d H:i:s', time()).'" WHERE id='.$userId);
            } else {
                $attrs = $openid->getAttributes();
                
                $sth = $dbh->prepare('INSERT INTO user (identity, email, last_login, attrs) VALUES (?, ?, ?, ?)');
                $sth->execute(array($openid->identity, $attrs['contact/email'], date('Y-m-d H:i:s', time()), '{}'));
                $userId = $dbh->lastInsertId();
            }
            
            // Does this instance already have an association? If so, delete it.
            $sth = $dbh->query('SELECT user_id,instance FROM user_instance WHERE instance="'.$instanceId.'"');
            $instance = $sth->fetch();
            if($instance) {
                $dbh->query('DELETE FROM user_instance WHERE user_id='.$instance['user_id'].' AND instance="'.$instance['instance'].'"');
            }
            
            $sth = $dbh->prepare('INSERT INTO user_instance (user_id, instance) VALUES (?, ?)');
            $sth->execute(array($userId, $instanceId));
        }
        
    }
    
    header('Location: ' . WSConfig::$url);
    
} elseif($action == 'gauth') { // Google Authorization (OAuth)

    // http://code.google.com/apis/accounts/docs/OAuth2.html#CallingAnAPI

    $error = $_REQUEST['error'];
    $code = $_REQUEST['code'];

    // TBD: If code, then post to https://accounts.google.com/o/oauth2/token
    /*
     POST /o/oauth2/token HTTP/1.1
     Host: accounts.google.com
     Content-Type: application/x-www-form-urlencoded

     code=4/P7q7W91a-oMsCeLvIaQm6bTrgtp6&
     client_id=21302922996.apps.googleusercontent.com&
     client_secret=XTHhXh1SlUNgvyWGwDk1EjXB&
     redirect_uri=https://www.example.com/back&
     grant_type=authorization_code
     */

} elseif($action == 'stl') { // ServerToLocal

    $ret = array();
    $instance = $_REQUEST['i'];
    $localLastWrite = $_REQUEST['llw'];
    
    $lastWriteServer = -1;
    $newLastWriteServer = time();
    $oldDocs = array();
    $newDocs = array();
    
    $sth = $dbh->prepare('SELECT user_id FROM user_instance WHERE instance=? LIMIT 1');
    $sth->execute(array($instance));
    $user = $sth->fetch();
    if($user) {
        $user_id = $user['user_id'];
        
        $sth = $dbh->query('SELECT last_write FROM docs WHERE user_id='.$user_id.' ORDER BY last_write DESC LIMIT 1');
        $doc = $sth->fetch();
        
        if($doc) {
            $lastWriteServer = $doc['last_write'];

            // Retrieve ids and mod times for docs modified before local was last updated
            $sth = $dbh->prepare('SELECT doc_id,last_write FROM docs WHERE last_write <= ?');
            $sth->execute(array($localLastWrite));
            $oldDocs = $sth->fetchAll(PDO::FETCH_ASSOC);
            
            // Retrieve all documents modified since local was last updated
            $sth = $dbh->prepare('SELECT * FROM docs WHERE last_write > ?');
            $sth->execute(array($localLastWrite));
            $newDocs = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $ret['error'] = 'User not found.';
    }
            
    $ret += array('lws' => $lastWriteServer, 'nlws' => $newLastWriteServer, 
        'oldDocs' => $oldDocs, 'newDocs' => $newDocs);
    $ret_json = json_encode($ret);
    echo $ret_json;
    
} elseif($action == 'lts') { // LocalToServer

    $ret = array();
    $lastWriteServer = time();
    $instance = $_REQUEST['i'];
    $lastServerLastWrite = $_REQUEST['lslw'];
    $deletes_json = $_REQUEST['del'];
    $updates_json = $_REQUEST['up'];
    $deletes = json_decode($deletes_json, true, 8);
    $updates = json_decode($updates_json, true, 8);
    
    $sth = $dbh->prepare('SELECT user_id FROM user_instance WHERE instance=? LIMIT 1');
    $sth->execute(array($instance));
    $user = $sth->fetch();
    if($user) {
        $user_id = $user['user_id'];
    
        if($deletes) {
            foreach($deletes as $docId) {
                $dbh->query('DELETE FROM docs WHERE doc_id="'.$docId.'" AND user_id='.$user_id);
            }
        }
        
        if($updates) {
            foreach($updates as $doc) {
                $sth = $dbh->prepare('REPLACE INTO docs (doc_id, user_id, name, content, last_write) VALUES (?, ?, ?, ?, ?)');
                $sth->execute(array($doc['docId'], $user_id, $doc['name'], $doc['content'], $lastServerLastWrite));
            }
        }
    } else {
        $ret['error'] = 'User not found.';
    }
    
    
    $ret += array('lws' => $lastWriteServer, 'updates' => $updates, 'deletes' => $deletes);
    $ret_json = json_encode($ret);
    echo $ret_json;
    
} else {
    echo 'Undefined action.';
}

$dbh = null;
