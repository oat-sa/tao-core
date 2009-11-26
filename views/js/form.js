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
			var treeId = $(this).attr('id').replace('_range', '_tree');
			elt = $(this).parent("div");
			elt.append("<div id='"+dialogId+"' style='display:none;' ><div id='"+treeId+"' ></div></div>");
			$("#"+dialogId).dialog({
				width: 500,
				height: 300,
				autoOpen: false,
				title: 'Manage list'
			});
			$("#"+dialogId).bind('dialogclose', function(event, ui){
				$("#"+dialogId).dialog('destroy');
				$("#"+dialogId).remove();
			});
			$("#"+dialogId).bind('dialogopen', function(event, ui){
				if(ctx_extension){
				 	url = '/' + ctx_extension + '/' + ctx_module + '/';
				 }
				 url += '';
				$("#"+treeId).tree({
					data: {
						type: "json",
						async : true,
						opts: {
							method : "POST",
							url: url
						}
					},
					types: {
					 "default" : {
							renameable	: true,
							deletable	: true,
							creatable	: true,
							draggable	: false
						}
					},
					ui: {
						theme_name : "custom"
					}
				});
			});
			
			$("#"+dialogId).dialog('open');
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
	/*
	$('textarea.html-area').each(function(){
		if($(this).css('display') != 'none') {
			$('textarea.html-area').tinymce(simpleEditorOptions);
		}
	});
	*/
	
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