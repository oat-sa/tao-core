_parent.startCountDown = 60;
// listener on close event
var thisWindow_lo = new Object();
thisWindow_lo.click = function(){
	trace("system close requested");
	clearInterval(_parent.uploadInterval);
}
_parent.addEventListener("click", thisWindow_lo);

// listener on ok button
okButton_lo = new Object();
okButton_lo.click = function(){
	clearInterval(_parent.uploadInterval);
	_parent.callerOk.call(null);
}
this.ok_btn.addEventListener("click", okButton_lo);

// listener on cancel button
var cancelButton_lo = new Object();
cancelButton_lo.click = function (){
	clearInterval(_parent.uploadInterval);
	_parent.callerCancel.call(null);
}
this.cancel_btn.addEventListener("click", cancelButton_lo);

function showCountDown(){
	trace("showCountDown entered: " + _parent.startCountDown);
	if(_parent.startCountDown != undefined){
		var startCountDown_str:String = new String(_parent.startCountDown);
		var msg_str:String = new String("<font size='-1'>Fermeture automatique dans ");
		msg_str = msg_str.concat(startCountDown_str, " sec.</fond>");
		trace("MSG " + msg_str);
		trace("where _parent.content " + _parent.content);
		trace("_parent.content.timerLabel.text = " + _parent.content.timerLabel.text);
		_parent.content.timerLabel.text = msg_str;
//		this.timerLabel.redraw(true);
		_parent.startCountDown--;
		if(_parent.startCountDown == 0){
			trace("Time Out");
			clearInterval(_parent.uploadInterval);
			_parent.callerOk.call(null);
		}
	}
}
//var retLang = _root.getCurrentGuiLang();
this.msg_txt.text = _parent.messageToShow;

if(_parent.scrollState != undefined){
	this.msg_txt.vScrollPolicy = _parent.scrollState;
}

if((_parent.okLabel != this.ok_btn.label) && (_parent.okLabel != undefined)){
	this.ok_btn.label = _parent.okLabel;
}

if((_parent.cancelLabel != this.cancel_btn.label) && (_parent.cancelLabel != undefined)){
	this.cancel_btn.label = _parent.cancelLabel;
}
if(_parent.callerCancel == undefined){
	this.cancel_btn.visible = false;
	this.ok_btn.move(60,this.ok_btn.y);
}

if((_parent.focusOn=="Ok") || (_parent.callerCancel == undefined)){
	this.ok_btn.setFocus();
}
else{
	this.cancel_btn.setFocus();
}

if(_parent.timerSetOn != undefined){
	this.msg_txt.setSize(180,100);
	this.timerLabel.visible = True;
	var startCountDown_str:String = new String(_parent.timerSetOn);
	var msg_str:String = new String("<font size='-1'>Fermeture automatique dans ");
	msg_str = msg_str.concat(startCountDown_str, " sec.</fond>");
	this.timerLabel.text = msg_str;
	trace("_parent.timerSetOn = " + _parent.timerSetOn);
	_parent.startCountDown = _parent.timerSetOn;
	_parent.uploadInterval = setInterval(showCountDown,1000);
}
else{
	this.timerLabel.visible = false;
}
