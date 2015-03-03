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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 *
 */

define([
    'jquery', 
    'lodash',
    'tpl!ui/lock/lock'
], function($, _, tpl){
    'use strict';

    //keep a reference to alive lock
    var current = null;

    //contains the reference to the main lock box. We expect other containers to be only edge cases.
    var $lockBox;

    //lock levels are divided into 2 categories
    var categories = {
        'hasLock'   : 'info',
        'locked'    : 'error'
    };


    //lock's states
    var states = {
        created     : 'created',
        displayed   : 'displayed',
        closed      : 'closed'
    };

    //the default options
    var defaultOptions = {
        uri : '',
        url : ''
    };

    /**
     * Object delegation. This enables us to separate the instance from Api.
     * An instance can call methods from the API like it was it, so each object will not contain the function definition.
     * @private 
     * @param {Object} receiver - the object that receive the methods
     * @param {Object} provider - it provides the methods to the receiver
     * @returns {Object} the receiver augmented by the provider's methods. 
     */
    function delegate (receiver, provider) {
        _(provider).functions().forEach(function delegateMethod(methodName) {
            receiver[methodName] = function applyDelegated() {
                return provider[methodName].apply(receiver, arguments);
            };
        });
        return receiver;
    }

    /**
     * It provides the lock behavior
     * @typedef lockApi
     */
    var lockApi = {

        level : null,

        category : null,

        message : function message(category, msg, options){
            if(!category || !_.contains(_.keys(categories), category)){
                category = 'hasLock';
            }
            this.setState(states.created);

            this.category = category;
            this.level = _.result(categories, this.category);
            this.options  = _.defaults(options || {}, defaultOptions);

            this.content  = tpl({
                level : this.level,
                msg : msg
            });

            this._trigger('create');

            return this;
        },

        hasLock : function hasLock(msg, options){
            return this.message('hasLock', msg, options)
                       .open();
        },

        locked : function locked(msg, options){
            return this.message('locked', msg, options)
                       .open();
        },

        open : function open(){

            this._trigger();


            //close others
            current.close();                       //run close

            //and display me
            this.display();
            return this;
        },

        close : function close(){
            if(this.isInState(states.displayed)){

                this.setState(states.closed);

                $('#' + this.id).remove();
        
                this._trigger();
            
                //clean up ref
                current = null;
            }
        },

        display : function display(){
            var self = this;
            if(this.content){
                this.setState(states.displayed);

                $(this.content)
                    .attr('id', this.id)
                    .appendTo(this._container);

                this._trigger();

            }
            return this;
        },

        release : function release(){
            var self = this;
            if(self.options.url !== ''){
                $.ajax({
                    url: self.options.url,
                    type: "POST",
                    data : {uri : self.options.uri},
                    dataType: 'json',
                    success : function(response){
                        if(response.success){
                            self._trigger('released');
                        }
                        else{
                            self._trigger('failed');
                        }
                    },
                    error : function(){
                        self._trigger('failed');
                    }
                });
            }
            else{
                self._trigger('failed');
            }

            return this;

        },

        /**
         * trigger the event and the callback if exists
         * @param {String} [eventName] - the name of the event, use the caller name if not set
         */
        _trigger : function _trigger(eventName) {
            var name = eventName || this._trigger.caller.name;

            //trigger the related event
            this._container.trigger(name + '.lock', [this]);

            //run the callback if set in options
            if(_.isFunction(this.options[name])){
                this.options[name].call(this);
            }
        }

    };

    /**
     * COntains the current state of the lock and accessors
     * @typedef lockState
     */
    var lockState = {

        //the current state
        _state : null,

        /**
         * Check if the current state is one of the given values
         * @param {String|Array} verify - the statue to check
         * @returns {Boolean} true if the object is in the state to verify
         */        
        isInState : function isInState(verify){
            if(_.isString(verify)){
                verify = [verify];
            }
            return _.contains(verify, this._state);
        },

        /**
         * Change the current state
         * @param {String} state - the new state
         * @throws {Error} if we try to set an invalid state
         */
        setState : function setState(state){
            if(!_.contains(states, state)){
                throw new Error('Unkown state ' + state );
            }
            this._state = state;
        } 
    };

    /**
     * Enables you to create a new lock.
     * example lock().error("content");
     * @exports ui/lock
     * @param {jQUeryElement} [$container] - only to specify another container
     * @returns {Object} the lock object
     * @throws {Error} if the container isn't found
     */
    var lockFactory = function lockFactory( $container ){
        var _container;
        if(!$lockBox){
            $lockBox = $('#lock-box');
        }
        _container = $container || $lockBox;
       
        if(!_container || !_container.length){
            throw new Error('The lock needs to belong to an existing container');
        }

        //mixin the new object with the state object
        var lk = _.extend( {
            id          : 'lock',
            _container  : _container
        }, lockState);

        current = lk;
 
        //delegate the api calls to the new instance
        return delegate(lk, lockApi);
    }; 


    return lockFactory;
});
