// Repeat a string n times
String.prototype.repeat = function(n) {
  return new Array(n + 1).join(this);
};

// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/encodeURIComponent
String.prototype.urlEncode = function fixedEncodeURIComponent() {
  return encodeURIComponent(this).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A');
};

// Zero-pad a string on the left
String.prototype.zeroPad = function(num) {
  if (num == undefined)
    num = 2;
  ret = this;
  if (this.length < num)
    ret = '0'.repeat(num - 1) + this;

  return ret;
};

// Get a date in YYYY-MM-DD format
Date.prototype.get8601Date = function() {
  month = (this.getMonth() + 1).toString().zeroPad();
  daynum = this.getDate().toString().zeroPad();
  ret = this.getFullYear() + '-' + month + '-' + daynum;
  return ret;
};

// Get a time in HH:MM:SS format
Date.prototype.get8601Time = function() {
  hour = this.getHours().toString().zeroPad();
  min = this.getMinutes().toString().zeroPad();
  sec = this.getSeconds().toString().zeroPad();
  return hour + ':' + min + ':' + sec;
};

// Dynamically resize a textarea
Element.prototype.resize = function() {
  if (this.type == "textarea") {
    var minLines = 10;
    var content = this.value;
    var style = this.getAttribute('style');
    var lines = content.length > 0 ? content.split("\n").length : 0;
    var rows = this.getAttribute("rows");
    
    // Auto-adjust size (up or down) if content is longer than minLines
    if (lines >= minLines || (lines < minLines && rows > minLines)) {
      if(lines < minLines) lines = minLines;
      this.setAttribute("rows", lines + 2);
    }
  }
};

var CacheHelper = {
  getTextStatus: function() {
    var appCache = window.applicationCache;
  
    switch (appCache.status) {
      case appCache.UNCACHED: // UNCACHED == 0
        return 'UNCACHED';
        break;
      case appCache.IDLE: // IDLE == 1
        return 'IDLE';
        break;
      case appCache.CHECKING: // CHECKING == 2
        return 'CHECKING';
        break;
      case appCache.DOWNLOADING: // DOWNLOADING == 3
        return 'DOWNLOADING';
        break;
      case appCache.UPDATEREADY:  // UPDATEREADY == 5
        return 'UPDATEREADY';
        break;
      case appCache.OBSOLETE: // OBSOLETE == 5
        return 'OBSOLETE';
        break;
      default:
        return 'UKNOWN CACHE STATUS';
        break;
    };
  },
  
  setStatusDiv: function (divName) {
    var cache = window.applicationCache;

    cache.addEventListener("cached", function () {
      $(divName).html("Cached (working normally)");
    }, false);
    cache.addEventListener("checking", function () {
      $(divName).html("Checking manifest");
    }, false);
    cache.addEventListener("downloading", function () {
      $(divName).html("Starting download of cached files");
    }, false);
    cache.addEventListener("error", function (e) {
      $(divName).html("There was an error in the manifest, downloading cached files or you're offline: " + e);
    }, false);
    cache.addEventListener("noupdate", function () {
      $(divName).html("No update needed (working normally)");
    }, false);
    cache.addEventListener("progress", function () {
      $(divName).html("Downloading cached files");
    }, false);
    cache.addEventListener("updateready", function () {
      cache.swapCache();
      $(divName).html("Updated cache is ready");
      // Even after swapping the cache the currently loaded page won't use it
      // until it is reloaded, so force a reload so it is current.
      window.location.reload(true);
      console.log("Window reloaded");
    }, false);    
    
  }
    
};
