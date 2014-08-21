/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'lodash', 'layout/actions/binder', 'helpers'], function($, _, binder, helpers){

    /**
     * Register common actions
     * TODO this common actions may be re-structured, split in different files or moved in a more obvious location 
     */

    /**
     * Load Action 
     */
    binder.register('load', function load(context){
        helpers._load(helpers.getMainContainerSelector(), this.url, context);
    });
    
    /**
     * Add Class 
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
     * Instantiate
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
     * Instantiate
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
     * Remove
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

});
