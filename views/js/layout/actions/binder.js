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
define(['lodash'], function(_){
    'use strict';

    /**
     * Helps you to bind actions' behavior.
     *
     * To bind a behavior to an action, you need to register a callback under the same name than 'binding' value in the structure.xml
     *
     *
     * @exports layout/actions/binder
     */
    var actionBinder =  {

        /**
         * The list of registered bindings, key are binding name.
         */
        _bindings : {},

        /**
         * Register a new binding
         *
         * @example
         *  binder.register('subClass', function subClass(context){
         *      //do something with context.uri to create a sub class.
         *  });
         *
         * @param {String} name - the binding name
         * @param {ActionBinding}
         *
         */
        register : function(name, binding){

            /**
             * @callback ActionBinding
             * @this action - the action object
             * @param {ActionContext} context - the context
             */
            this._bindings[name] = binding;
        },

        /**
         * Execute the binding of an action if one has been registerd
         * @param {Object} action - the action to execute the binding of
         * @param {String} action.binding - the action must contain a binding property that match a registerd binding
         * @param {ActionContext} context - the context in which to execute the binding
         */
        exec : function(action, context){
            var name;
            if(action && action.binding){

                name = action.binding;
                if(_.isFunction(this._bindings[name])){

                    this._bindings[name].call(action, context);

                }
            }
        }
    };

    return actionBinder;
});
