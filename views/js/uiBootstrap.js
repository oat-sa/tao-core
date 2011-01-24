/**
 * UiBootstrap class enable you to run the naviguation mode, 
 * bind the events on the main components and initialize handlers
 * 
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require [helpers.js]
 * 
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
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
		this.initMenuBar();
		
		//create tabs
		UiBootstrap.tabs = $('#tabs').tabs({
			load: function(){
				$("#section-trees").empty().css({display: 'none'});
				$("#section-actions").empty().css({display: 'none'});
				bootInstance.initTrees();
			}, 
			select: function(event, ui) {
				$("#section-trees").empty().css({display: 'none'});
				$("#section-actions").empty().css({display: 'none'});
				$("#" + UiBootstrap.tabs.attr('id') + " > .ui-tabs-panel").each(function(){
					if($(this).attr('id') != ui.panel.id){
						$(this).empty();
					}
				});
			},
			collapsible: true
		});
	};
	
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
			if (settings.dataType == 'html') {
				_autoFx();
				if(/add|edit|Instance|Class|search|getSectionTrees/.test(settings.url) && !/authoring/i.test(settings.url)){
					bootInstance.initActions();
				}
				if(!/getMetaData/.test(settings.url)){
					$("#section-meta").empty();
				}
				bootInstance.initSize();
			}
		});
		
		//intercept errors
		$("body").ajaxError(function(event, request, settings){
		 	if(request.status == 404 || request.status == 500){
				createErrorMessage(request.responseText);
			}
			if(request.status == 403){
				window.location = root_url + '/tao/Main/logout';
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
	}
	
	/**
	 * initialize the tree component
	 */
	this.initTrees = function(){
		//left menu trees init by loading the tab content
		if(UiBootstrap.tabs.length > 0){
			$.ajax({
				url: root_url + '/tao/Main/getSectionTrees',
				type: "GET",
				data: {
					section: $("li a[href=#" + $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id + "]:first").attr('title')		//get the link text of the selected tab
				},
				dataType: 'html',
				success: function(response){
					if(response == ''){
						$('#section-trees').css({display: 'none'});
					}
					else if($('#section-trees').css('display') == 'none'){
						$('#section-trees').css({display: 'block'});
					}
					$('#section-trees').html(response);
				}
			});
		}
	}
	
	/**
	 * initialize the actions component
	 */
	this.initActions = function(){
		//left menu actions init by loading the tab content
		if(UiBootstrap.tabs.length > 0){
			$.ajax({
				url: root_url + '/tao/Main/getSectionActions',
				type: "GET",
				data: {
					section: $("li a[href=#" + $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id + "]:first").attr('title')		//get the link text of the selected tab
				},
				dataType: 'html',
				success: function(response){
					if(response == ''){
						$('#section-actions').css({display: 'none'});
					}
					else if($('#section-actions').css('display') == 'none'){
						$('#section-actions').css({display: 'block'});
					}
					$('#section-actions').html(response);
					EventMgr.trigger('actionInitiated', [response]);
				}
			});
		}
	}
	
	/**
	 * re-calculate the container size regarding the components content
	 */
	this.initSize = function(){
		//set up the container size
		myPanel = $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')];
		if(myPanel){
			uiTab = myPanel.id;
			if($('#section-actions').html() == '' && $('#section-trees').html()  == '' && $("div#"+uiTab).css('left') == '19%' ){
				$("div#"+uiTab).css('left', '0%');
				$("div#"+uiTab).css('width', '99%');				
			}
			if( $('#section-actions').html() != '' || $('#section-trees').html()  != '' ){
				$("div#"+uiTab).css('left', '19%');
				$("div#"+uiTab).css('width', '80%');
			}
		}
	};
	
	this.initMenuBar = function(){
		//initialize the media manager menu
		$("#main-menu .file-manager").fmload({type: 'file'});
		
		//initialize the settings menu
		$("#main-menu .settings-loader").click(function(){
			_load(getMainContainerSelector(UiBootstrap.tabs), this.href);
			return false;
		});
	};
	
	//run the manual constructor
	this._init();
};


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
