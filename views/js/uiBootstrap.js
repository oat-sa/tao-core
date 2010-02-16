/**
 * UiBootstrap class enable you to run the naviguation mode, 
 * bind the events on the main components and initialize handlers
 * 
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require [helpers.js]
 * 
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */ 

/**
 * @var tabs access the tabs instance globally
 */
UiBootstrap = function(options){
	
	/**
	 * to access the context 
	 */
	var bootInstance = this;
	
	/**
	 * manual constructor
	 */
	this._init = function(){
		
		this.initAjax();
		this.initNav();
		
		//fix ie6 bug with dialog z-index
		$.ui.dialog.defaults.bgiframe = true;	
		
		//create tabs
		UiBootstrap.tabs = $('#tabs').tabs({
			load: function(){
				bootInstance.initTrees();
			}, 
			collapsible: true
		});
	}
	
	/**
	 * initialize common ajavx behavior
	 */
	this.initAjax = function(){
		
		//just before an ajax request
		$("body").ajaxSend(function(event,request, settings){
			loading();
		});
		
		//when an ajax request complete
		$("body").ajaxComplete(function(event,request, settings){
			loaded();
			_autoFx();
			if (settings.dataType == 'html') {
				if(/add|edit|Instance|Class|search|getSectionTrees/.test(settings.url) ){
					bootInstance.initActions();
				}
				bootInstance.initSize();
			}
		});
		
		//intercept errors
		$("body").ajaxError(function(event, request, settings){
		 	if(request.status == 404){
				createErrorMessage(request.responseText);
			}
			if(request.status == 403){
				window.location = '/tao/Main/logout';
			}
		});
	}
	
	/**
	 * initialize common naviguation
	 */
	this.initNav = function(){
		//load the links target into the main container instead of loading a new page
		$('a.nav').live('click', function() { 	
			try{
				_load(getMainContainerSelector(UiBootstrap.tabs), this.href);
			}
			catch(exp){ return false; }
			return false;
		});
		
		//initialize the settings menu
		$("#settings-loader").click(function(){
			_load(getMainContainerSelector(UiBootstrap.tabs), this.href);
			return false;
		});
	}
	
	/**
	 * initialize the tree component
	 */
	this.initTrees = function(){
		//left menu trees init by loading the tab content
		$.ajax({
			url: '/tao/Main/getSectionTrees',
			type: "GET",
			data: {
				section: $("li a[href=#" + $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
			},
			dataType: 'html',
			success: function(response){
				$('#section-trees').html(response);
			}
		});
	}
	
	/**
	 * initialize the actions component
	 */
	this.initActions = function(){
		//left menu actions init by loading the tab content
		$.ajax({
			url: '/tao/Main/getSectionActions',
			type: "GET",
			data: {
				section: $("li a[href=#" + $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
			},
			dataType: 'html',
			success: function(response){
				$('#section-actions').html(response);
			}
		});
	}
	
	/**
	 * re-calculate the container size regarding the components content
	 */
	this.initSize = function(){
		//set up the container size
		uiTab = $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id;
		if($('#section-actions').html() == '' && $('#section-trees').html()  == '' && $("div#"+uiTab).css('left') == '17.5%' ){
			$("div#"+uiTab).css('left', '0.5%');
			$("div#"+uiTab).css('width', '98%');
		}
		if( $('#section-actions').html() != '' || $('#section-trees').html()  != '' ){
			$("div#"+uiTab).css('left', '17.5%');
			$("div#"+uiTab).css('width', '81%');
		}
	}
	
	/**
	 * init and load the meta data component
	 * @param {String} uri
	 * @param {String} classUri
	 */
	this.getMetaData = function(uri, classUri){
		
		$("#comment-form-container").dialog('destroy');
		
		if(uri){
			 url = '';
			 if(ctx_extension){
			 	url = '/' + ctx_extension + '/' + ctx_module + '/';
			 }
			 url += 'getMetaData';
			 $.ajax({
			 	url: url,
				type: "POST",
				data:{uri: uri, classUri: classUri},
				dataType: 'html',
				success: function(response){
					$('#section-meta').html(response);
					
					//meta data dialog
					var commentContainer = $("#comment-form-container");
					if (commentContainer) {
						
						$("#comment-editor").click(function(){
							
							commentContainer.dialog({
								title: $("#comment-form-container-title").text(),
								width: 330,
								height: 220,
								autoOpen: false
							});
							commentContainer.bind('dialogclose', function(event, ui){
								commentContainer.dialog('destroy');
								//commentContainer.remove();
							});
							commentContainer.dialog('open');
							$("#comment-saver").click(function(){
								if (ctx_extension) {
									url = '/' + ctx_extension + '/' + ctx_module + '/';
								}
								url += 'saveComment';
								$.ajax({
									url: url,
									type: "POST",
									data: $("#comment-form").serializeArray(),
									dataType: 'json',
									success: function(response){
										if (response.saved) {
											commentContainer.dialog('close');
											$("#comment-field").text(response.comment);
										}
									}
								})
							})
							return false;
						})
					}
				}
			});
		}
	}
	
	//run the manual constructor
	this._init();
}


/**
 * @var uiBootstrap accessible globally
 */
var uiBootstrap = null;

/**
 * instanciate UiBootstrap on load
 */
$(document).ready(function(){
	uiBootstrap = new UiBootstrap();
});
