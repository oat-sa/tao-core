/**
 * AsyncFileUpload class
 * @class 
 */
AsyncFileUpload = function(elt, options){

	var self = this;
	var elt = elt;	
	
	this.settings = {
			"script"    : root_url + "/tao/File/upload",
			"uploader"  : taobase_www + "js/jquery.uploadify-v2.1.0/uploadify.swf",
			"cancelImg" : taobase_www + "img/cancel.png",
			"buttonImg"	: taobase_www + "img/browse_btn.png",
			"scriptAccess": 'sameDomain',
			"width"		: 140,
			"height"	: 40,
			"auto"      : true,
			"multiple"	: false,
			"buttonText": __('Browse'),
			"folder"    : "/"
	};
	
	this.settings = $.extend(true, this.settings, options);
	
	var target = false;
	if(options.target){
		var target = $(options.target);
	}
	(options.starter) ? starter = options.starter : starter = elt + '_starter';

	if(target){
		this.settings.onComplete = function(event, queueID, fileObj, response, data){
			response = $.parseJSON(response);
			if(response.uploaded){
				target.val(response.data);
			}
			return false;
		};
	}
	
	if(isFlashPluginEnabled() && typeof(jQuery.fn.uploadify) != 'undefined'){
	
		$(elt).uploadify(this.settings);
		
		$(starter).click(function(){
		 	$(elt).uploadifyUpload();
		 	return false;
		 });
	
	}
	else{
		//fallback if no flash or if uploadify is not loaded
		var params = {
				target : options.target
		};
		if(this.settings.fileExt){
			params.fileExt = this.settings.fileExt;
		}
		if(this.settings.fileExt){
			params.sizeLimit = this.settings.sizeLimit;
		}
		
		var opener = $("<span><a href='#'>"+__('Upload File')+"</a></span>");
		opener.click(function(e){
			
			$(this).attr('disabled', true);
			
			var url = root_url + '/tao/File/htmlUpload?' + $.param(params);
			var popupOpts = "width=350px,height=100px,menubar=no,resizable=yes,status=no,toolbar=no,dependent=yes,left="+e.pageX+",top="+e.pageY;
			
			self.window = window.open(url, 'fileuploader', popupOpts);
			self.window.focus();
			
			
			return false;
		});
		$(elt).parents('div.form-elt-container').append(opener);
		
		$(elt).hide();
		$(starter).hide();
		
	}
};