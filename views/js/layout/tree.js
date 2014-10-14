/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery', 
    'lodash',
    'i18n', 
    'context',
    'store',
    'layout/actions',
    'jquery.tree',
    'lib/jsTree/plugins/jquery.tree.contextmenu'
], function($, _, __, context, store, actionManager){

    var pageRange = 30;

    /**
     * @exports layout/tree
     */
    var treeFactory = function($elt, url, options){
        
        options = options || {};

        var lastOpened;
        var permissions = {};

        var moreNode = {
            data : __('More'),
            type : 'more',
            attributes : {
                class : 'more'
            }
        };

        //these are the parameters added to the server call to load data
	    var serverParams = _.defaults(options.serverParameters || {}, {
            hideInstances   :  options.hideInstances || 0,
            filter          : '*',
            offset          : 0,
            limit           : pageRange
        });

        /**
         * Set up the tree using the defined options
         * @private
         */
        var setUpTree  = function setUpTree(){

            //try to load the action instance from the options
            options.actions = _.transform(options.actions, function(result, value, key){
                if(value && value.length){
                    result[key] = actionManager.getBy(value);
                }
            });

            //bind events from the definition below
            _.forEach(events, function(callback, name){
                $elt.on(name + '.taotree', function(){
                    callback.apply(this, Array.prototype.slice.call(arguments, 1));
                });
            });

            // workaround to fix dublicate tree bindings on multiple page loads
            if (!$elt.hasClass('tree')) {

                //create the tree
                $elt.tree(treeOptions);
            }
        };

        /**
         * Options given to the jsTree plugin
         */
        var treeOptions = {

            //data call
            data: {
                type: "json",
                async : true,
                opts: {
                    method : "POST",
                    url: url
                }
            },

            //theme
            ui: {
                theme_name : "css",
                theme_path : context.taobase_www + 'js/lib/jsTree/themes/css/style.css'
            },
        
            //nodes types
            types: {
                "default" : {
                    renameable	: false,
                    deletable	: true,
                    creatable	: true,
                    draggable	: function($node) {
                        return $node.hasClass('node-instance') && options.actions && options.actions.moveInstance;
                    }
                }
            },

            //lifecycle callbacks
            callback: {

                /**
                 * Additional parameters to send to the server to retrieve data.
                 * It uses the serverParams object previously defined
                 * @param {jQueryElement} [$node] - the node that represents a class. Used to add the classUri to the call
                 * @returns {Object} params
                 */
                beforedata: function($node) {
                    var treeData = $elt.data('tree-state');
                    var params = _.clone(serverParams);
                    if($node && $node.length){
                        params.classUri = $node.attr('id');
                    }
                    params.selected = options.selectNode;

                    //check if there is a filter load filter value
                    if(treeData && _.isString(treeData.filter) && treeData.filter.length){
                        params.filter = treeData.filter;
                    }
                    
                    return params;
                },

                /**
                 * Called back once the data are received. 
                 * Used to modify them before building the tree.
                 * 
                 * @param {Object} data - the received data
                 * @param {Object} tree - the tree instance 
                 * @returns {Object} data the modified data
                 */
                ondata: function(data, tree) {
                    
                    //automatically open the children of the received node
                    if (data.children) {
                        data.state = 'open';
                    }

                    computeSelectionAccess(data);
            
                    flattenPermissions(data);

                    needMore(data); 

                    addTitle(data);

                    return data;
                },

                /**
                 * Once the data are loaded and the tree is ready
                 * Used to modify them before building the tree.
                 * 
                 * @param {Object} tree - the tree instance 
                 *
                 * @fires layout/tree#change.taotree
                 */
                onload: function(tree){

                    var treeStore       = store.get('taotree.' + context.section) || {};
                    var $firstClass     = $(".node-class:not(.private):first", $elt);
                    var $firstInstance  = $(".node-instance:not(.private):first", $elt);
                    var $lastSelected;

                    if(options.selectNode){
                         $lastSelected = $('#' + options.selectNode, $elt);
                    }
                    if((!$lastSelected || !$lastSelected.length) && 
                       treeStore && treeStore.lastSelected){
                         $lastSelected = $('#' +  treeStore.lastSelected, $elt);
                    }

                    _.defer(function(){ //needed as jstree seems to doesn't know the callbacks right now...
                        
                        //open the first class
                        tree.open_branch($firstClass);

                        //try to select the last one
                        if($lastSelected && $lastSelected.length && !$lastSelected.hasClass('private')){
                            tree.select_branch($lastSelected);

                        //or the 1st instance
                        } else if ($firstInstance.length) {
                            tree.select_branch($firstInstance);

                        //or the 1st class
                        } else if ($firstClass.length){
                            tree.select_branch($firstClass);
    
                        //or something else
                        } else {
                            tree.select_branch($('.node-class,.node-instance', $elt).get(0));
                        }
                    });
                 
                    /**
                     * The tree state has changed
                     * @event layout/tree#change.taotree
                     * @param {Object} [context] - the tree context (uri, classUri)
                     */       
                    $elt.trigger('change.taotree');
                    $elt.trigger('ready.taotree');
                },


                /**
                 * Before a branch is opened
                 * @param {HTMLElement} node - the opened node
                 */
                beforeopen: function(node) {
                    lastOpened = $(node);
                },

                /**
                 * A node is selected.
                 * 
                 * @param {HTMLElement} node - the opened node
                 * @param {Object} tree - the tree instance 
                 *
                 * @fires layout/tree#change.taotree
                 * @fires layout/tree#select.taotree
                 */
                onselect: function(node, tree) {

                    var action;
                    var $node           = $(node);
                    var nodeId          = $node.attr('id');
                    var $parentNode     = tree.parent($node);
                    var treeStore       = store.get('taotree.' + context.section) || {};
                    var nodeContext     = {
                        permissions : permissions[nodeId] || {}
                    };

                    //mark all unselected
                    $('a.clicked', $elt)
                        .parent('li')
                        .not('[id="' + nodeId + '"]')
                        .removeClass('clicked'); 

                    //the more node makes you load more resources
                    if($node.hasClass('more')){
                        loadMore($node, $parentNode, tree);
                        return false;
                    }

                    //exec the  selectClass action
                    if ($node.hasClass('node-class')) {
                        if ($node.hasClass('closed')) {
                            tree.open_branch($node);
                        }
                        nodeContext.classUri = nodeId;
                        nodeContext.permissions = permissions[nodeId];

                        //execute the selectClass action
                        if(options.actions.selectClass){
                            actionManager.exec(options.actions.selectClass, nodeContext);
                        }
                    }

                    //exec the  selectInstance action
                    if ($node.hasClass('node-instance')){
                        nodeContext.uri = nodeId;
                        nodeContext.classUri = $parentNode.attr('id');

                        //the last selected node is stored into the browser storage
                        treeStore.lastSelected = nodeId; 
                        store.set('taotree.' + context.section, treeStore);

                        //execute the selectInstance action
                        if(options.actions.selectInstance){
                            actionManager.exec(options.actions.selectInstance, nodeContext);
                        }
                    }

                    /**
                     * A node has been selected
                     * @event layout/tree#select.taotree
                     * @param {Object} [context] - the tree context (uri, classUri)
                     */       
                    $elt
                      .trigger('select.taotree', [nodeContext])
                      .trigger('change.taotree', [nodeContext]);

                    return false;
                },

                //when a node is move by drag n'drop
                onmove: function(node, refNode, type, tree, rollback) {
                    if (!options.actions.moveInstance) {
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
                    
                    //set the rollback data
                    $elt.data('tree-state', _.merge($elt.data('tree-state'), {rollback : rollback}));

                    //execute the selectInstance action
                    actionManager.exec(options.actions.moveInstance, {
                        uri: $(node).attr('id'),
                        destinationClassUri: $(refNode).attr('id')
                    });

                    $elt.trigger('change.taotree');
                }
            }
        };

        //list of events callbacks to be bound to the tree       
        var events = {
            
            /**
             * Refresh the tree
             *
             * @event layout/tree#refresh.taotree
             * @param {Object} [data] - some data to bind to the tree
             */
            'refresh' : function(data){
                var treeState;
                var tree =  $.tree.reference($elt);
                if(tree){

                    //update the state with data to be used later (ie. filter value, etc.)
                    treeState = _.merge($elt.data('tree-state') || {}, data);
                    $elt.data('tree-state', treeState);

                    tree.refresh();
                }
            },

            /**
             * Rollback the tree. 
             * The rollback state must have been set in the state previously, otherwise runs a refresh.
             *
             * @event layout/tree#rollback.taotree
             */
            'rollback' : function(){
                var treeState;
                var tree =  $.tree.reference($elt);
                if(tree){
        
                    treeState = $elt.data('tree-state');
                    if(treeState.rollback){
                        tree.rollback(treeState.rollback);

                        //remove the rollback infos.
                        $elt.data('tree-state', _.omit(treeState, 'rollback'));
                    } else {
                        //trigger a full refresh
                        $elt.trigger('refresh.taotree');
                    }
                }
            },

            /**
             * Add a node to the tree. 
             *
             * @event layout/tree#addnode.taotree
             * @param {Object} data - the data about the node to add
             * @param {String} data.parent - the id/uri of the node that will contain the new node
             * @param {String} data.id - the id of the new node
             * @param {String} data.cssClass - the css class for the new node (node-instance or node-class at least).
             */       
            'addnode' : function(data){
                var tree =  $.tree.reference($elt);
                var parentNode = tree.get_node($('#' + data.parent, $elt).get(0));

                tree.select_branch(
                    tree.create({
                        data: data.label,
                        attributes: {
                            'id': data.id,
                            'class': data.cssClass
                        }
                    }, parentNode)
                );
           },

            /**
             * Remove a node from the tree.
             *
             * @event layout/tree#removenode.taotree
             * @param {Object} data - the data about the node to remove
             * @param {String} data.id - the id of the node to remove
             */       
            'removenode' : function(data){
                var tree =  $.tree.reference($elt);
                var node = tree.get_node($('#' + data.id, $elt).get(0));
                tree.remove(node);
           },

            /**
             * Select a node
             *
             * @event layout/tree#selectnode.taotree
             * @param {Object} data - the data about the node to select
             * @param {String} data.id - the id of the node to select
             */       
            'selectnode' : function(data){
                var tree =  $.tree.reference($elt);
                var node = tree.get_node($('#' + data.id, $elt).get(0));
                $('li a', $elt).removeClass('clicked');
                tree.select_branch(node);
            },

            /**
             * Opens a tree branch
             *
             * @event layout/tree#openbranch.taotree
             * @param {Object} data - the data about the node to remove
             * @param {String} data.id - the id of the node to remove
             */       
            'openbranch' : function(data){
                var tree =  $.tree.reference($elt);
                var node = tree.get_node($('#' + data.id, $elt).get(0));
                $('li a', $elt).removeClass('clicked');
                tree.open_branch(node);
            }
        };

    
        /**
         * Check if a node has access to a type of action regarding it's permissions 
         * @private
         * @param {String} actionType - in selectClass, selectInstance, moveInstance and delete
         * @param {Object} node       - the node data as recevied from the server
         * @returns {Boolean} true if the action is allowed 
         */
        var hasAccessTo = function hasAccessTo(actionType, node){
            var action = options.actions[actionType];
            if(node && action && node.permissions && node.permissions[action.name] !== undefined){
                return !!node.permissions[action.name];
            }
            return true;
        };

        /**
         * Check whether the nodes in a tree are selectable. If not, we add the <strong>private</strong> class. 
         * @private
         * @param {Object} node - the tree node as recevied from the server
         */
        var computeSelectionAccess = function(node){
            if(_.isArray(node)){
                _.forEach(node, computeSelectionAccess);
                return;
            } 
            if(node.type){
                if(node.type === 'class' && !hasAccessTo('selectClass', node)){
                    addClassToNode(node, 'private');
                }   
                else if(node.type === 'instance' && !hasAccessTo('selectInstance', node)){
                    addClassToNode(node, 'private');
                }   

            }
            if(node.children){
                _.forEach(node.children, computeSelectionAccess);
            }
        };


        /**
         * Add a title attribute to the nodes
         * @private
         * @param {Object} node - the tree node as recevied from the server
         */
        var addTitle = function addTitle(node){
            if(_.isArray(node)){
                _.forEach(node, addTitle);
                return;
            } 
            if(node.attributes && node.data){
                node.attributes.title = node.data;
            }
            if(node.children){
                _.forEach(node.children, addTitle);
            }
        };

        /**
         * Reads the permissions from tree data to put them into a flat Map as <pre>nodeId : nodePermissions</pre>
         * @private
         * @param {Object} node - the tree node as recevied from the server
         */
        var flattenPermissions = function flattenPermissions(node){
            if(_.isArray(node)){
                _.forEach(node, flattenPermissions);
                return;
            } 
            if(node.attributes && node.attributes.id){
                permissions[node.attributes.id] = node.permissions;
            }
            if(node.children){
                _.forEach(node.children, flattenPermissions);
            }
        };

        

        var needMore = function needMore(node){
           if(_.isArray(node) && lastOpened.length && lastOpened.data('count') > pageRange){
               node.push(moreNode);
           } else {
                if(node.count){
                    node.attributes['data-count'] = node.count;
                    
                    if(node.count > pageRange && node.children){
                       node.children.push(moreNode);
                    }
                }
                if(node.children){
                    _.forEach(node.children, needMore);
                }
           }
        }; 

        var loadMore = function loadMore($node, $parentNode, tree){
            var current     = $parentNode.children('ul').children('li.node-instance').length;
            var count       = $parentNode.data('count');
            var left        = count - current;
			var params      = _.defaults({
				'classUri'      : $parentNode.attr('id'),
				'subclasses'    : 0,
				'offset'        : current,
				'limit'         : left < pageRange ? left : pageRange
			}, serverParams);
    
            $.ajax(tree.settings.data.opts.url, {
                type        : tree.settings.data.opts.method,
                dataType    : tree.settings.data.type,
                async       : tree.settings.data.async,
                data        : params
            }).done(function(response){
                if(response && _.isArray(response.children)){
                    response = response.children;
                }
                if(_.isArray(response)){
                   _.forEach(response, function(newNode){
                        if(newNode.type === 'instance'){   //yes the server send also the class, even though I ask him gently...
                            tree.create(newNode, $parentNode);
                        }
                   });
                   tree.deselect_branch($node);
                   tree.remove($node);
                   if(left - response.length > 0){
                        tree.create(moreNode, $parentNode);
                   }
                }
            });
        };
        
        return setUpTree();
    };

    /**
     * Add a css class to a list of nodes and their children, recursilvely.
     * @param {Array} nodes - the nodes to add the class to
     * @param {String} clazz - the css class
     */
    function addClassToNodes(nodes, clazz) {
        if(nodes.length){
           _.forEach(nodes, function(node){
                addClassToNode(node, clazz);
                if (node.children) {
                    addClassToNodes(node.children, clazz);
                }
            });
        }
    }

    function addClassToNode(node, clazz){
        if(node && node.attributes){
            
            node.attributes['class'] = node.attributes['class'] || '';
            
            if(node.attributes['class'].length) {
                node.attributes['class'] = node.attributes['class'] + ' ' + clazz;
            } else {
                node.attributes['class'] = clazz;
            }
        }
    }



    return treeFactory; 
});
