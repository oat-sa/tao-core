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
 * @return {String} the current main container jQuery selector (from the opened tab)
 */
function getMainContainerSelector(){
	var uiTab = $('.ui-tabs-panel')[tabs.tabs('option', 'selected')].id;
	return "div#"+uiTab+" div.main-container";
}

function selectTabByName(name){
	$("#"+name).click();
}

function getTabIndexByName(name){
	elts = $("div#tabs ul.ui-tabs-nav li a");
	i = 0;
	while(i < elts.length){
		elt = elts[i];
		if(elt){
			if(elt.id){
				if(elt.id == name){
					return i;
				}
			}
		}
		i++;
	}
	return -1;
}

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
	
	_initFormNavigation();
	_autoFx();
	_initForms();
}

/**
 * Load url asyncly into selector container
 * @param {String} selector
 * @param {String} url
 */
function _load(selector, url, data){
	
	if(data){
		data.nc = new Date().getTime();
	}
	else{
		data = {nc: new Date().getTime()}
	}
	loading();
	if(url.indexOf('?') == -1){
		$(selector).load(url, data, loaded());
	}
	else{
		url += '&' + ($.param(data));
		$(selector).load(url, loaded());
	}
}

/**
 * Make a nocache url, using a timestamp
 * @param {String} ref
 */
function _href(ref){
	return  (ref.indexOf('?') > -1) ? ref + '&nc='+new Date().getTime() : ref + '?nc='+new Date().getTime(); 
}

/**
 * apply effect to elements that are only present
 */
function _autoFx(){
	setTimeout(function(){
		$(".auto-highlight").effect("highlight", {color: "#9FC9FF"}, 2500);
	}, 750);
	setTimeout(function(){
		$(".auto-hide").fadeOut("slow");
	}, 2000);
	setTimeout(function(){
		$(".auto-slide").slideUp(1500);
	}, 5000);
}

function _initControls(){
	
	//fix ie6 bug
	$.ui.dialog.defaults.bgiframe = true;		
	
	//settings dialog
	$("#settings-form").dialog({
		width: 500,
		height: 300,
		autoOpen: false
	});
	$("a#settings-loader").click(function(){
		$("#settings-form").dialog('option', 'title', $(this).text());
		$("#settings-form").load(this.href).dialog('open');
		return false;
	});
	
	//meta data dialog
	/*if ($("#comment-form-container").hasClass("ui-dialog-content")) {
		$("#comment-form-container").dialog('destroy');
	}*/
	$("#comment-form-container").dialog({
			title: $("#comment-form-container-title").text(),
			width: 400,
			height: 200,
			autoOpen: false
		});
	 $("#comment-editor").click(function(){
		$("#comment-form-container").dialog('open');
		return false;
	 })
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
					$("#comment-form-container").dialog('close');
					$("#comment-field").text(response.comment);
				}
			}
		})
	})
}

/**
 * Check and cut the text of the selector container only if the text is longer than the maxLength parameter
 * @param {String} selector JQuery selector
 * @param {int} maxLength  
 */
function textCutter(selector, maxLength){
	if(!maxLength){
		maxLength = 100; 
	}
	$(selector).each(function(){
		if($(this).text().length > maxLength && !$(this).hasClass("text-cutted")){
			$(this).attr('title', $(this).text());
			$(this).css('cursor', 'pointer');
			$(this).html($(this).text().substring(0, maxLength) + "[...<img src='"+imgPath+"bullet_add.png' />]")
			$(this).addClass("text-cutted");
		}
	});
}

/**
 * Begin an async request, while loading:
 * - show the loader img
 * - disable the submit buttons
 */
function loading(){
	$("#ajax-loading").show('fast');
	$("input[type='submit']").attr('disabled', 'true');
}

/**
 * Complete an async request, once loaded:
 *  - hide the loader img
 *  - enable back the submit buttons
 */
function loaded(){
	$("#ajax-loading").hide('fast');
	$("input[type='submit']").attr('disabled', 'false');
}

function createErrorMessage(message){
	$("body").append("<div id='info-box' class='ui-state-error ui-widget-header ui-corner-all auto-slide' >"+message+"</div>")
	_autoFx();
}

var tabs = null;
$(function(){
	
	$("body").ajaxError(function(event, request, settings){
	 	if(request.status == 404){
			createErrorMessage(request.responseText);
		}
		if(request.status == 403){
			window.location = '/tao/Main/login?errorMessage='+request.responseText;
		}
	});

	
	//create tabs
	tabs = $('#tabs').tabs({
		load: loadControls, 
		collapsible: true
	});
	
})
