//   -----------------------------
//   show tooltip over movie clips
//   and buttons
//   -----------------------------
//syntax:
//movieClip.setTooltip(string[,timer,textColor,backgroundColor,borderColor])
//   -----------------------------
MovieClip.prototype.setTooltip = function(theText, timer, text_color, bg_color, border_color)
{
   if (timer == undefined)
   {
      timer = 500;
   }
   var addMsg = function (theMsg, col, bg_color, border_color, level)
   {
      var x = _root._xmouse
      var y = _root._ymouse      
      var f = new TextFormat();
      f.font = "Verdana";
      f.size = 11;
      f.color = col != undefined ? col : 0x000000;
      _level0.createTextField('tooltip', 123456, x, y, 150, 20);
      with (_level0.tooltip)
      {
         setNewTextFormat(f);
         text = theMsg;
         selectable = false;
         autoSize = true;
         background = true;
         border = true;
         borderColor = border_color != undefined ? border_color : 0x000000;
         backgroundColor = bg_color != undefined ? bg_color : 0xFFFFEE;
         _y -= _height;
      }
      clearInterval(level.q_t);
   };
   this.q_t = setInterval(addMsg, timer, theText, text_color, bg_color, border_color, this);
};
//   --------------------
//   unset the tooltip
//   --------------------
MovieClip.prototype.unsetTooltip = function()
{
   _level0.tooltip.removeTextField();
   clearInterval(this.q_t);
};

//   ------------------------
//   USAGE
//   ------------------------
// as code of a button:
//
//   on (rollOver) 
//   {
//      setTooltip("This is a test comment", 500, 0x000000, 0xFFFFEE, 0x000000);
//      // setTooltip("This is a test comment\nyou can also set multiline text");
//   }
//   on (rollOut, release, press) 
//   {
//      unsetTooltip();
//   }
//   -------------------------