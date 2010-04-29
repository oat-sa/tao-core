
AsyncFileUpload = function(elt, options){
	
	(options.basePath) ? basePath = options.basePath : basePath = '';
	
	this.settings = {
			"script"    : "/tao/File/upload",
			"uploader"  : basePath + "js/jquery.uploadify-v2.1.0/uploadify.swf",
			"cancelImg" : basePath + "img/cancel.png",
			"buttonImg"	: basePath + "img/browse_btn.png",
			"width"		: 140,
			"height"	: 40,
			"auto"      : false,
			"buttonText": "'.__('Browse').'",
			"folder"    : "/"
	};
	
	if(options.fileDesc){
		this.settings.fileDesc = options.fileDesc;
	}
	if(options.fileExt){
		this.settings.fileExt = options.fileExt;
	}
	if(options.sizeLimit){
		this.settings.sizeLimit = options.sizeLimit;
	}
	if(options.folder){
		this.settings.folder = options.folder;
	}
	
	if(options.target){
		var target = $(options.target);
		this.settings.onComplete = function(event, queueID, fileObj, response, data){
			response = $.parseJSON(response);
			if(response.uploaded){
				target.val(response.data);
			}
			return false;
		};
	}
	
	var elt = elt;	
	$(elt).uploadify(this.settings);
	
	(options.starter) ? starter = options.starter : starter = elt + '_starter';
	$(starter).click(function(){
	 	$(elt).uploadifyUpload();
	 });
	
};
