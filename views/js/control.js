/**
 * initialize the naviguation into the ui components 
 */
function uiNaviguate(){
	
	//left menu action init by loading the tab content
	$.ajax({
		url: '/tao/Main/getSectionActions',
		type: "GET",
		data: {
			section: $("li a[href=#" + $('.ui-tabs-panel')[$tabs.tabs('option', 'selected')].id + "]:first").text()		//get the link text of the selected tab
		},
		dataType: 'html',
		success: function(response){
			$('#section-actions').html(response);
			$('a.nav').click(function() {	//links load the content into the main container
				 _load("#main-container", this.href);
				 return false;
			});
		}
	});
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
	$tabs = $('#tabs').tabs({load: uiNaviguate});
});