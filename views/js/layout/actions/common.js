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
    'layout/search',
    'layout/filter',
    'uri',
    'ui/feedback',
    'ui/dialog/confirm'
], function(module, $, __, _, appContext, section, binder, search, toggleFilter, uri, feedback, confirmDialog) {
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
                            $(actionContext.tree).trigger('addnode.taotree', [{
                                'uri'       : uri.decode(response.uri),
                                'parent'    : classUri,
                                'label'     : response.label,
                                'cssClass'  : 'node-class'
                            }]);
                            resolve({
                                'uri'       : uri.decode(response.uri),
                                'parent'    : classUri,
                                'label'     : response.label,
                                'type'      : 'class'
                            });
                        }
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
                            $(actionContext.tree).trigger('addnode.taotree', [{
                                'uri'		: uri.decode(response.uri),
                                'parent'    : classUri,
                                'label'     : response.label,
                                'cssClass'  : 'node-instance'
                            }]);
                            resolve({
                                'uri'       : uri.decode(response.uri),
                                'parent'    : classUri,
                                'label'     : response.label,
                                'type'      : 'instance'
                            });
                        }
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
         *
         * @fires layout/tree#addnode.taotree
         */
        binder.register('duplicateNode', function duplicateNode(actionContext){
            $.ajax({
                url: this.url,
                type: "POST",
                data: {uri: actionContext.id, classUri: uri.decode(actionContext.classUri)},
                dataType: 'json',
                success: function(response){
                    if (response.uri) {
                        $(actionContext.tree).trigger('addnode.taotree', [{
                            'uri'       : uri.decode(response.uri),
                            'parent'    : uri.decode(actionContext.classUri),
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
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         *
         * @fires layout/tree#removenode.taotree
         */
        binder.register('removeNode', function remove(actionContext){
            var tokenName = module.config().xsrfTokenName;
            var data = {};

            data.uri = uri.decode(actionContext.uri),
            data.classUri = uri.decode(actionContext.classUri),
            data.id = actionContext.id,
            data[tokenName] = $.cookie(tokenName);

            //TODO replace by a nice popup
            if (window.confirm(__("Please confirm deletion"))) {
                $.ajax({
                    url: this.url,
                    type: "POST",
                    data: data,
                    dataType: 'json',
                    success: function(response){
                        if (response.deleted) {
                            $(actionContext.tree).trigger('removenode.taotree', [{
                                id : actionContext.uri || actionContext.classUri
                            }]);
                        } else {
                            feedback().error(response.msg || __("Unable to delete the selected resource"));
                        }
                    }
                });
            }
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
            var confirmMessage;
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
                    confirmMessage = __('%s instances', instances.length);
                }
                if(classes.length){
                    if(confirmMessage){
                        confirmMessage += __(' and ');
                    }
                    confirmMessage += __('%s classes', classes.length);
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
                        }
                    });
                }, resolve);
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
         * This action helps to filter tree content.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         */
        binder.register('filter', function filter(actionContext){
            $('#panel-' + appContext.section + ' .search-form').slideUp();

            toggleFilter($('#panel-' + appContext.section + ' .filter-form'));
        });

        /**
         * Register the removeNode action: removes a resource.
         *
         * @this the action (once register it is bound to an action object)
         *
         * @param {Object} actionContext - the current actionContext
         * @param {String} [actionContext.uri]
         * @param {String} [actionContext.classUri]
         */
        binder.register('launchFinder', function remove(actionContext){


            var data = _.pick(actionContext, ['uri', 'classUri', 'id']);
	            // used to avoid same query twice
	        var uniqueValue = data.uri || data.classUri || '';
	        var $container  = $('.search-form [data-purpose="search"]');

            $('.filter-form').slideUp();

            if($container.is(':visible')){
                $('.search-form').slideUp();
                search.reset();
                return;
            }

            if($container.data('current') === uniqueValue) {
                $('.search-form').slideDown();
                return;
            }

            $.ajax({
                url: this.url,
                type: "GET",
                data: data,
                dataType: 'html'
            }).done(function(response){
                $container.data('current', uniqueValue);
                search.init($container, response);
                $('.search-form').slideDown();
            });
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


