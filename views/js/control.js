/**
 * initialize the naviguation into the ui components 
 */
function loadControls(){
	
	//left menu trees init by loading the tab content
	$.ajax({
		url: '/tao/Main/getSectionTrees',
		type: "GET",
		data: {
			section: $("li a[href=#" + $('.ui-tabs-panel')[$tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
		},
		dataType: 'html',
		success: function(response){
			$('#section-trees').html(response);
			initNavigation();
		}
	});
	//left menu actions init by loading the tab content
	$.ajax({
		url: '/tao/Main/getSectionActions',
		type: "GET",
		data: {
			section: $("li a[href=#" + $('.ui-tabs-panel')[$tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
		},
		dataType: 'html',
		success: function(response){
			$('#section-actions').html(response);
			initNavigation();
		}
	});
	//bottom grid init by loading the tab content
	$.ajax({
		url: '/tao/Main/getSectionGrid',
		type: "GET",
		data: {
			section: $("li a[href=#" + $('.ui-tabs-panel')[$tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
		},
		dataType: 'html',
		success: function(response){
			$('#section-grid').html(response);
			initNavigation();
		}
	});
	
	initNavigation();
}

/**
 * change links and form behavior to load  content via ajax
 */
function initNavigation(){
	//links load the content into the main container
	$('a.nav').click(function() { 	
		 _load("#main-container", this.href);
		 return false;
	});
	//submit the form by ajax into the form container
	$("form").submit(function(){
		try{
			loading();
			$("#main-container").load(
				_href($(this).attr('action')),
				$(this).serializeArray(),
				loaded()
			);
		}
		catch(exp){console.log(exp);}
		return false;
	});
	
	
	$.ui.dialog.defaults.bgiframe = true;		//fix ie6 bug
	$("a#settings-loader").click(function(){
		try{
			var settingTitle = $(this).text();
			$("#settings-form").load(this.href).dialog({
				title: settingTitle,
				width: 500,
				height: 300
			});
		}
		catch(exp){console.log(exp);}
		
		return false;
	});
	_autoFx();
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
	$(selector).load(url, data, loaded());
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
}

/**
 * Show the loader
 */
function loading(){
	$("#ajax-loading").show('fast');
}

/**
 * Hide the loader
 */
function loaded(){
	$("#ajax-loading").hide('fast');
}

var $tabs = null;
$(function(){
	//create tabs
	$tabs = $('#tabs').tabs({load: loadControls});
});