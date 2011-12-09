/**
 * Actions component
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require GenerisTreeClass [generis.tree.js]
 * 
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */ 

var GenerisAction = {};

/**
 * conveniance method to select a resource 
 * @param {String} uri
 */
GenerisAction.select = function(uri){
        GenerisTreeClass.selectTreeNode(uri);
};

/**
 * conveniance method to subclass
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.subClass = function(uri, classUri, url){
	var options = getTreeOptions(classUri);
	if(options){
		options.id = classUri;
		options.url = url;
		options.instance.addClass(options.NODE, options.TREE_OBJ, options);
	}
};

/**
 * conveniance method to instanciate
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.instanciate = function (uri, classUri, url){
	var options = getTreeOptions(classUri);
	if(options){
		options.id = classUri;
		options.url = url;
		options.instance.addInstance(options.NODE, options.TREE_OBJ, options);
	}
};

/**
 * conveniance method to instanciate
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.removeNode = function (uri, classUri, url){
	var options = getTreeOptions(uri);
	if(!options){
		options = getTreeOptions(classUri);
	}
	if(options){
		options.url = url;
		options.instance.removeNode(options.NODE, options.TREE_OBJ, options);
	}
};

/**
 * conveniance method to clone
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.duplicateNode = function (uri, classUri, url){
	var options = getTreeOptions(uri);
	if(options){
		options.url = url;
		options.instance.cloneNode(options.NODE, options.TREE_OBJ, options);
	}
};

/**
 * move a selected node
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.moveNode = function (uri, classUri, url){
	var options = getTreeOptions(uri);
	if(options){
		options.instance.moveInstance(options.NODE, options.TREE_OBJ);
	}
};

/**
 * Open a popup
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.fullScreen = function (uri, classUri, url){
	url += '?uri='+uri+'&classUri='+classUri;
	var width = parseInt($(window).width());
	if(width < 800){
		width = 800;
	}
	var height = parseInt($(window).height()) + 50;
	if(height < 600){
		height = 600;
	}
	var windowOptions = {
		'width' 	: width,
		'height'	: height,
		'menubar'	: 'no',
		'resizable'	: 'yes',
		'status'	: 'no',
		'toolbar'	: 'no',
		'dependent' : 'yes',
		'scrollbar' : 'yes'
	};
	var params = '';
	for (key in windowOptions) {
		params += key + '=' + windowOptions[key] + ',';
	}
	params = params.replace(/,$/, '');
	window.open(url, 'preview', params);
};

/**
 * Add a new property
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.addProperty = function (uri, classUri, url){
	var index = ($(".form-group").size());
	$.ajax({
		url: url,
		type: "POST",
		data: {
			index: index,
			classUri: classUri
		},
		dataType: 'html',
		success: function(response){
			$(".form-group:last").after(response);
			formGroupElt = $("#property_" + index);
			if(formGroupElt){
				formGroupElt.addClass('form-group-opened');
			}
			window.location = '#propertyAdder';
		}
	});
};

/**
 * Load the result table with the tree instances in parameter
 * @deprecated
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.resultTable = function (uri, classUri, url){
	options = getTreeOptions(classUri);
	TREE_OBJ = options.TREE_OBJ;
	NODE = options.NODE;
	
	function getInstances(TREE_OBJ, NODE){
		NODES = new Array();
		$.each(TREE_OBJ.children(NODE), function(i, CNODE){
			if ($(CNODE).hasClass('node-instance')) {
				NODES.push($(CNODE).attr('id'));
			}
			if ($(CNODE).hasClass('node-class')) {
				subNodes = getInstances(TREE_OBJ, CNODE);
				NODES.concat(subNodes);
			}
		});
		return NODES;
	}
	data = {};
	instances = getInstances(TREE_OBJ, NODE);
	
	i=0;
	while(i< instances.length){
		data['uri_'+i] = instances[i];
		i++;
	}
	data.classUri = classUri;
	_load(getMainContainerSelector(UiBootstrap.tabs), url, data);
};

/**
 * init and load the meta data component
 * @param {String} uri
 * @param {String} classUri
 */
GenerisAction.loadMetaData = function(uri, classUri, url){
	$("#comment-form-container").dialog('destroy');
	 $.ajax({
	 	url: url,
		type: "POST",
		data:{uri: uri, classUri: classUri},
		dataType: 'html',
		success: function(response){
			$('#section-meta').html(response);
			$('#section-meta').show();
			
			//meta data dialog
			var commentContainer = $("#comment-form-container");
			if (commentContainer) {
				
				$("#comment-editor").click(function(){
					var commentContainer = $(this).parents('td');
					if($('.comment-area', commentContainer).length == 0){
						
						var commentArea = $("<textarea id='comment-area'></textarea>");
						var commentField = $('span#comment-field', commentContainer);
						commentArea.val(commentField.html())
									.width(parseInt(commentContainer.width()) - 5)
										.height(parseInt(commentContainer.height()));
						commentField.empty();
						commentArea.bind('keypress blur' , function(event){
							if(event.type == 'keypress'){
								if (event.which != '13') {
									return true;
								}
								event.preventDefault();
							}
							if (ctx_extension) {
								url = root_url + '/' + ctx_extension + '/' + ctx_module + '/';
							}
							url += 'saveComment';
							$.ajax({
								url: url,
								type: "POST",
								data: {comment: $(this).val(), uri: $('#uri').val(), classUri:$('#classUri').val() },
								dataType: 'json',
								success: function(response){
									if (response.saved) {
										commentArea.remove();
										commentField.text(response.comment);
									}
								}
							});
						});
						commentContainer.prepend(commentArea);
					}
				});
			}
		}
	});
};