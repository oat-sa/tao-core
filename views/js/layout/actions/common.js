/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'layout/actions/binder',
    'helpers',
    'layout/search'
],
    function(
        $,
        _,
        binder,
        helpers,
        search
        ){

    /**
     * Register common actions
     * TODO this common actions may be re-structured, split in different files or moved in a more obvious location 
     */

    /**
     * Register the load action: load the url and into the content container
     *
     * @this the action (once register it is bound to an action object)
     *
     * @param {Object} context - the current context
     * @param {String} [context.uri]
     * @param {String} [context.classUri]
     */
    binder.register('load', function load(context){
        helpers._load(helpers.getMainContainerSelector(), this.url, _.pick(context, ['uri', 'classUri']));
    });
    
    /**
     * Register the subClass action: creates a sub class
     *
     * @this the action (once register it is bound to an action object)
     *
     * @param {Object} context - the current context
     * @param {String} context.classUri - the URI of the parent class
     * 
     * @fires layout/tree#addnode.taotree
     */
    binder.register('subClass', function subClass(context){
        $.ajax({
            url: this.url,
            type: "POST",
            data: {classUri: context.classUri, type: 'class'},
            dataType: 'json',
            success: function(response){
                if (response.uri) {
                    $(context.tree).trigger('addnode.taotree', [{
                        'id'        : response.uri, 
                        'parent'    : context.classUri, 
                        'label'     : response.label,
                        'cssClass'  : 'node-class' 
                    }]);
                }
            }
        });
    });

    /**
     * Register the instanciate action: creates a new instance from a class
     *
     * @this the action (once register it is bound to an action object)
     *
     * @param {Object} context - the current context
     * @param {String} context.classUri - the URI of the class' instance
     * 
     * @fires layout/tree#addnode.taotree
     */
    binder.register('instanciate', function instanciate(context){
        $.ajax({
            url: this.url,
            type: "POST",
            data: {classUri: context.classUri, type: 'instance'},
            dataType: 'json',
            success: function(response){
                if (response.uri) {
                    $(context.tree).trigger('addnode.taotree', [{
                        'id'        : response.uri, 
                        'parent'    : context.classUri, 
                        'label'     : response.label,
                        'cssClass'  : 'node-instance' 
                    }]);
                }
            }
        });
    });

    /**
     * Register the duplicateNode action: creates a clone of a node.
     *
     * @this the action (once register it is bound to an action object)
     *
     * @param {Object} context - the current context
     * @param {String} context.uri - the URI of the base instance
     * @param {String} context.classUri - the URI of the class' instance
     * 
     * @fires layout/tree#addnode.taotree
     */
    binder.register('duplicateNode', function duplicateNode(context){
        $.ajax({
            url: this.url,
            type: "POST",
            data: {uri : context.uri, classUri: context.classUri},
            dataType: 'json',
            success: function(response){
                if (response.uri) {
                    $(context.tree).trigger('addnode.taotree', [{
                        'id'        : response.uri, 
                        'parent'    : context.classUri, 
                        'label'     : response.label,
                        'cssClass'  : 'node-instance' 
                    }]);
                }
            }
        });
    });

    /**
     * Register the removeNode action: removes a resource.
     *
     * @this the action (once register it is bound to an action object)
     *
     * @param {Object} context - the current context 
     * @param {String} [context.uri]
     * @param {String} [context.classUri]
     * 
     * @fires layout/tree#removenode.taotree
     */
    binder.register('removeNode', function remove(context){
        var data = _.pick(context, ['uri', 'classUri']);
        $.ajax({
            url: this.url,
            type: "POST",
            data: data,
            dataType: 'json',
            success: function(response){
                if (response.deleted) {
                    $(context.tree).trigger('removenode.taotree', [{
                        id : context.uri || context.classUri 
                    }]);
                }
            }
        });
    });


    /**
     * Register the removeNode action: removes a resource.
     *
     * @this the action (once register it is bound to an action object)
     *
     * @param {Object} context - the current context
     * @param {String} [context.uri]
     * @param {String} [context.classUri]
     *
     * @fires layout/tree#removenode.taotree
     */
    binder.register('launchFinder', function remove(context){


        var data = _.pick(context, ['uri', 'classUri']),

            // used to avoid same query twice
            uniqueValue = data.uri || data.classUri || '',
            $container  = search.getContainer('search');

        if($container.is(':visible')) {
            search.toggle();
            return;
        }

        if($container.data('current') === uniqueValue) {
            search.toggle();
            return;
        }

        if(this.name.toLowerCase() === 'filter') {
            return;
        }

        $.ajax({
            url: this.url,
            type: "GET",
            data: data,
            dataType: 'html',
            success: function(response){
                $container.data('current', uniqueValue);
                search.init(response, uniqueValue);
            }
        });
    });

});


