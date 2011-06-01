<!doctype html>
<?php
$ads = 0; // Set to 1 to enable ads, and adjust 

$debug = 0;
if(isset($_GET['debug']) && $_GET['debug'] || $argc > 1 && in_array('debug', $argv)) {
    $debug = 1;
}
$mobile = 0;
$auto_mobile = 1;
if(isset($_GET['mobile']) && $_GET['mobile'] || $argc > 1 && in_array('mobile', $argv)) {
    $mobile = 1;
}
// Force desktop mode
if(isset($_GET['desktop']) && $_GET['desktop'] || $argc > 1 && in_array('desktop', $argv)) {
    $mobile = 0;
    $auto_mobile = 0; // don't auto-switch to mobile version
}
?>
<html <?php if(!$debug) echo 'manifest="cache.manifest"'; ?>>
<script type="text/javascript">
window.google_analytics_uacct = "UA-74505-26";
</script>
<script>
<?php
echo 'var debugMode = '.$debug.';';
echo 'var mobileMode = '.$mobile.';';
echo 'var adsMode = '.$ads.';';
?>
</script>
<?php if($auto_mobile && !$debug && !$mobile) : // Auto-detect mobile browsers and redirect from index.html ?>
<script><?php // script from http://detectmobilebrowser.com/ ?>
(function (a, b) {
    if (/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) 
        window.location = b;
})(navigator.userAgent || navigator.vendor || window.opera, 'm.html');
</script>
<?php endif ?>
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
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<title>HTML5 Notepad</title>
<meta name="description"
    content="HTML5 Notepad: Inspired by the iPad 'Notes' application. Very simple. Just start typing. Everything is saved to your system automatically.">
<meta name="author" content="Jason M. Hanley">

<link rel="shortcut icon" href="favicon.ico">

<?php if($mobile): // Mobile-only stuff ?>
    <!-- Mobile viewport optimization http://goo.gl/b9SaQ -->
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="apple-touch-icon-precomposed" href="apple-touch-icon-precomposed.png">
    
    <!-- Mobile IE allows us to activate ClearType technology for smoothing fonts for easy reading -->
    <meta http-equiv="cleartype" content="on">
    
    <style>
        textarea { overflow: auto; } /* www.sitepoint.com/blogs/2010/08/20/ie-remove-textarea-scrollbars/ */
        
        textarea#note {
            margin-bottom: 30px;
        }
    </style>
<?php else:  // Non-mobile stuff ?>
    <style>
        #saved { overflow-y: scroll; }
    </style>
<?php endif ?>

<link rel="stylesheet" href="<?php autoVer('css/style.css')?>">

</head>

<body>

<div id="loading">Loading h5note.com | Please wait...</div>

<div id="container" style="display:none;">

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

<?php if(!$mobile) echo '<table id="main_table" style="width:100%"><tr><td id="saved_td" style="width:50%">' ?>
<div id="saved">
    <div id="saved_docs"></div>
    <div id="saved_message"></div>
</div>
<?php if(!$mobile) echo '</td><td id="main_td" style="width:50%; overflow-y:hidden;">' ?>
<div id="main" <?php if($mobile) echo 'style="display:none;"' ?>>
    <div class="textareawrapper">
        <textarea id="note" disabled="disabled"></textarea>
    </div>
</div>
<?php if(!$mobile) echo '</td></tr></table>' ?>

<div id="config" style="display: none">
    <div id="login"></div>
    <div style="margin:4px;">
        Mode: <a href="./">Auto</a> | <a href="m.html">Mobile</a> | <a href="d.html">Desktop</a>
    </div>
    <div style="padding:0.5em;">
    <h2>About h5note | HTML5 Notepad</h2>
    <span style="float: left;"><img src="apple-touch-icon.png"
        style="width: 5em; height: 5em; margin: .5em;" /></span> Inspired by
    the iPad "Notes" application.
    <br /><br />
    Very simple. Just start typing. Everything is saved to your system automatically!
    <br /><br />
    <b>Features: </b>Works offline | Mobile-optimized | Syncronizes between devices
    <br /><br />
    <b>Any reason not to give us a 5-star rating? Contact us here first:  
        <a href="http://bit.ly/n5support" target="_">http://bit.ly/n5support</a></b>
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
    Available at the <a
        href="https://chrome.google.com/webstore/detail/olhhcobmolooljldnlapkgfnompogplm"
        target="_">Chrome Web Store</a>
    <br /><br />
    <a href="./about/">More information about h5note.com | HTML5 Notepad</a>
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

 
<?php if($ads && $mobile): ?>
<div id="ads" style="margin-top:4px">
<script type="text/javascript"><!--
  // XHTML should not attempt to parse these strings, declare them CDATA.
  /* <![CDATA[ */
  window.googleAfmcRequest = {
    client: 'ca-mb-pub-8566430800850888',
    format: '320x50_mb',
    output: 'html',
    slotname: '5039362503',
  };
  /* ]]> */
//--></script>
<script type="text/javascript"    src="http://pagead2.googlesyndication.com/pagead/show_afmc_ads.js"></script>
</div>
<?php elseif($ads): ?>
<div id="ads" style="margin-top:4px; height:90px;">
<center>
    <script type="text/javascript"><!--
    google_ad_client = "ca-pub-8566430800850888";
    /* note5 Desktop 468 */
    var whichAd = 0;//parseInt(Math.random() * 2, 10);
    if(whichAd) {
        google_ad_slot = "4027032749";
        google_ad_width = 468;
        google_ad_height = 60;
    } else {
        google_ad_slot = "4905786790";
        google_ad_width = 728;
        google_ad_height = 90;
    }
    //-->
    </script>
    <script type="text/javascript"
    src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
    </script>    
</center>
</div>
<?php endif ?>

<!-- <div>
<div id="last-write"></div>
<div id="status-message"></div>
</div> -->

<div id="dialog-confirm-delete" title="Confirm delete" style="display: none;">This item will be permanently deleted.
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

<?php if(!$debug): // don't count debug displays in analytics ?>
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
<?php endif ?>

</body>
</html>
