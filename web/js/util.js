// Repeat a string n times
String.prototype.repeat = function(n) {
  return new Array(n + 1).join(this);
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

function getWindowSize() {
  var myWidth = 0, myHeight = 0, ret = new Object;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }
  
  ret.width = myWidth;
  ret.height = myHeight;

  return ret;
}
