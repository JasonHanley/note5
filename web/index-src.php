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

<title>note5: An HTML5 Notepad</title>
<meta name="description"
    content="Inspired by the iPad 'Notes' application. Very simple. Just start typing. Everything is saved to your system automatically.">
<meta name="author" content="Jason M. Hanley">

<link rel="shortcut icon" href="favicon.ico">

<!-- Mobile viewport optimization http://goo.gl/b9SaQ -->
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="320"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<link rel="apple-touch-icon" href="apple-touch-icon.png">

<link rel="stylesheet" href="<?php autoVer('css/style.css')?>">
<link rel="stylesheet"
    href="<?php autoVer('css/smoothness/jquery-ui-1.8.9.custom.css')?>">
<script src="js/libs/modernizr-1.6.min.js"></script>

<!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
<meta http-equiv="cleartype" content="on">

</head>

<body>

<div id="container">

<div id="main" style="display: none">
    <table id="main_table" style="width:100%"><tr>
        <td id="button_saved" class="button-mobile"><img src="images/gnome_home.png" class="icon" alt="Home" title="Home" />Home</td>
    </tr></table>

    <table id="note_table" style="width:100%"><tr><td style="text-align:center; vertical-align:middle">
        <form><textarea id="note" disabled="disabled"></textarea></form>
    </td></tr></table>
</div>

<div id="saved">
    <table style="width:100%"><tr>
        <td id="button_new" class="button-mobile"><img src="images/gnome_new.png" class="icon" alt="New" title="New" />New</td>
        <td id="button_config" class="button-mobile"><img src="images/gnome_system.png" class="icon" alt="Config" title="Config" />Settings</td>
    </tr></table>

    <div id="saved_docs"></div>
    <div id="saved_message"></div>
</div>

<div id="config" style="display: none">
    <table style="width:100%"><tr>
        <td id="button_saved2" class="button-mobile"><img src="images/gnome_home.png" class="icon" alt="Home" title="Home" />Home</td>
        <td id="login" class="button-mobile">Sign in</td>
    </tr></table>
    
    <div style="padding:0.5em;">
    <h2>About note5: An HTML5 Notepad</h2>
    <span style="float: left;"><img src="apple-touch-icon.png"
        style="width: 5em; height: 5em; margin: .5em;" /></span> Inspired by
    the iPad "Notes" application. <br />
    <br />
    Very simple. Just start typing. Everything is saved to your system
    automatically. <br />
    <br />
    About the app: <a href="http://www.html5notepad.com/" target="_">www.html5notepad.com</a>
    <br />
    <br />
    Available at the <a
        href="https://chrome.google.com/webstore/detail/olhhcobmolooljldnlapkgfnompogplm"
        target="_">Chrome Web Store</a> <br />
    <br />
    <b>Problems or suggestions?</b> Report them here: <a
        href="http://bit.ly/n5support" target="_">http://bit.ly/n5support</a>
    <br />
    <br />
    Version: <?php echo date('Y-m-d G:i:s', time())?> <?php if($debug): ?> <br />
    <a href="#" onclick="Note5.resetApplication();">Reset Application</a> <br />
    <a href="#" onclick="testerrordfunctiondoesnotexist();">Test Error</a> <br />
    <a href="#" onclick="Note5.errorHandler('Msg','Url','Line');">Test Error
    2</a> <br />
    <br />
    Offline status: <span id="offlineStatus"></span> <?php endif ?></div>
</div>

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

<script
    src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
<script
    src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
<script src="<?php autoVer('js/libs/json2-min.js') // http://json.org/js.html ?>"></script>
<script src="<?php autoVer('js/libs/localstorage.js') // https://gist.github.com/350433 ?>"></script>
<script src="<?php autoVer('js/libs/autoresize.jquery.js') // http://james.padolsey.com/javascript/jquery-plugin-autoresize/ ?>"></script>
<script src="<?php autoVer('js/util.js')?>"></script>
<script src="<?php autoVer('js/note5.js')?>"></script>
<script>
    $(document).ready(function () {
      Note5.init();    
  });
  </script>

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-74505-25']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

</body>
</html>