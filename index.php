<!doctype html>  

<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js" manifest="cache.manifest"> <!--<![endif]-->
<head>
<?php
$debug = false;
if(isset($_GET['debug']) && $_GET['debug'] || $argc > 1 && in_array('debug', $argv)) {
	$debug = true;
	echo '<script>var debugMode = '.$debug.';</script>';
}

// http://particletree.com/notebook/automatically-version-your-css-and-javascript-files/
// (modified to actually work)
function autoVer($url) {
	global $debug;
	
  $abspath = dirname(__FILE__).'/web';
  $path = pathinfo($url);
  $ver = '..'.filemtime($abspath.'/'.$url).'.';
  
  if($debug)
    {echo $url; return;}
  
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
  <meta name="description" content="Inspired by the iPad 'Notes' application. Very simple. Just start typing. Everything is saved to your system automatically.">
  <meta name="author" content="Jason M. Hanley">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php /* <meta name="apple-mobile-web-app-capable" content="yes"  /> */ ?>
  
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <link rel="stylesheet" href="<?php autoVer('css/style.css')?>">
  <link rel="stylesheet" href="<?php autoVer('css/smoothness/jquery-ui-1.8.9.custom.css')?>">
  <script src="js/libs/modernizr-1.6.min.js"></script>
</head>

<body>

  <div id="container">
    <header>
			<nav id="nav">
        <ul>
          <li><a id="button_home" href="#" class="awesome icon"><img src="images/gnome_home.png" class="icon" alt="Home" title="Home" /></a></li>
          <li><a id="button_new" href="#" class="awesome icon"><img src="images/gnome_new.png" class="icon" alt="New" title="New" /></a></li>
          <li><a id="button_saved" href="#" class="awesome icon"><img src="images/gnome_open.png" class="icon" alt="Saved" title="Saved" /><span id="num_saved"></span></a></li>
          <li><a id="button_config" href="#" class="awesome icon"><img src="images/gnome_system.png" class="icon" alt="Config" title="Config" /></a></li>
        </ul>
			</nav>
    </header>
    
    <div id="main">
      <form>
        <textarea id="note" rows="12"></textarea>
        <div id="help">Text area will auto-expand as you type. Changes are auto-saved.</div>
        <?php if($debug) echo 'Timestamp: '.time(); ?>
      </form>
    </div>
    
    <div id="saved" style="display:none">
	    <div id="saved_docs">
	      No saved documents.
	    </div>
      <div id="saved_message"></div>
    </div>

    <div id="config" style="display:none">
      <h2>About note5: An HTML5 Notepad</h2>
      Inspired by the iPad "Notes" application.
      <br /><br />
      Very simple. Just start typing. Everything is saved to your system automatically.
      </br /></br />
      By: <a href="http://www.jasonhanley.com/" target="_">Jason M. Hanley</a>
      <br /><br />
      Available at the <a href="https://chrome.google.com/webstore/detail/olhhcobmolooljldnlapkgfnompogplm" target="_">Chrome Web Store</a>
      <br /><br />
      Version: <?php echo date('Y-m-d G:i:s', time())?>
      <?php if($debug): ?>
        <br />
        <a href="#" onclick="Note5.resetApplication();">Reset Application</a>
        <br />
        <a href="#" onclick="testerrordfunctiondoesnotexist();">Test Error</a>
        <br />
        <a href="#" onclick="Note5.errorHandler('Msg','Url','Line');">Test Error 2</a>
        <br /><br />
        Offline status: <span id="offlineStatus"></span>
      <?php endif ?>
    </div>
    
    <footer>
    </footer>
  </div> <!-- end of #container -->

  <div id="dialog-confirm-delete" title="Confirm delete" style="display:none;">
    This item will be permanently deleted. Recovery will not be possible.
  </div>

  <div id="dialog-error" title="Error" style="display:none;">
    We're very sorry, but there's been an error. It may be a problem with your browser.<br /><br />
    It is being logged right now and will attempt to fix it! Please check back in a few days.<br /><br />
    Sorry for the trouble, and thanks for your patience!
    <div id="error-return"></div>
  </div>

  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
  <script src="<?php autoVer('js/libs/json2-min.js')?>"></script>
  <script src="<?php autoVer('js/libs/localstorage.js')?>"></script>
  <script src="<?php autoVer('js/util.js')?>"></script>
  <script src="<?php autoVer('js/note5.js')?>"></script>
  <script>
    $(document).ready(function () {
      Note5.init();    
  });
  </script>
  
  <!--[if lt IE 7 ]>
    <script src="js/libs/dd_belatedpng.js"></script>
    <script> DD_belatedPNG.fix('img, .png_bg'); </script>
  <![endif]-->
<?php /* Disable Analytics for now
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
*/ ?>

</body>
</html>