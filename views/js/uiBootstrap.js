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
		this.initMenuBar();
		
		//fix ie6 bug with dialog z-index
	//	$.ui.dialog.defaults.bgiframe = true;	
		
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
				if(/authoring/i.test(settings.url)){
					$("#section-trees").empty();
					$("#section-actions").empty();
				}
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
				section: $("li a[href=#" + $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id + "]:first").attr('title')		//get the link text of the selected tab
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
				section: $("li a[href=#" + $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')].id + "]:first").attr('title')		//get the link text of the selected tab
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
		myPanel = $('.ui-tabs-panel')[UiBootstrap.tabs.tabs('option', 'selected')];
		if(myPanel){
			uiTab = myPanel.id;
			if($('#section-actions').html() == '' && $('#section-trees').html()  == '' && $("div#"+uiTab).css('left') == '17.5%' ){
				$("div#"+uiTab).css('left', '0%');
				$("div#"+uiTab).css('width', '99%');
			}
			if( $('#section-actions').html() != '' || $('#section-trees').html()  != '' ){
				$("div#"+uiTab).css('left', '17.5%');
				$("div#"+uiTab).css('width', '81%');
			}
		}
	}
	
	this.initMenuBar = function(){
		//menu button
		$("#menu-button").mouseover(function(){
			this.src = this.src.replace('.png', '_high.png'); 
		});
		$("#menu-button").mouseout(function(){
			this.src = this.src.replace('_high.png', '.png'); 
		});
		$("#menu-button").click(function(){
			$("#menu-popup").show("slide", { direction: "up" }, 800);
			setTimeout(function(){
				$("#menu-popup").hide("slide", { direction: "up" }, 500);
			}, 6000);
		});
		
		$("#menu-expander").click(function(){
			$('.ghost-menu').toggle();
			if(/arrow_right\.png$/.test(this.src)){
				this.src = this.src.replace('_right.png', '_left.png');
			}
			else{
				this.src = this.src.replace('_left.png', '_right.png');
			}
		})
		
		//initialize the settings menu
		$(".settings-loader").click(function(){
			_load(getMainContainerSelector(UiBootstrap.tabs), this.href);
			return false;
		});
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
