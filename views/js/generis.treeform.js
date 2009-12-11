/**
 * GenerisTreeClass is a easy to use container for the tree widget, 
 * it provides the common behavior for a Class/Instance Rdf resource tree
 * 
 * @example new GenerisTreeClass('#tree-container', 'myData.php', {});
 * @see GenerisTreeClass.defaultOptions for options example
 * 
 * @require jquery >= 1.3.2 [http://jquery.com/]
 * @require jstree >= 0.9.9 [http://jstree.com/]
 * 
 * @author Bertrand Chevrier, <chevrier.bertrand@gmail.com>
 */ 


/**
 * Constructor
 * @param {String} selector the jquery selector of the tree container
 * @param {String} dataUrl the url to call, it must provide the json data to populate the tree 
 * @param {Object} options
 */
function GenerisTreeFormClass(selector, dataUrl, options){
	try{
		this.selector = selector;
		this.options = options;
		this.dataUrl = dataUrl;
		
		var instance = this;
		
		this.treeOptions = {
			data: {
				type: "json",
				async : true,
				opts: {
					method : "POST",
					url: instance.dataUrl
				}
			},
			types: {
			 "default" : {
					renameable	: false,
					deletable	: false,
					creatable	: false,
					draggable	: false
				}
			},
			ui: {
				theme_name : "checkbox"
			},
			plugins : {
				checkbox : { }
			}
		};
		
		//create the tree
		$(selector).tree(this.treeOptions);
		
		$("#saver-action-" + this.options.actionId).click(function(){
			var toSend = [];
			$.each( $.tree.plugins.checkbox.get_checked($.tree.reference(instance.selector)) , function(i, NODE){
				if(NODE.hasClass('node-instance')){
					toSend[i] = NODE.attr('id');
				}
			});
			
			console.log(toSend);
			
			$.ajax({
			 	url: instance.options.saveUrl,
				type: "POST",
				data: toSend,
				dataType: 'json',
				success: function(response){
					if(response.saved){
						alert('Tree saved');
					}
				}
			 });
		});
	}
	catch(exp){
		console.log(exp);
	}
}