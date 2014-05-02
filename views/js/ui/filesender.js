define( ['jquery', 'lodash'], function($, _){	
	'use strict';
	
	/**
	 * The FileSender widget enables you to post a file 
	 * to the server asynchronously.
	 * 
	 * @exports filesender
	 */
	var FileSender = {
	
		/**
		 * The default options
		 */
		_opts : {
			frame : '__postFrame_',
			loaded : function(data){}
		},
		
		/**
		 * Initialize the file sending
		 *  @param {Object} options - the sending options
		 *  @param {String} [options.url] - the url where the form will send the file, if not set we get the form.action attr
		 *  @param {String} [options.frame] - a name for the frame create in background
		 *  @param {FileLoadedCallback} [options.loaded] - executed once received the server response
		 */
		_init : function(options){

			var self = FileSender,
	            opts = _.defaults(options, self._opts),
	            $form = this,
	            id = opts.frame;
    
			if(!$form || !$form.is('form')){
				$.error('This plugin can only be called on a FORM element');
			}
			if(!$form.attr('action') && (!opts.url || opts.url.trim().length === 0)){
				$.error('An url is required in the options or at least an action ');
			}
			if($form.find("input[type='file']").length === 0){
				$.error('This plugin is used to post files, your form should include an input element of type file.');
			}
			
			//the iframe identifier is composed by opts.frame + (form.id or form.name or timestamp)
			//the timestamp is the worth because if the response goes wrong we will not be able to remove it
			id += ($form.attr('id') ?  $form.attr('id') : ($form.attr('name') ?  $form.attr('name') : (new Date()).getTime()));
			
			//clean up if already exists
			$('#' + id).remove();
			
			//we create the hidden frame as the action of the upload form (to prevent page reload)
			var $postFrame = $("<iframe />");
			$postFrame.attr({
				'name': id,
				'id' : id
			})
			.css('display', 'none');
			
			
			//we update the form attributes according to the frame
			$form.attr({
					'action'	: opts.url,
					'method'	: 'POST',
					'enctype'	: 'multipart/form-data',
					'encoding'	: 'utf8',
					'target'	: id
				})
				.append($postFrame);
			
			$('#' + id, $form).on('load', function(e){
				//we get the response in the frame
				var result = $.parseJSON($(this).contents().text());
				
				if(typeof opts.loaded === 'function'){
					 opts.loaded(result);
				}
				
				$(this).off('load');
				$(this).remove();
			});
				
			$form.submit();
		}
	};
	
	/**
	 * Reference the plugin to the jQuery context 
	 * to be able to call as $('#aForm').sendfile({'url' : '/api/postfile'});
	 *  @param {Object} options - the sending options
	 *  @param {String} options.url - the url where the form will send the file
	 *  @param {String} [options.frame] - a name for the frame create in background
	 *  @param {FileLoadedCallback} [options.loaded] - executed once received the server response
	 */
	$.fn.sendfile = function(options){
		return FileSender._init.call(this, options);
	};
	
	/**
	 * Callback function to receive the server response of posted file
	 * @callback FileLoadedCallback
	 * @param {Object} data - the evaluated JSON response sent by the server
	 */
});
