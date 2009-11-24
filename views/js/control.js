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
		 _load(getMainContainerSelector(), this.href);
		 return false;
	});
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
	setTimeout(function(){
		$(".auto-slide").slideUp(1500);
	}, 5000);
}
function _initFormNavigation(){
	//submit the form by ajax into the form container
	$("form").submit(function(){
		try{
			loading();
			$(getMainContainerSelector()).load(
				_href($(this).attr('action')),
				$(this).serializeArray(),
				loaded()
			);
			window.location = '#form-title';
		}
		catch(exp){console.log(exp);}
		return false;
	});
	//revert form
	$(".form-reverter").click(function(){
		if ($("#uri")) {
			GenerisTreeClass.selectTreeNode($("#uri").val());
		}
		else if ($("#classUri")) {
			GenerisTreeClass.selectTreeNode($("#classUri").val());
		}
	})
}
function _initForms(){
	 $(".property-deleter").click(function(){
	 	groupNode = $(this).parents(".form-group").get(0);
		if(groupNode){
			$(groupNode).empty();
			$(groupNode).remove();
		}
	 })
	 $(".property-adder").click(function(){
		 var groupNode = $(this).parents(".form-group").get(0);
		 if(ctx_extension){
		 	url = '/' + ctx_extension + '/' + ctx_module + '/';
		 }
		 url += 'addClassProperty';
		 var eltId = this.id;
		 if (groupNode) {
		 	$.ajax({
		 		url: url,
		 		type: "POST",
		 		data: {
		 			index: $(".property-uri").size(),
		 			classUri: $("#classUri").val()
		 		},
		 		dataType: 'html',
		 		success: function(response){
		 			$(groupNode).before(response)
		 			initNavigation();
					window.location = '#'+eltId;
		 		}
		 	});
		 }
	 })
	 
	 function showPropertyList(){
	 	if(/list$/.test($(this).val())){
			elt = $(this).parent("div").next("div");
			if(elt.css('display') == 'none'){
				elt.show("slow");
			}
		}
		else{
			elt = $(this).parent("div").next("div")
			if(elt.css('display') != 'none'){
				elt.hide("slow");
			}
		}
	 }
	 function showPropertyListValues(){
	 	if($(this).val() == 'new'){
			var dialogId = $(this).attr('id').replace('_range', '_dialog');
			elt = $(this).parent("div");
			elt.append("<div id='"+dialogId+"' style='display:none;' ></div>");
			$("#"+dialogId).dialog({
				width: 500,
				height: 300,
				autoOpen: false,
				title: 'Create new list'
			});
			if(ctx_extension){
			 	url = '/' + ctx_extension + '/' + ctx_module + '/';
			 }
			url += 'createNewList';
			$("#"+dialogId).bind('dialogclose', function(event, ui){
				$("#"+dialogId).dialog('destroy');
				$("#"+dialogId).remove();
			});
			
			$("#"+dialogId).load(url, {nc: new Date().getTime()}, function(){
				$("#"+dialogId+" form").live('submit', function(){
					_load("#"+dialogId, $(this).attr('action'), $(this).serializeArray());
					return false;
				})
			}).dialog('open');
		}
		else{
			var classUri = $(this).val();
			if(classUri != ''){
				var elt = this;
				if(ctx_extension){
				 	url = '/' + ctx_extension + '/' + ctx_module + '/';
				 }
				url += 'getInstances';
				$.ajax({
					url: url,
					type: "POST",
					data: {classUri: classUri},
					dataType: 'json',
					success: function(response){
						html = "<ul class='form-elt-list'>";
						for(i in response){
							html += '<li>'+response[i]+'</li>'
						}
						html += '</ul>';
						$(elt).parent("div").children("ul.form-elt-list").remove();
						$(elt).parent("div").append(html);
					}
				});
			}
		}
	 }
	 
	 $(".property-type").change(showPropertyList);
	 $(".property-type").each(showPropertyList);
	 
	 $(".property-listvalues").change(showPropertyListValues);
	 $(".property-listvalues").each(showPropertyListValues);
	 
	 $(".property-listvalues").each(function(){
	 	elt = $(this).parent("div");
		if(!elt.hasClass('form-elt-highlight') && elt.css('display') != 'none'){
			elt.addClass('form-elt-highlight');
		}
	 });
	 
	 $('.html-area').each(function(){
		if($(this).css('display') != 'none') {
			$(this).wysiwyg();
		}
	});
	
	$('.authoringOpener').click(function(){
		index = getTabIndexByName('item_authoring');
		if(index > -1){
			if($("#uri") && $("#classUri")){
				tabs.tabs('url', index, '/taoItems/Items/authoring?uri=' + $("#uri").val() +'&classUri=' + $("#classUri").val());
				tabs.tabs('select', index);
			}
		}
	});
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

var tabs = null;
$(function(){
	//create tabs
	tabs = $('#tabs').tabs({
		load: loadControls, 
		collapsible: true
	});
	
})
