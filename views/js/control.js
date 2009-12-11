/**
 * initialize the naviguation into the ui components 
 */
function loadControls(){
	
	//left menu trees init by loading the tab content
	$.ajax({
		url: '/tao/Main/getSectionTrees',
		type: "GET",
		data: {
			section: $("li a[href=#" + $('.ui-tabs-panel')[tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
		},
		dataType: 'html',
		success: function(response){
			$('#section-trees').html(response);
			initNavigation();
		}
	});
	
	initActions();
	_initControls();
}

/**
 * initialize the action component
 */
function initActions(){
	//left menu actions init by loading the tab content
	$.ajax({
		url: '/tao/Main/getSectionActions',
		type: "GET",
		data: {
			section: $("li a[href=#" + $('.ui-tabs-panel')[tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
		},
		dataType: 'html',
		success: function(response){
			$('#section-actions').html(response);
			initNavigation();
		}
	});
	
}

/**
 * Load the metadata
 * @param {String} uri
 * @param {String} classUri
 */
function getMetaData(uri, classUri){
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
				_initControls();
			}
		 });
	}
}

/**
 * change links and form behavior to load  content via ajax
 * @see form.js, helpers.js
 */
function initNavigation(){
	
	//links load the content into the main container
	$('a.nav').click(function() { 	
		try{
			_load(getMainContainerSelector(), this.href);
		}
		catch(exp){ return false; }
		return false;
	});
	
	//set up the container size
	uiTab = $('.ui-tabs-panel')[tabs.tabs('option', 'selected')].id;
	if($('#section-actions').html() == '' && $('#section-trees').html()  == '' && $("div#"+uiTab).css('left') == '17.5%' ){
		$("div#"+uiTab).css('left', '0.5%');
		$("div#"+uiTab).css('width', '98%');
	}
	if( $('#section-actions').html() != '' || $('#section-trees').html()  != '' ){
		$("div#"+uiTab).css('left', '17.5%');
		$("div#"+uiTab).css('width', '81%');
	}
	
	_initFormNavigation();	//on form.js
	_autoFx();				//on helpers.js
	_initForms();			//on form.js
}

/**
 * 
 */
function _initControls(){
	
	//meta data dialog
	var commentContainer = $("#comment-form-container");
	if (commentContainer) {
		
		$("#comment-editor").click(function(){
			
			commentContainer.dialog({
				title: $("#comment-form-container-title").text(),
				width: 300,
				height: 200,
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

/*
 * Executed on loading
 */
var tabs = null;
$(function(){
	
	//fix ie6 bug with dialog z-index
	$.ui.dialog.defaults.bgiframe = true;	
	
	//intercept errors
	$("body").ajaxError(function(event, request, settings){
	 	if(request.status == 404){
			createErrorMessage(request.responseText);
		}
		if(request.status == 403){
			window.location = '/tao/Main/logout';
		}
	});
	
	//create tabs
	tabs = $('#tabs').tabs({
		load: loadControls, 
		collapsible: true
	});
})