/**
 * init common form controls
 */
function _initFormNavigation(){
	
	//submit the form by ajax into the form container
	$("form").submit(function(){
		myForm = $(this);
		try{
			loading();
			if (myForm.attr('enctype') == 'multipart/form-data' && myForm.find(".file-uploader")) {
				return false;
			}
			else {
				$(getMainContainerSelector()).load(myForm.attr('action'), myForm.serializeArray(), loaded());
			}
			window.location = '#form-title';
		}
		catch(exp){}
		return false;
	});
	
	$("form").each(function(){
		var myForm = $(this);
		try{
			if (myForm.attr('enctype') == 'multipart/form-data' && myForm.find(".file-uploader")) {
				uploaderId = $(".file-uploader:first").attr('id');
				var myAjaxUploader = new AjaxUpload(uploaderId, {
					action: 	myForm.attr('action'),
					name: 		uploaderId,
					responseType: 'text/html',
					autoSubmit: true,
					onSubmit : function(file, extension){
						this.disable();
						loading();
						formData = myForm.serializeArray()
						data = {};
						for (i in formData){
							data[formData[i]['name']] = formData[i]['value']; 
						}
						myAjaxUploader.setData(data);
					},
					onComplete: function(file, response) {
						$(getMainContainerSelector()).html(response);
						loaded();
					}
				});
			}
		}
		catch(exp){}
		return false;
	});
	
	//revert form button
	$(".form-reverter").click(function(){
		if ($("#uri")) {
			GenerisTreeClass.selectTreeNode($("#uri").val());
		}
		else if ($("#classUri")) {
			GenerisTreeClass.selectTreeNode($("#classUri").val());
		}
	})
}

/**
 * init special forms controls
 */
