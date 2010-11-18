
AsyncFileUpload = function(elt, options){

	var elt = elt;	
	
	this.settings = {
			"script"    : root_url + "/tao/File/upload",
			"uploader"  : taobase_www + "js/jquery.uploadify-v2.1.0/uploadify.swf",
			"cancelImg" : taobase_www + "img/cancel.png",
			"buttonImg"	: taobase_www + "img/browse_btn.png",
			"scriptAccess": 'always',
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
	
	
	
	$(elt).uploadify(this.settings);
	
	
	$(starter).click(function(){
	 	$(elt).uploadifyUpload();
	 	return false;
	 });
	
};
