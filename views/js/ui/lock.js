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
    'i18n',
    'tpl!ui/lock/lock',
    'helpers',
    'ui/feedback'
], function($, _, __,tpl, helpers, feedback){
    'use strict';

    //keep a reference to alive lock
    var currents = [];

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
     *
     *
     * @param {Object} [options] - the plugin options
     * @param {String} [options.uri =  ''] - The uri of the selected resource
     * @param {String} [options.url =  ''] - The url to call to release the lock
     */
    var lockApi = {

        level : null,

        category : null,

        /**
         * generate the lock with the right options
         *
         * @example lock().message();
         * @param {String} [category] - the category of the lock (hasLock or locked)
         * @param {String} [msg] - the message to display
         * @param {Object} [options] - the plugin options
         * @fires create.lock
         * @returns {lockApi}
         */
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

        /**
         * generate the lock with the right options and open it
         *
         * @example lock().hasLock();
         * @param {String} [msg] - the message to display
         * @param {Object} [options] - the plugin options
         * @returns {lockApi}
         */
        hasLock : function hasLock(msg, options){
            return this.message('hasLock', msg, options)
                       .open();
        },

        /**
         * generate the lock with the right options and open it
         *
         * @example lock().locked();
         * @param {String} [msg] - the message to display
         * @param {Object} [options] - the plugin options
         * @returns {lockApi}
         */
        locked : function locked(msg, options){
            return this.message('locked', msg, options)
                       .open();
        },

        /**
         * open the lock
         * @example lock().message().open();
         * @fires open.lock
         * @returns {lockApi}
         */
        open : function open(){

            this._trigger('open');

            // display me
            this.display();
            return this;
        },

        /**
         * close the lock
         * @example lock().close();
         * @fires close.lock
         */
        close : function close(){
            if(this.isInState(states.displayed)){

                this.setState(states.closed);

                $('#' + this.id).remove();

                this._trigger('close');

                //clean up ref
                _.remove(currents, { _state : states.closed });
            }
        },

        /**
         * display the lock
         * @example lock().display();
         * @fires display.lock
         * @returns {lockApi}
         */
        display : function display(){
            var self = this;
            if(self.content){
                self.setState(states.displayed);

                $(self.content)
                    .attr('id', self.id)
                    .appendTo(self._container);

                self._trigger('display');

                $('.release', self._container).on('click',function(){
                    self.release();
                });

            }
            return self;
        },

        /**
         * call the url to release the lock
         * @example lock().release();
         * @fires released.lock
         * @fires failed.lock
         * @returns {lockApi}
         */
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
         * Default behaviour
         */
        register : function() {
        	var msg = this._container.data('msg');
        	var id = this._container.data('id');
        	return this.message('hasLock', msg,
                {
                    released : function() {
                    	feedback().success(__('The test has been released'));
                        this.close();
                    },
                    failed : function() {
                    	feedback().error(__('The test could not be released'));
                    },
                    url: helpers._url('release','Lock','tao'),
                    uri: id
                }).open();
        },

        /**
         * trigger the event and the callback if exists
         * @param {String} [eventName] - the name of the event, use the caller name if not set
         */
        _trigger : function _trigger(eventName) {

            //trigger the related event
            this._container.trigger(eventName + '.lock', [this]);

            //run the callback if set in options
            if(_.isFunction(this.options[eventName])){
                this.options[eventName].call(this);
            }
        }

    };

    /**
     * Contains the current state of the lock and accessors
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
        if(!$container){
            $lockBox = $('#lock-box');
        }
        _container = $container || $lockBox;
       
        if(!_container || !_container.length){
            throw new Error('The lock needs to belong to an existing container');
        }

        //if there is already a lock component in this container close it and open a new one
        _.forEach(currents, function(lockRef) {
            if(lockRef !== null && lockRef._container.get(0) === _container.get(0)){
                lockRef.close();
            }
        });
        //mixin the new object with the state object
        var lk = _.extend( {
            id          : 'lock-' + (currents.length + 1),
            _container  : _container
        }, lockState);

        currents.push(lk);

        //delegate the api calls to the new instance
        return delegate(lk, lockApi);
    }; 


    return lockFactory;
});
