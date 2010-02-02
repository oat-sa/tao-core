/**
 * Actions component
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require GenerisTreeClass [generis.tree.js]
 * 
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */ 

var GenerisAction = {};

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
		GenerisTreeClass.addClass(options);
	}
}

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
		GenerisTreeClass.addInstance(options);
	}
}

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
		GenerisTreeClass.removeNode(options);
	}
}

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
		GenerisTreeClass.cloneNode(options);
	}
}

/**
 * Open a popup
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.fullScreen = function (uri, classUri, url){
	url += '?uri='+uri+'&classUri='+classUri;
	window.open(url, 'tao', 'width=800,height=600,menubar=no,toolbar=no');
}

/**
 * Add a new property
 * @param {String} uri
 * @param {String} classUri
 * @param {String} url
 */
GenerisAction.addProperty = function (uri, classUri, url){
	var index = ($(".form-group").size() - 1);
	$.ajax({
		url: url,
		type: "POST",
		data: {
			index: index,
			classUri: classUri
		},
		dataType: 'html',
		success: function(response){
			console.log($(".form-group:last"));
			console.log(response);
			$(".form-group:last").after(response);
			formGroupElt = $("#property_" + index);
			if(formGroupElt){
				formGroupElt.addClass('form-group-opened');
			}
			window.location = '#propertyAdder';
		}
	});
}

/**
 * Load the result table with the tree instances in parameter
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
}
