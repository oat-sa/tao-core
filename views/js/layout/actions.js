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
    'jquery',
    'lodash',
    'core/eventifier',
    'core/promise',
    'lib/uuid',
    'layout/actions/binder',
    'layout/actions/common',
], function($, _, eventifier, Promise, uuid, binder, commonActions){
    'use strict';

    /**
     * The data context for actions
     * @typedef {Object} ActionContext
     * @property {String} [uri] - the resource uri
     * @property {String} [classUri] - the class uri
     */

    var actions = {};
    var resourceContext = {};

    /**
     * @exports layout/actions
     */
    var actionManager = eventifier({

        /**
         * Initialize the actions for the given scope. It should be done only once.
         * @constructor
         * @param {jQueryElement} [$scope = $(document)] - to scope the actions into the page
         */
        init: function init($scope){

            if($scope && $scope.length){
                this.$scope = $scope;
            } else {
                this.$scope = $(document);
            }

            //initialize the registration of common actions
            commonActions();

            this._lookup();
            this.updateContext();
            this._listenUpdates();
            this._bind();
        },

        /**
         * Lookup for existing actions in the page and add them to the _actions property
         * @private
         */
        _lookup : function _lookup(){
            var self = this;
            $('.action-bar .action', this.$scope).each(function(){

                var $this = $(this);
                var id;
                if($this.data('action')){

                    //use the element id
                    if($this.attr('id')){
                        id = $this.attr('id');
                    } else {
                        //or generate one
                        do {
                            id = 'action-' + uuid(8, 16);
                        } while (self._actions[id]);

                        $this.attr('id', id);
                    }

                    actions[id] = {
                        id      : id,
                        name    : $this.attr('title'),
                        binding : $this.data('action'),
                        url     : $('a', $this).attr('href'),
                        context : $this.data('context'),
                        multiple : $this.data('multiple'),
                        state : {
                            disabled    : $this.hasClass('disabled'),
                            hidden      : $this.hasClass('hidden'),
                            active      : $this.hasClass('active')
                        }
                    };
                }
            });
        },

        /**
         * Bind actions' events: try to execute the binding registered for this action.
         * The behavior depends on the binding name of the action.
         * @private
         */
        _bind : function _bind(){
            var self = this;
            var actionSelector = this.$scope.selector + ' .action-bar .action';

            $(document)
                .off('click', actionSelector)
                .on('click', actionSelector, function(e){
                    var selected;
                    e.preventDefault();
                    selected  = actions[$(this).attr('id')];
                    if(selected && selected.state.disabled === false &&  selected.state.hidden === false){
                        self.exec(selected);
                    }
                });
        },

        /**
         * Listen for event that could update the actions.
         * Those events may change the current context.
         * @private
         * @deprecated
         */
        _listenUpdates : function _listenUpdates(){
            var self = this;
            var treeSelector = this.$scope.selector + ' .tree';

            //listen for tree changes
            $(document)
                .off('change.taotree.actions', treeSelector)
                .on('change.taotree.actions', treeSelector, function(e, context){
                    context = context || {};
                    context.tree = this;
                    self.updateContext(context);
                });
        },

        /**
         * Update the current context. Context update may change the visibility of the actions.
         * @param {ActionContext|ActionContext[]} context - the new context
         */
        updateContext : function updateContext(context){
            var self = this;
            var current;
            var permissions;

            context = context || {};

            if(_.isArray(context) ) {
                _.forEach(actions, function(action){
                    var hasClasses = _.some(context, { type : 'class' });
                    var hasInstances = _.some(context, { type : 'instance' });

                    //if some has not the permissions we deny
                    var hasPermissionDenied = _.some(context, function(resource){
                        return resource.permissions && resource.permissions[action.id] === false;
                    });

                    if( action.multiple &&
                        !hasPermissionDenied &&
                        action.context !== 'none' &&
                        ( (action.context === '*' || action.context === 'resource') ||
                          (action.context === 'instance' && hasInstances && !hasClasses) ||
                          (action.context === 'class' && hasClasses && !hasInstances) ) ) {

                        action.state.hidden = false;
                    } else {
                        action.state.hidden = true;
                    }
                });

            } else {

                if(context.type){
                    current = context.type;
                } else {
                    current = context.uri ? 'instance' : context.classUri ? 'class' : 'none';
                }
                permissions = context.permissions || {};

                _.forEach(actions, function(action){
                    var permission = permissions[action.id];

                    if( action.multiple || permission === false ||
                        (current === 'none' && action.context !== '*') ||
                        (action.context !== '*' && action.context !== 'resource' && current !== action.context) ){

                        action.state.hidden = true;

                    } else {
                        action.state.hidden = false;
                    }
                });
            }

            resourceContext = context;
            self.updateState();
        },

        /**
         * Update the state of the actions regarding the values of their state property
         */
        updateState : function updateState(){
            _.forEach(actions, function(action, id){
                var $elt = $('#' + id);
                _.forEach(['hidden', 'disabled', 'active'], function(state){
                    if(action.state[state] === true){
                        $elt.addClass(state);
                    } else {
                        $elt.removeClass(state);
                    }
                });
            });
        },

        /**
         * Execute the operation bound to an action (via {@link layout/actions/binder#register});
         * @param {String|Object} action - can be either the id, the name or the action directly
         * @param {ActionContext} [context] - an action conext, use the current otherwise
         * @returns {Promise?}
         * @fires ActionManger#error if the executed action fails
         * @fires ActionManger#{actionId} an event with the action id
         */
        exec : function exec(action, context){
            var self = this;
            if(_.isString(action)){
                if(_.isPlainObject(actions[action])){
                    //try to find by id
                    action = actions[action];
                } else {
                    //or by by name
                    action = _.find(actions, {name : action});
                }
            }
            if(_.isPlainObject(action)){

                //make the executed action active
                _.forEach(actions, function(otherAction){
                    otherAction.state.active = false;
                });
                action.state.active = true;
                this.updateState();

                return Promise
                    .resolve(binder.exec(action, context || resourceContext))
                    .then(function actionDone(actionData){
                        var events = [action.id, action.binding];
                        self.trigger(events.join(' '), context || resourceContext, actionData);
                    })
                    .catch( function actionError(err){
                        self.trigger('error', err);
                    });
            }
        },

        /**
         * Helps you to retrieve an action from it's name or id
         * @param {String} actionName - name or id of the action
         * @returns {Object} the action
         */
        getBy : function(actionName){
            var action;
            if(_.isPlainObject(actions[actionName])){
                //try to find by id
                action = actions[actionName];
            } else {
                //or by by name
                action = _.find(actions, {name : actionName});
            }
            return action;
        }
    });

    return actionManager;
});
