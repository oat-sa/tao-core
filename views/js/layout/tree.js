define([
    'jquery', 
    'lodash', 
    'context',
    'uiBootstrap',
    'jsTree/plugins/jquery.tree.contextmenu',
], function($, _, context, uiBootstrap){




    var treeFactory = function($elt, url, options){

        var lastOpened;
        var lastSelected;

	    var serverParams = _.defaults(options.serverParameters || {}, {
            hideInstances   :  options.hideInstances || 0,
            filter          : '*',
            offset          : 0,
            limit           : 30
        });
        
        var treeOptions = {
				data: {
					type: "json",
					async : true,
					opts: {
						method : "POST",
						url: url
					}
				},
                ui: {
                    theme_name : "custom",
                    theme_path : context.taobase_www + 'js/lib/jsTree/themes/custom/style.css'
                },
				types: {
					"default" : {
						renameable	: false,
						deletable	: true,
						creatable	: true,
                        draggable	: function($node) {
                            return $node.hasClass('node-instance');
                        }
					}
				},
				callback: {


                    oninit: function(TREE_OBJ) {
                            
                        console.log(' on init ');

                        //TODO context change
                        //instance.callGetSectionActions(undefined, TREE_OBJ);
                    },

                    /**
                     * Additionnal parameters to send to the server to retrieve data
                     */
                    beforedata: function($node) {

                        console.log(' before data ');

                        var params = _.clone(serverParams);
                        if($node && $node.length){
                            params.classUri = $node.attr('id');
                        }
                        params.selected = options.selectNode;

                        return params;
                    },

                    //when we receive the data
                    ondata: function(data, tree) {

                        console.log(' on data ');

                        var nodes = data.children || data;

                        //do some styling
                        if(options.instanceClass){
                            addClassToNodes(nodes, options.instanceClass);
                        }

                        if (options.moveInstanceAction) {
                            addClassToNodes(nodes, 'node-draggable');
                        }

                        //automatically open the children of the received node
                        if (data.children) {
                            data.state = 'open';
                        }

                        return data;
                    },

                    //we open either the last selected node or the 1st branch
                    onload: function(tree){

                        console.log(' on load ');

                        if (options.selectNode) {
                            tree.select_branch($("li[id='" + options.selectNode + "']"));
                            options.selectNode = false;
                        } else {
                            tree.open_branch($("li.node-class:first"));
                        }
                            
                        $elt.trigger('change.taotree');
                    },

					beforeopen: function(node) {
                        console.log(' before open ');
                        //TODO store this in the browser
				        lastOpened = node;
					},

                    //when a node is selected
                    onselect: function(node, tree) {

                        console.log(' select ');

                        var uri, classUri;
                        var $node           = $(node);
                        var nodeId          = $node.attr('id');
                        var $parentNode     = tree.parent($node);

                        $('a.clicked', $elt).each(function() {
                            if ($(this).parent('li').attr('id') !==  nodeId) {
                                $(this).removeClass('clicked');
                            }
                        });

                        //already selected
                        //if (nodeId === options.selectNode) {
                            //return false;
                        //}

                        if ($node.hasClass('node-class')) {
                            if ($node.hasClass('closed')) {
                                tree.open_branch($node);
                            }
                            classUri = nodeId;


                            //TODO trigger edit event for a class
                            //load the editClassAction into the formContainer
                            //helpers._load(instance.options.formContainer, instance.options.editClassAction, instance.data(null, nodeId));
                        }

                        if ($node.hasClass('node-instance')){

                            //TODO trigger edit event for an instance
    
                            uri = nodeId;
                            classUri = $parentNode.attr('id');


                             

                            //load the editInstanceAction into the formContainer
                            //var PNODE = TREE_OBJ.parent(NODE);
                            //helpers._load(instance.options.formContainer, instance.options.editInstanceAction, instance.data(nodeId, $(PNODE).prop('id')));
                        }

                        //if ($(NODE).hasClass('paginate-more')) {
                            //instance.paginateInstances ($(NODE).parent().parent(), TREE_OBJ);
                        //}
                        //if ($(NODE).hasClass('paginate-all')) {
                            //var limit = instance.getMeta(parentNodeId, 'count') - instance.getMeta (parentNodeId, 'displayed');
                            //instance.paginateInstances($(NODE).parent().parent(), TREE_OBJ, {'limit':limit});
                        //}
                         
                        $elt
                          .trigger('select.taotree', [{
                            uri : uri,
                            classUri : classUri 
                        }])
                          .trigger('change.taotree', [{
                            uri : uri,
                            classUri : classUri 
                        }]);

                        //instance.callGetSectionActions(NODE, TREE_OBJ);

//                        lastSelected = $node.attr('id');
                        
                        return false;
                    },

                    //when a node is move by drag n'drop
                    onmove: function(node, refNode, type, tree, rollback) {
                        if (!options.moveInstanceAction) {
                            return false;
                        }

                        //do not move an instance into an instance...
                        if ($(refNode).hasClass('node-instance') && type === 'inside') {
                            $.tree.rollback(rollback);
                            return false;
                        } 
                        

                        if (type === 'after' || type === 'before') {
                            refNode = tree.parent(refNode);
                        }

                            //TODO trigger a move event
 
                            //call the server with the new node position to save the new position
                            //function moveNode(url, data) {
                                //var NODE 		= data.NODE;
                                //var REF_NODE	= data.REF_NODE;
                                //var RB 			= data.RB;
                                //var TREE_OBJ 	= data.TREE_OBJ;
                                //var confirmed = (data.confirmed === true);

                                //$.postJson(url, {
                                    //'uri': data.uri,
                                    //'destinationClassUri':  data.destinationClassUri,
                                    //'confirmed' : confirmed
                                    //},
                                    //function(response) {
                                        //if (response == null) {
                                            //$.tree.rollback(RB);
                                            //return;
                                        //}
                                        //if (response.status == 'diff') {
                                            //var message = __("Moving this element will remove the following properties:");
                                            //message += "\n";
                                            //for (var i = 0; i < response.data.length; i++) {
                                                //if (response.data[i].label) {
                                                    //message += "- " + response.data[i].label + "\n";
                                                //}
                                            //}
                                            //message += "Please confirm this operation.\n";
                                            //if (confirm(message)) {
                                                //data.confirmed = true;
                                                //moveNode(url, data);
                                            //} else {
                                                //$.tree.rollback(RB);
                                            //}
                                        //} else if (response.status == true) {
                                            //$('li a').removeClass('clicked');
                                            //TREE_OBJ.open_branch(NODE);
                                        //} else {
                                            //$.tree.rollback(RB);
                                        //}
                                //});
                            //}
                            //moveNode(instance.options.moveInstanceAction, {
                                    //'uri': $(NODE).prop('id'),
                                    //'destinationClassUri': $(REF_NODE).prop('id'),
                                    //'NODE'		: NODE,
                                    //'REF_NODE'	: REF_NODE,
                                    //'RB'		: RB,
                                    //'TREE_OBJ'	: TREE_OBJ
                                //});

                        $elt.trigger('change.taotree');

                        //instance.callGetSectionActions(NODE, TREE_OBJ);
                    },
                

            }
        };
        
        $elt.on('select.taotree', function(){
            console.log('select ', arguments);
        });

        $elt.on('change.taotree', function(e, data){
            console.log('change ', arguments);
            if(e.namespace === 'taotree'){
                if(data){

                    uiBootstrap.initActions(data.uri, data.classUri);

                } else {

                    uiBootstrap.initActions();

                }
            }
        });

        // workaround to fix dublicate tree bindings on multiple page loads
        //TODO check data-attr
        var classes = $elt.attr('class');
        if (!classes || !classes.test('tree')) {
            console.log(treeOptions);
            $elt.tree(treeOptions);
        }
    };

    /**
     * Add a css class to a list of nodes and their children, recursilvely.
     * @param {Array} nodes - the nodes to add the class to
     * @param {String} clazz - the css class
     */
    function addClassToNodes(nodes, clazz) {
        if(nodes.length){
           _.forEach(nodes, function(node){
 	            if (node.attributes && node.attributes['class'] && 
                    /node\-instance/.test(node.attributes['class'])) {
						
    				node.attributes['class'] = node.attributes['class'] + ' ' + clazz;
				}
                if (node.children) {
                    addClassToNodes(node.children, clazz);
                }
            });
        }
    }

    return treeFactory; 

});
