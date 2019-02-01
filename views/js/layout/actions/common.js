/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014-2017 Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'module',
    'jquery',
    'i18n',
    'lodash',
    'context',
    'layout/section',
    'layout/actions/binder',
    'layout/permissions',
    'provider/resources',
    'ui/destination/selector',
    'uri',
    'ui/feedback',
    'ui/dialog/confirm',
    'util/httpErrorParser'
], function(module, $, __, _, appContext, section, binder, permissionsManager, resourceProviderFactory, destinationSelectorFactory, uri, feedback, confirmDialog, httpErrorParser) {
    'use strict';

    var messages = {
        confirmMove: __('The properties of the source class will be replaced by those of the destination class. This might result in a loss of metadata. Continue anyway?')
    };

    /**
     * Cleans up the main panel and creates a container
     * @returns {jQuery}
     */
    var emptyPanel = function cleanupPanel() {
        section.current().updateContentBlock('<div class="main-container flex-container-form-main"></div>');
        return $(section.selected.panel).find('.main-container');
    };

    /**
     * Register common actions.
     *
     * TODO this common actions may be re-structured, split in different files or moved in a more obvious location.
     *
     * @exports layout/actions/common
     */
    var commonActions = function commonActions(){

        /**
         * Register the load action: load the url and into the content container
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         */
        binder.register('load', function load(actionContext){
            section.current().loadContentBlock(this.url, _.pick(actionContext, ['uri', 'classUri', 'id']));
        });

        /**
         * Register the load class action: load the url into the content container
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.classUri - the URI of the parent class
         */
        binder.register('loadClass', function load(actionContext){
            section.current().loadContentBlock(this.url, {classUri: actionContext.classUri, id: uri.decode(actionContext.classUri)});
        });

        /**
         * Register the subClass action: creates a sub class
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.classUri - the URI of the parent class
         * @returns {Promise<Object>} resolves with the new class data
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('subClass', function subClass(actionContext){
            var classUri = uri.decode(actionContext.classUri);
            var self = this;
            return new Promise( function(resolve, reject) {
                $.ajax({
                    url: self.url,
                    type: "POST",
                    data: {classUri: actionContext.classUri, id: classUri, type: 'class'},
                    dataType: 'json',
                    success: function(response){
                        if (response.uri) {

                            if(actionContext.tree){
                                $(actionContext.tree).trigger('addnode.taotree', [{
                                    uri       : uri.decode(response.uri),
                                    label     : response.label,
                                    parent    : uri.decode(actionContext.classUri),
                                    cssClass  : 'node-class'
                                }]);
                            }

                            //resolve format (resourceSelector)
                            return resolve({
                                uri       : uri.decode(response.uri),
                                label     : response.label,
                                classUri  : uri.decode(actionContext.classUri),
                                type      : 'class'
                            });
                        }
                        return reject(new Error(__('Adding the new class has failed')));
                    },
                    error : function (xhr, options, err){
                        reject(httpErrorParser.parse(xhr, options, err));
                    }
                });
            });
        });

        /**
         * Register the instanciate action: creates a new instance from a class
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.classUri - the URI of the class' instance
         * @returns {Promise<Object>} resolves with the new instance data
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('instanciate', function instanciate(actionContext){
            var self = this;
            var classUri = uri.decode(actionContext.classUri);
            return new Promise( function(resolve, reject) {
                $.ajax({
                    url: self.url,
                    type: "POST",
                    data: {classUri: actionContext.classUri, id: classUri, type: 'instance'},
                    dataType: 'json',
                    success: function(response){
                        if (response.uri) {

                            //backward compat format for jstree
                            if(actionContext.tree){
                                $(actionContext.tree).trigger('addnode.taotree', [{
                                    uri       : uri.decode(response.uri),
                                    label     : response.label,
                                    parent    : uri.decode(actionContext.classUri),
                                    cssClass  : 'node-instance'
                                }]);
                            }

                            //resolve format (resourceSelector)
                            return resolve({
                                uri       : uri.decode(response.uri),
                                label     : response.label,
                                classUri  : uri.decode(actionContext.classUri),
                                type      : 'instance'
                            });
                        }
                        return reject(new Error(__('Adding the new resource has failed')));
                    },
                    error : function (xhr, options, err){
                        reject(httpErrorParser.parse(xhr, options, err));
                    }
                });
            });
        });

        /**
         * Register the duplicateNode action: creates a clone of a node.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} actionContext.uri - the URI of the base instance
         * @param {String} actionContext.classUri - the URI of the class' instance
         * @returns {Promise<Object>} resolves with the new instance data
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('duplicateNode', function duplicateNode(actionContext){
            var self = this;
            return new Promise( function(resolve, reject) {
                $.ajax({
                    url: self.url,
                    type: "POST",
                    data: {
                        uri: actionContext.id,
                        classUri: uri.decode(actionContext.classUri)
                    },
                    dataType: 'json',
                    success: function(response){
                        if (response.uri) {

                            //backward compat format for jstree
                            if(actionContext.tree){
                                $(actionContext.tree).trigger('addnode.taotree', [{
                                    uri       : uri.decode(response.uri),
                                    label     : response.label,
                                    parent    : uri.decode(actionContext.classUri),
                                    cssClass  : 'node-instance'
                                }]);
                            }

                            //resolve format (resourceSelector)
                            return resolve({
                                uri       : uri.decode(response.uri),
                                label     : response.label,
                                classUri  : uri.decode(actionContext.classUri),
                                type      : 'instance'
                            });
                        }
                        return reject(new Error(__('Node duplication has failed')));
                    },
                    error : function (xhr, options, err){
                        reject(httpErrorParser.parse(xhr, options, err));
                    }
                });
            });
        });

        /**
         * Register the removeNode action: removes a resource.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         *
         * @fires layout/tree#removenode.taotree
         */
        binder.register('removeNode', function remove(actionContext){
            var self = this;
            var data = {};
            var tokenName = module.config().xsrfTokenName;

            data.uri        = uri.decode(actionContext.uri);
            data.classUri   = uri.decode(actionContext.classUri);
            data.id         = actionContext.id;
            data[tokenName] = $.cookie(tokenName);

            return new Promise( function (resolve, reject){
                confirmDialog(__("Please confirm deletion"), function accept(){
                    $.ajax({
                        url: self.url,
                        type: "POST",
                        data: data,
                        dataType: 'json',
                        success: function(response){
                            if (response.deleted) {
                                if(actionContext.tree){
                                    $(actionContext.tree).trigger('removenode.taotree', [{
                                        id : actionContext.uri || actionContext.classUri
                                    }]);
                                }
                                return resolve({
                                    uri : actionContext.uri || actionContext.classUri
                                });

                            } else {
                                reject(response.msg || __("Unable to delete the selected resource"));
                            }
                        },
                        error : function (xhr, options, err){
                            reject(httpErrorParser.parse(xhr, options, err));
                        }
                    });
                }, function cancel(){
                    reject({ cancel : true });
                });
            });
        });

        /**
         * Register the removeNodes action: removes multiple resources
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object[]|Object} actionContexts - single or multiple action contexts
         * @returns {Promise<String[]>} with the list of deleted ids/uris
         */
        binder.register('removeNodes', function removeNodes(actionContexts){
            var self = this;
            var tokenName = module.config().xsrfTokenName;
            var confirmMessage = '';
            var data = {};
            var classes;
            var instances;

            if(!_.isArray(actionContexts)){
                actionContexts = [actionContexts];
            }

            classes = _.filter(actionContexts, { type : 'class' });
            instances = _.filter(actionContexts, { type : 'instance' });

            //TODO do not use cookies !
            data[tokenName] = $.cookie(tokenName);
            data.ids = _.pluck(actionContexts, 'id');

            if(actionContexts.length === 1){
                confirmMessage = __('Please confirm deletion');
            } else if(actionContexts.length > 1){
                if(instances.length){
                    if(instances.length === 1){
                        confirmMessage = __('an instance');
                    } else {
                        confirmMessage = __('%s instances', instances.length);
                    }
                }
                if(classes.length){
                    if(confirmMessage){
                        confirmMessage += __(' and ');
                    }
                    if(classes.length === 1){
                        confirmMessage = __('a class');
                    } else {
                        confirmMessage += __('%s classes', classes.length);
                    }
                }
                confirmMessage =  __('Please confirm deletion of %s.', confirmMessage);
            }

            return new Promise( function (resolve, reject){
                confirmDialog(confirmMessage, function accept(){
                    $.ajax({
                        url: self.url,
                        type: "POST",
                        data: data,
                        dataType: 'json',
                        success: function(response){
                            if (response.success && response.deleted) {
                                resolve(response.deleted);
                            } else {
                                reject(new Error(response.msg || __("Unable to delete the selected resources")));
                            }
                        },
                        error : function (xhr, options, err){
                            reject(httpErrorParser.parse(xhr, options, err));
                        }
                    });
                }, function cancel(){
                    reject({ cancel : true });
                });
            });
        });

        /**
         * Register the moveNode action: moves a resource.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         */
        binder.register('moveNode', function remove(actionContext){
            var data = _.pick(actionContext, ['id', 'uri', 'destinationClassUri', 'confirmed']);

            //wrap into a private function for recusion calls
            var _moveNode = function _moveNode(url){
                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    dataType: 'json',
                    success: function(response){
                        var message;
                        var i;
                        if (response && response.status === 'diff') {
                            message = __("Moving this element will replace the properties of the previous class by those of the destination class :");
                            message += "\n";
                            for (i = 0; i < response.data.length; i++) {
                                if (response.data[i].label) {
                                    message += "- " + response.data[i].label + "\n";
                                }
                            }
                            message += __("Please confirm this operation.") + "\n";

                            if (window.confirm(message)) {
                                data.confirmed = true;
                                return  _moveNode(url, data);
                            }
                        } else if (response && response.status === true) {
                            //open the destination branch
                            $(actionContext.tree).trigger('openbranch.taotree', [{
                                id : actionContext.destinationClassUri
                            }]);
                            return;
                        }

                        //ask to rollback the tree
                        $(actionContext.tree).trigger('rollback.taotree');
                    }
                });
            };
            _moveNode(this.url, data);
        });

        /**
         * Register the launchEditor action.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         *
         * @fires layout/tree#removenode.taotree
         */
        binder.register('launchEditor', function launchEditor(actionContext){

            var data = _.pick(actionContext, ['id']);
            var wideDifferenciator = '[data-content-target="wide"]';

            $.ajax({
                url: this.url,
                type: "GET",
                data: data,
                dataType: 'html',
                success: function(response){
                    var $response = $($.parseHTML(response, document, true));
                    //check if the editor should be displayed widely or in the content area
                    if($response.is(wideDifferenciator) || $response.find(wideDifferenciator).length){
                        section.create({
                            id : 'authoring',
                            name : __('Authoring'),
                            url : this.url,
                            content : $response,
                            visible : false
                        })
                        .show();
                    } else {
                        section.updateContentBlock($response);
                    }
                }
            });
        });

        /**
         * Register the copyTo action: select a destination class to copy a resource
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object[]|Object} actionContext - single or multiple action contexts
         * @returns {Promise<String>} with the new resource URI
         */
        binder.register('copyTo',  function copyTo (actionContext){
            //create the container manually...
            var $container = emptyPanel();

            //get the resource provider configured with the action URL
            var resourceProvider = resourceProviderFactory({
                copyTo : {
                    url : this.url
                }
            });

            return new Promise( function (resolve, reject){

                //set up a destination selector
                destinationSelectorFactory($container, {
                    classUri: actionContext.rootClassUri,
                    preventSelection : function preventSelection(nodeUri, node, $node){
                        //prevent selection on nodes without WRITE permissions
                        if( $node.length &&  $node.data('access') === 'partial' || $node.data('access') === 'denied'){
                            if(! permissionsManager.hasPermission(nodeUri, 'WRITE') ) {
                                feedback().warning(__('You are not allowed to write in the class %s', node.label));
                                return true;
                            }
                        }
                        return false;
                    }
                })
                .on('query', function(params) {
                    var self = this;

                    //asks only classes
                    params.classOnly = true;
                    resourceProvider
                        .getResources(params, true)
                        .then(function(resources){
                            //ask the server the resources from the component query
                            self.update(resources, params);
                        })
                        .catch(function(err){
                            self.trigger('error', err);
                        });
                })
                .on('select', function(destinationClassUri){
                    var self = this;
                    if(!_.isEmpty(destinationClassUri)){
                        this.disable();

                        resourceProvider
                            .copyTo(actionContext.id, destinationClassUri)
                            .then(function(result){
                                if(result && result.uri){

                                    feedback().success(__('Resource copied'));

                                    //backward compatible for jstree
                                    if(actionContext.tree){
                                        $(actionContext.tree).trigger('refresh.taotree', [result]);
                                    }
                                    return resolve(result);
                                }
                                return reject(new Error(__('Unable to copy the resource')));
                            })
                            .catch(function(err){
                                self.trigger('error', err);
                            });
                    }
                })
                .on('error', reject);
            });
        });

        /**
         * Register the moveTo action: select a destination class to move resources
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object|Object[]} actionContext - multiple action contexts
         * @returns {Promise<String>} with the destination class URI
         */
        binder.register('moveTo', function moveTo(actionContext) {
            //create the container manually...
            var $container = emptyPanel();

            //backward compatible for jstree
            var tree = actionContext.tree;

            //get the resource provider configured with the action URL
            var resourceProvider = resourceProviderFactory({
                moveTo: {
                    url: this.url
                }
            });

            if (!_.isArray(actionContext)) {
                actionContext = [actionContext];
            }

            return new Promise(function (resolve, reject) {
                var rootClassUri = _.pluck(actionContext, 'rootClassUri').pop();
                var selectedUri = _.pluck(actionContext, 'id');

                //set up a destination selector
                destinationSelectorFactory($container, {
                    title: __('Move to'),
                    actionName: __('Move'),
                    icon: 'move-item',
                    classUri: rootClassUri,
                    confirm: messages.confirmMove,
                    preventSelection: function preventSelection(nodeUri, node, $node) {
                        var uriList = [];

                        //prevent selection on nodes without WRITE permissions
                        if ($node.length && $node.data('access') === 'partial' || $node.data('access') === 'denied') {
                            if (!permissionsManager.hasPermission(nodeUri, 'WRITE')) {
                                feedback().warning(__('You are not allowed to write in the class %s', node.label));
                                return true;
                            }
                        }

                        uriList = [nodeUri];
                        $node.parents('.class').each(function() {
                            if (this.dataset.uri !== rootClassUri) {
                                uriList.push(this.dataset.uri);
                            }
                        });

                        //prevent selection on nodes that are already the containers of the resources or the resources themselves
                        if (_.intersection(selectedUri, uriList).length) {
                            feedback().warning(__('You cannot move the selected resources in the class %s', node.label));
                            return true;
                        }

                        return false;
                    }
                })
                    .on('query', function (params) {
                        var self = this;

                        //asks only classes
                        params.classOnly = true;
                        resourceProvider
                            .getResources(params, true)
                            .then(function (resources) {
                                //ask the server the resources from the component query
                                self.update(resources, params);
                            })
                            .catch(function (err) {
                                self.trigger('error', err);
                            });
                    })
                    .on('select', function (destinationClassUri) {
                        var self = this;

                        if (!_.isEmpty(destinationClassUri)) {
                            this.disable();

                            resourceProvider
                                .moveTo(selectedUri, destinationClassUri)
                                .then(function (results) {
                                    var failed = [];
                                    var success = [];

                                    _.forEach(results, function (result, uri) {
                                        var resource = _.find(actionContext, {uri: uri});
                                        if (result.success) {
                                            success.push(resource);
                                        } else {
                                            failed.push(result.message);
                                        }
                                    });

                                    if (!success.length) {
                                        feedback().error(__('Unable to move the resources'));
                                    } else if (failed.length) {
                                        feedback().warning(__('Some resources have not been moved: %s', failed.join(', ')));
                                    } else {
                                        feedback().success(__('Resources moved'));
                                    }

                                    //backward compatible for jstree
                                    if (tree) {
                                        $(tree).trigger('refresh.taotree', [destinationClassUri]);
                                    }
                                    return resolve(destinationClassUri);
                                })
                                .catch(function (err) {
                                    self.trigger('error', err);
                                });
                        }
                    })
                    .on('error', reject);
            });
        });
    };

    return commonActions;
});


