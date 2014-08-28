/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'lodash', 
    'context',
    'uiBootstrap',
    'jsTree/plugins/jquery.tree.contextmenu',
], function($, _, context, uiBootstrap){


    /**
     * @exports layout/tree
     */
    var treeFactory = function($elt, url, options){

        var lastOpened;
        var lastSelected;

        options = options || {};

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
                theme_path : context.taobase_www + 'js/lib/jsTree/themes/css/style.css'
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

                /**
                 * Additionnal parameters to send to the server to retrieve data
                 */
                beforedata: function($node) {

                    var params = _.clone(serverParams);
                    if($node && $node.length){
                        params.classUri = $node.attr('id');
                    }
                    params.selected = options.selectNode;

                    return params;
                },

                //when we receive the data
                ondata: function(data, tree) {

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

                    if (options.selectNode) {
                        tree.select_branch($("li[id='" + options.selectNode + "']"));
                        options.selectNode = false;
                    } else {
                        tree.open_branch($("li.node-class:first"));
                    }
                        
                    $elt.trigger('change.taotree');
                    $elt.trigger('ready.taotree');
                },

                beforeopen: function(node) {
                    //TODO store this in the browser
                    lastOpened = node;
                },

                //when a node is selected
                onselect: function(node, tree) {

                    var uri, classUri;
                    var $node           = $(node);
                    var nodeId          = $node.attr('id');
                    var $parentNode     = tree.parent($node);

                    $('a.clicked', $elt).each(function() {
                        if ($(this).parent('li').attr('id') !==  nodeId) {
                            $(this).removeClass('clicked');
                        }
                    });

                    if ($node.hasClass('node-class')) {
                        if ($node.hasClass('closed')) {
                            tree.open_branch($node);
                        }
                        classUri = nodeId;
                    }

                    if ($node.hasClass('node-instance')){
                        uri = nodeId;
                        classUri = $parentNode.attr('id');
                    }

                    $elt
                      .trigger('select.taotree', [{
                        uri : uri,
                        classUri : classUri 
                    }])
                      .trigger('change.taotree', [{
                        uri : uri,
                        classUri : classUri 
                    }]);

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

                    $elt.trigger('change.taotree');
                }
            }
        };

        //list of events callbacks to be bound to the tree       
        var events = {
            'addnode' : function(data){
                var tree =  $.tree.reference($elt);
                var parentNode = tree.get_node($('#' + data.parent, $elt).get(0));

                var cssClasses = data.cssClass;
                if(data.cssClass === 'node-instance' && options.instanceClass){
                    cssClasses += ' ' + options.instanceClass;
                }

                tree.select_branch(
                    tree.create({
                        data: data.label,
                        attributes: {
                            'id': data.id,
                            'class': cssClasses
                        }
                    }, parentNode)
                );
           },

            'removenode' : function(data){
                var tree =  $.tree.reference($elt);
                var node = tree.get_node($('#' + data.id, $elt).get(0));
                tree.remove(node);
           }
        };

        //bind events 
        _.forEach(events, function(callback, name){
            $elt.on(name + '.taotree', function(){
                callback.apply(this, Array.prototype.slice.call(arguments, 1));
            });
        });

        // workaround to fix dublicate tree bindings on multiple page loads
        //TODO check data-attr
        if (!$elt.hasClass('tree')) {
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
