<!doctype html>
<?php
$debug = false;
if(isset($_GET['debug']) && $_GET['debug'] || $argc > 1 && in_array('debug', $argv)) {
    $debug = true;
    echo '<script>var debugMode = '.$debug.';</script>';
}?>
<html <?php if(!$debug) echo 'manifest="cache.manifest"'; ?>>
<head>
<?php
// http://particletree.com/notebook/automatically-version-your-css-and-javascript-files/
// (modified to actually work)
function autoVer($url) {
    global $debug;

    $abspath = dirname(__FILE__);
    $path = pathinfo($url);
    $ver = '..'.filemtime($abspath.'/'.$url).'.';

    if($debug) {
        echo $url; return;
    }

    echo $path['dirname'].'/'.$path['filename'].$ver.$path['extension'];
}

$abspath = dirname(__FILE__);
$path = pathinfo($url);
$ver = filemtime($abspath);

echo '<script>var note5fileVersion='.$ver.';</script>';

?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>h5note.com</title>
<meta name="description"
    content="HTML5 Notepad: Inspired by the iPad 'Notes' application. Very simple. Just start typing. Everything is saved to your system automatically.">
<meta name="author" content="Jason M. Hanley">

<link rel="shortcut icon" href="favicon.ico">

<!-- Mobile viewport optimization http://goo.gl/b9SaQ -->
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">

<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">

<link rel="stylesheet" href="<?php autoVer('css/style.css')?>">

<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
<meta http-equiv="cleartype" content="on">

</head>

<body>

<div id="container">

<table id="menu_bar"><tr><td>
    <div id="button_saved" class="button-mobile"><img src="images/gnome_home.png" class="icon" alt="Home" title="Home" /><span id="num_saved"></span></div>
    <div id="button_new" class="button-mobile"><img src="images/gnome_new.png" class="icon" alt="New" title="New" /></div>
    <div id="button_config" class="button-mobile"><img src="images/gnome_system.png" class="icon" alt="Config" title="Config" /></div>
    <div id="button_login" class="button-mobile" style="display:none;"><img src="images/g_favicon.png" class="icon" alt="Sign in" title="Sign in" /></div>
    <div id="button_sync" class="button-mobile" style="display:none;"><img src="images/gnome_sync.png" class="icon" alt="Sync" title="Sync" /></div>
    <div id="right_status">
        <img id="status_saving" src="images/gnome_saving.png" class="icon" style="display:none;" />
        <img id="status_syncing" src="images/gnome_syncing.png" class="icon" style="display:none;" />
    </div>
</td></tr></table>

<div id="main" style="display:none;">
    <div class="textareawrapper">
        <textarea id="note" disabled="disabled"></textarea>
    </div>
</div>

<div id="saved">
    <div id="saved_docs"></div>
    <div id="saved_message"></div>
</div>

<div id="config" style="display: none">
    <div id="login"></div>
    <div style="padding:0.5em;">
    <h2 style="margin-top: 3em;">About h5note | HTML5 Notepad</h2>
    <span style="float: left;"><img src="apple-touch-icon.png"
        style="width: 5em; height: 5em; margin: .5em;" /></span> Inspired by
    the iPad "Notes" application.
    <br /><br />
    Very simple. Just start typing. Everything is saved to your system automatically!
    <br /><br />
    <b style="color:red;">New!</b> <b>Automatic Synchronization.</b> Just sign in with your Google account.
    <br /><br />
    Available at the <a
        href="https://chrome.google.com/webstore/detail/olhhcobmolooljldnlapkgfnompogplm"
        target="_">Chrome Web Store</a>
    <br /><br />
    <b style="color:red;">Can't find your old notes?</b> We changed addresses. Go to the old address at 
    <a href="http://note5.jasonhanley.com" target="_">note5.jasonhanley.com</a>, 
    and export your notes.<br />
    Then come back and <a href="#" onclick="$('#import_old').show();">click here to import</a>.
    <div id="import_old" style="display:none;">
    Paste your exported notes here:<br />
    <textarea id="import_data" style="width:32em; height:12em;"></textarea><br />
    <button onclick="Note5.importOld();">Import</button>
    <br /><br />
    </div>
    <br /><br />
    <b>Problems or suggestions?</b> 
        <a href="http://bit.ly/n5support" target="_">http://bit.ly/n5support</a>
    <br /><br />
    <b>Version:</b> <?php echo date('Y-m-d G:i:s', time())?>
    <br /><br />
    <img src="images/html5-badge-h-css3-semantics-storage.png" alt="Built using HTML5" title="Built using HTML5" />
    <br />
    Source code available: <a
        href="https://github.com/JasonHanley/note5"
        target="_">github.com/JasonHanley/note5</a>
    <br /><br />
    <?php if($debug): ?> <br />
    <a href="#" onclick="Note5.resetApplication();">Reset Application</a> <br />
    <a href="#" onclick="testerrordfunctiondoesnotexist();">Test Error</a> <br />
    <a href="#" onclick="Note5.errorHandler('Msg','Url','Line');">Test Error
    2</a> <br />
    <br />
    Offline status: <span id="offlineStatus"></span> <?php endif ?>
    </div>
</div>

<!-- <div>
<div id="last-write"></div>
<div id="status-message"></div>
</div> -->

<div id="dialog-confirm-delete" title="Confirm delete"
    style="display: none;">This item will be permanently deleted.
    Recovery will not be possible.
</div>

<div id="dialog-error" title="Error" style="display: none;">
    We're very
    sorry, but there's been an error. It may be a problem with your browser.<br />
    <br />
    It is being logged right now and will attempt to fix it! Please check
    back in a few days.<br />
    <br />
    Sorry for the trouble, and thanks for your patience!
    <div id="error-return"></div>
</div>
   
<!-- end of #container -->
</div>

<script src="<?php autoVer('js/util.js')?>"></script>
<script src="<?php autoVer('js/note5.js')?>"></script>
<script>
    $(document).ready(function () {
      Note5.init();    
  });
  </script>

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-74505-26']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

</body>
</html>