function _initForms(){
	
	
	//map the wysiwyg editor to the html-area fields
	$('.html-area').each(function(){
		if($(this).css('display') != 'none') {
			$(this).wysiwyg();
		}
	});
	
	//open the authoring tool on the authoringOpener button
	$('.authoringOpener').click(function(){
		if(ctx_extension){
		 	url = '/' + ctx_extension + '/' + ctx_module + '/';
			tabName = ctx_module.toLowerCase() + '_authoring';
		 }
		url += 'authoring';
		index = getTabIndexByName(tabName);
		if(index > -1){
			if($("#uri") && $("#classUri")){
				tabs.tabs('url', index, url + '?uri=' + $("#uri").val() +'&classUri=' + $("#classUri").val());
				tabs.tabs('enable', index);
				tabs.tabs('select', index);
			}
		}
	});
	
	function removeGroup(){
		if (confirm('Please confirm property deletion!')) {
			groupNode = $(this).parents(".form-group").get(0);
			if (groupNode) {
				$(groupNode).remove();
			}
		}
	}
	
	//property form group controls
	$('.form-group').each(function(){
		var formGroup = $(this);
		if(/property\_[0-9]+$/.test(formGroup.attr('id'))){
			var child = formGroup.children("div:first");
			
			if(!formGroup.hasClass('form-group-opened')){
				child.hide();
			}
			
			//toggle control
			toggeler = $("<span class='form-group-control ui-icon ui-icon-circle-plus' title='expand' style='right:48px;'></span>");			
			toggeler.click(function(){
				var control = $(this);
				if(child.css('display') == 'none'){
					child.show('slow');
					control.removeClass('ui-icon-circle-plus');
					control.addClass('ui-icon-circle-minus');
					control.attr('title', 'hide property');
				}
				else{
					child.hide('slow');
					control.removeClass('ui-icon-circle-minus');
					control.addClass('ui-icon-circle-plus');
					control.attr('title', 'show property');
				}
			})
			formGroup.prepend(toggeler);
			
			//delete control
			if (/^property\_[0-9]+/.test(formGroup.attr('id'))) {
				deleter = $("<span class='form-group-control ui-icon ui-icon-circle-close' title='Delete' style='right:24px;'></span>");
				deleter.click(removeGroup);
				formGroup.prepend(deleter);
			}
		}
	});
	
	//property delete button 
	 $(".property-deleter").click(removeGroup);
	 
	 //property add button
	 $(".property-adder").click(function(){
		 var groupNode = $(this).parents(".form-group").get(0);
		 if(ctx_extension){
		 	url = '/' + ctx_extension + '/' + ctx_module + '/';
		 }
		 url += 'addClassProperty';
		 var eltId = this.id;
		var index = ($(".form-group").size() - 1);
		 if (groupNode) {
		 	$.ajax({
		 		url: url,
		 		type: "POST",
		 		data: {
		 			index: index,
		 			classUri: $("#classUri").val()
		 		},
		 		dataType: 'html',
		 		success: function(response){
					$(groupNode).before(response)
					formGroupElt = $("#property_" + index);
					if(formGroupElt){
						formGroupElt.addClass('form-group-opened');
					}
		 			initNavigation();
					window.location = '#'+eltId;
		 		}
		 	});
		 }
	 })
	 
	 /**
	  * display or not the list regarding the property type
	  */
	 function showPropertyList(){
	 	elt = $(this).parent("div").next("div");
		if(/list$/.test($(this).val())){
			if(elt.css('display') == 'none'){
				elt.show();
			}
		}
		else if(elt.css('display') != 'none'){
			elt.css('display', 'none');
		}
	 }
	 
	 /**
	  * by selecting a list the values are displayed or the list editor open
	  */
	 function showPropertyListValues(){
	 	
		if($(this).val() == 'new'){
		
			/*
			 * Open the list editor: a tree in a dialog popup 
			 */
			var rangeId = $(this).attr('id');
			var dialogId = rangeId.replace('_range', '_dialog');
			var treeId = rangeId.replace('_range', '_tree');
			var closerId = rangeId.replace('_range', '_closer');
			
			//dialog content
			elt = $(this).parent("div");
			elt.append("<div id='"+dialogId+"' style='display:none;' >\
							Right click the tree to manage your lists<br />\
							<div id='"+treeId+"' ></div>\
							<div style='text-align:center;'>\
								<a id='"+closerId+"' class='ui-state-default ui-corner-all' href='#'>Ok</a>\
							</div>\
						</div>");
						
			//init dialog events
			$("#"+dialogId).dialog({
				width: 300,
				height: 300,
				autoOpen: false,
				title: 'Manage data list'
			});
			
			$("#"+dialogId).bind('dialogclose', function(event, ui){
				$.tree.reference("#"+treeId).destroy();
				$("#"+dialogId).dialog('destroy');
				$("#"+dialogId).remove();
			});
			$("#"+closerId).click(function(){
				$("#"+dialogId).dialog('close');
			});
			$("#"+dialogId).bind('dialogopen', function(event, ui){
				if(ctx_extension){
				 	url = '/' + ctx_extension + '/' + ctx_module + '/';
				}
				dataUrl 	= url + 'getLists';
				createUrl	= url + 'createList';
				removeUrl	= url + 'removeList';
				renameUrl	= url + 'renameList';
				 
				//create tree
				$("#"+treeId).tree({
					data: {
						type: "json",
						async : true,
						opts: {
							method : "POST",
							url: dataUrl
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
					},
					callback: {
						onrename: function(NODE, TREE_OBJ, RB){
							GenerisTreeClass.renameNode({
								url: renameUrl,
								NODE: NODE,
								TREE_OBJ: TREE_OBJ
							});
						},
						ondestroy: function(TREE_OBJ){
							
							//empty and build again the list drop down on tree destroying
							$("#"+rangeId+" option").each(function(){
								if($(this).val() != "" && $(this).val() != "new"){
									$(this).remove();
								}
							})
							$("#"+treeId+" .node-root .node-class").each(function(){
								$("#"+rangeId+" option[value='new']").before("<option value='"+$(this).attr('id')+"'>"+$(this).children("a:first").text()+"</option>");
							});
							$("#"+rangeId).parent("div").children("ul.form-elt-list").remove();
							$("#"+rangeId).val('');
						}
					},
					plugins: {
						contextmenu: {
							items: {
								remove: {
									icon	: "/tao/views/img/delete.png",
									visible: function(NODE, TREE_OBJ){
										if($(NODE).hasClass('node-root')){
											return false; 
										}
										return TREE_OBJ.check("deletable", NODE);
									},
									action  : function(NODE, TREE_OBJ){
										GenerisTreeClass.removeNode({
											url: removeUrl,
											NODE: NODE,
											TREE_OBJ: TREE_OBJ
										});
										return false;
									}
								},
								create: {
									icon	: "/tao/views/img/add.png",
									visible: function(NODE, TREE_OBJ){
										if($(NODE).hasClass('node-instance')){
											return false; 
										}
										return TREE_OBJ.check("creatable", NODE);
									},
									action  : function(NODE, TREE_OBJ){
										if ($(NODE).hasClass('node-class')) {
											GenerisTreeClass.addInstance({
												url: createUrl,
												id: $(NODE).attr('id'),
												NODE: NODE,
												TREE_OBJ: TREE_OBJ
											});
										}
										if ($(NODE).hasClass('node-root')) {
											GenerisTreeClass.addClass({
												id: 'root',
												url: createUrl,
												NODE: NODE,
												TREE_OBJ: TREE_OBJ
											});
										}
										return false;
									}
								},
								rename: {
									icon	: "/tao/views/img/rename.png",
									visible: function(NODE, TREE_OBJ){
										if($(NODE).hasClass('node-root')){
											return false; 
										}
										return TREE_OBJ.check("renameable", NODE);
									}
								}
							}
						}
					}
				});
			});
			$("#"+dialogId).dialog('open');
		}
		else{
			
			//load the instances and display them (the list items)
			$(this).parent("div").children("ul.form-elt-list").remove();
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
						$(elt).parent("div").append(html);
					}
				});
			}
		}
	 }
	 
	 //bind function to the drop down
	 $(".property-type").change(showPropertyList);
	 $(".property-type").each(showPropertyList);
	 
	 $(".property-listvalues").change(showPropertyListValues);
	 $(".property-listvalues").each(showPropertyListValues);
	 $(".property-listvalues").each(function(){
	 	var listField = $(this);
		listControl = $("<img title='manage lists' style='cursor:pointer;' />")
		listControl.attr('src', imgPath + '/add.png');
		listControl.click(function(){
			listField.val('new');
			listField.change();
		});
		listControl.insertAfter(listField);
	 });
	 
	 $(".property-listvalues").each(function(){
	 	elt = $(this).parent("div");
		if(!elt.hasClass('form-elt-highlight') && elt.css('display') != 'none'){
			elt.addClass('form-elt-highlight');
		}
	 });
}