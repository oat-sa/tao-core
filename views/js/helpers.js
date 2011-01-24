/*
 * tabs helpers
 */

/**
 * @return {String} the current main container jQuery selector (from the opened tab)
 */
function getMainContainerSelector(tabObj){
	if(tabObj == undefined){
		tabObj = UiBootstrap.tabs;	//backward compat by using the global object
	}
	if(tabObj.size() == 0) {
		if($("div.main-container").length > 0){
			return "div.main-container";
		}
		return false;
	}
	var uiTab = $('.ui-tabs-panel')[tabObj.tabs('option', 'selected')].id;
	if($("div#"+uiTab+" div.main-container").css('display') == 'none'){
		return "div#"+uiTab;
	}
	return "div#"+uiTab+" div.main-container";
}

/**
 * @param {String} name the name of the tab to select
 */
function selectTabByName(name){
	$("#"+name).click();
}

/**
 * get the index of the tab identified by name
 * @param {String} name
 * @return the index or -1 if not found
 */
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

/**
 * Add parameters to a tab 
 * @param {Object} tabObj
 * @param {String} tabName
 * @param {Object} parameters
 */
function updateTabUrl(tabObj, tabName, url){
	index = getTabIndexByName(tabName);
	tabObj.tabs('url', index, url);
	tabObj.tabs('enable', index);
}

/*
 * Naviguation and ajax helpers
 */

/**
 * Begin an async request, while loading:
 * - show the loader img
 * - disable the submit buttons
 */
function loading(){
	$(window).bind('click', function(e){
		e.stopPropagation();
		e.preventDefault();
		return false;
	});
	$("#ajax-loading").show('fast');
	$(":submit, :button, a").attr('disabled', true).css('cursor', 'default');
	
}

/**
 * Complete an async request, once loaded:
 *  - hide the loader img
 *  - enable back the submit buttons
 */
function loaded(){
	$(window).unbind('click');
	$("#ajax-loading").hide('fast');
	$(":submit, :button, a").attr('disabled', false).css('cursor', 'pointer');
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
 * EXtends the JQuery post method for conveniance use with Json 
 * @param {String} url
 * @param {Object} data
 * @param {Function} callback
 */
$.postJson = function(url, data, callback) {
	$.post(url, data, callback, "json");
};


/*
 * others
 */

/**
 * apply effect to elements that are only present
 */
function _autoFx(){
	setTimeout(function(){
		$(".auto-highlight").effect("highlight", {color: "#9FC9FF"}, 2500);
	}, 1000);
	setTimeout(function(){
		$(".auto-hide").fadeOut("slow");
	}, 3000);
	setTimeout(function(){
		$(".auto-slide").slideUp(1500);
	}, 11000);
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
			$(this).html($(this).text().substring(0, maxLength) + "[...<img src='"+imgPath+"bullet_add.png' />]");
			$(this).addClass("text-cutted");
		}
	});
}

/**
 * Create a error popup to display an error message
 * @param {String} message
 */
function createErrorMessage(message){
	$("body").append("<div id='info-box' class='ui-state-error ui-widget-header ui-corner-all auto-slide' >"+message+"</div>")
	_autoFx();
}

/**
 * Create an info popup to display a message
 * @param {String} message
 */
function createInfoMessage(message){
	$("body").append("<div id='info-box' class='ui-widget-header ui-corner-all auto-slide' >"+message+"</div>")
	_autoFx();
}

/**
 * Check if a flahs player is found in the plugins list
 * @return {boolean}
 */
function isFlashPluginEnabled(){
	if($.browser.msie){
		var hasFlash = false; 
		try {   
			var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');   
			if(fo) hasFlash = true; 
		}
		catch(e){   
			if(navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) hasFlash = true; 
		} 
		return hasFlash;
	}
	else{
		if(navigator.plugins != null && navigator.plugins.length > 0){
			for(i in navigator.plugins){
				if(/(Shockwave|Flash)/i.test(navigator.plugins[i]['name'])){
					return true;
				}
			}
		}
	}
	return false;
}