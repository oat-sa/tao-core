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
    'uri',
    'ui/feedback',
    'ui/dialog/confirm'
], function(module, $, __, _, appContext, section, binder, uri, feedback, confirmDialog) {
    'use strict';

    /**
     * Register common actions.
     *
     * TODO this common actions may be re-structured, split in different files or moved in a more obvious location.
     *
     * @exports layout/actions/common
     */
    var commonActions = function(){

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

                            //backward compat format for jstree
                            $(actionContext.tree).trigger('addnode.taotree', [{
                                uri       : uri.decode(response.uri),
                                label     : response.label,
                                parent    : uri.decode(actionContext.classUri),
                                cssClass  : 'node-class'
                            }]);

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
                        reject(err);
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
                            $(actionContext.tree).trigger('addnode.taotree', [{
                                uri       : uri.decode(response.uri),
                                label     : response.label,
                                parent    : uri.decode(actionContext.classUri),
                                cssClass  : 'node-instance'
                            }]);

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
                        reject(err);
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
                            $(actionContext.tree).trigger('addnode.taotree', [{
                                uri       : uri.decode(response.uri),
                                label     : response.label,
                                parent    : uri.decode(actionContext.classUri),
                                cssClass  : 'node-instance'
                            }]);

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
                        reject(err);
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
                                $(actionContext.tree).trigger('removenode.taotree', [{
                                    id : actionContext.uri || actionContext.classUri
                                }]);
                                return resolve({
                                    uri : actionContext.uri || actionContext.classUri
                                });

                            } else {
                                reject(response.msg || __("Unable to delete the selected resource"));
                            }
                        },
                        error : function (xhr, options, err){
                            reject(err);
                        }
                    });
                }, reject);
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
                            reject(err);
                        }
                    });
                }, reject);
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

                        if (response && response.status === 'diff') {
                            var message = __("Moving this element will replace the properties of the previous class by those of the destination class :");
                            message += "\n";
                            for (var i = 0; i < response.data.length; i++) {
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
                    var $response = $(response);
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
    };

    return commonActions;
});


