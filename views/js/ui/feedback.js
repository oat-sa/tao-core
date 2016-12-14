/*
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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 * Displays feedbacks "toasts" messages.
 *
 * @example
 *  feedback().info('This is correct');
 *
 *  feedback().warning('You are about to remove %d %s', [5, 'users']);
 *
 *  feedback($anotherContainer, {
 *    timeout : -1
 *    encodeHtml: false
 *  })
 *  .error('<p>Error : </p><ul><li>Reason 1</li>...</ul>');
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/format',
    'ui/component',
    'util/wrapLongWords',
    'util/encode',
    'tpl!ui/feedback/feedback'
], function($, _, format, component, wrapLongWords, encode, tpl){
    'use strict';

    //The default container of the feedbacks
    var defaultContainerSelector = '#feedback-box';

    //keep a reference to ALL alive feedback
    var currents = [];

    //available levels
    var levels = [
        'info',
        'success',
        'warning',
        'danger',
        'error'
    ];

    var defaultOptions = {
        timeout: {
            info:    2000,
            success: 2000,
            warning: 4000,
            danger:  4000,
            error:   8000
        },
        // Note: value depends on font, font-weight and such.
        // 40 is pretty good in the current setup but will
        // never be exact with a non-proportional font.
        wrapLongWordsAfter: 40,

        //by default HTML content is encoded
        encodeHtml : true,

        //change the display (absolute or in the flow)
        popup: true
    };

    /**
     * Creates a feedback object.
     *
     * @exports ui/feedback
     * @param {jQUeryElement} [$container] - only to specify another container
     * @param {Object} [config] - change the config
     * @param {Object|Number} [config.timeout] - either one for every level or per level timeout in ms
     * @param {Number} [config.wrapLongWordsAfter] - add a space in the middle of very long word to enable wrap lines
     * @param {Boolean} [config.encodeHtml] - weither the message is html encoded
     * @param {Boolean} [config.popup] - displays the message as a popup or in the flow
     * @returns {feedback} the feedback object
     * @throws {TypeError} without a container
     */
    var feedbackFactory = function feedbackFactory( $container, config ){
        var feedback;

        if(!$container || !$container.length){
            $container = $(defaultContainerSelector);
        }
        if(!$container.length){
            throw new TypeError('The feedback needs to belong to an existing container');
        }

        /**
         * @typedef {Object} feedback - the feedback component
         */
        feedback =  component({

            /**
             * Creates a message, not displayed.
             * @param {String} [level = 'info'] - the message level
             * @param {String} msg - the message to display
             * @param {Array} [params] - parameters for the message format (%s,%d,%j)
             * @param {Object} [options] - specify the config
             * @returns {feedback} chains
             */
            message : function message(level, msg, params, options){
                var displayedMessage = msg;

                if(!level || !_.contains(levels, level)){
                    level = 'info';
                }

                //parameterized messages
                if(_.isPlainObject(params)) {
                    options = params;
                    params  = [];
                }

                this.config  = _.defaults(options || {}, this.config);
                this.config.level = level;

                // encode plain text string to html
                if(this.config.encodeHtml){
                    displayedMessage = encode.html(displayedMessage);
                }

                // wrap long words
                if(this.config.wrapLongWordsAfter){
                    displayedMessage = wrapLongWords(displayedMessage, this.config.wrapLongWordsAfter);
                }

                //apply strf like format parameters
                if(_.isArray(params) && params.length){
                    displayedMessage = format.apply(format, [displayedMessage].concat(params));
                }

                this.config.msg = displayedMessage;

                return this;
            },

            /**
             * Opens an info message
             * @param {String} msg - the message to display
             * @param {Array} [params] - parameters for the message format (%s,%d,%j)
             * @param {Object} [options] - specify the config
             * @returns {feedback} chains
             */
            info : function info(msg, params, options){
                return this.message('info', msg, params, options).open();
            },

            /**
             * Opens an success message
             * @param {String} msg - the message to display
             * @param {Array} [params] - parameters for the message format (%s,%d,%j)
             * @param {Object} [options] - specify the config
             * @returns {feedback} chains
             */
            success : function success(msg, params, options){
                return this.message('success', msg, params, options).open();
            },

            /**
             * Opens an warning message
             * @param {String} msg - the message to display
             * @param {Array} [params] - parameters for the message format (%s,%d,%j)
             * @param {Object} [options] - specify the config
             * @returns {feedback} chains
             */
            warning : function warning(msg, params, options){
                return this.message('warning', msg, params, options).open();
            },

            /**
             * Opens an danger message
             * @param {String} msg - the message to display
             * @param {Array} [params] - parameters for the message format (%s,%d,%j)
             * @param {Object} [options] - specify the config
             * @returns {feedback} chains
             */
            danger : function danger(msg, params, options){
                return this.message('danger', msg, params, options).open();
            },

            /**
             * Opens an error message
             * @param {String} msg - the message to display
             * @param {Array} [params] - parameters for the message format (%s,%d,%j)
             * @param {Object} [options] - specify the config
             * @returns {feedback} chains
             */
            error : function error(msg, params, options){
                return this.message('error', msg, params, options).open();
            },

            /**
             * Opens the message (and close previous one)
             * ! Method kept for backward compat with previous version !
             * @returns {feedback} chains
             */
            open : function open(){

                //close others
                _(currents)
                    .reject(this)
                    .invoke('close');

                //and display
                return this.display();
            },

            /**
             * Closes the message
             * ! Method kept for backward compat with previous version !
             * @returns {feedback} chains
             */
            close : function close(){
                if(this.is('rendered')){
                    this.destroy();
                }
            },

            /**
             * Displays the message (does the render)
             * ! Method kept for backward compat with previous version !
             * @returns {feedback} chains
             */
            display : function display(){

                if(this.is('rendered')){
                    this.show();
                } else {
                    this.render($container);
                }
                return this;
            },
        }, defaultOptions);

        return feedback
            .setTemplate(tpl)
            .on('init', function(){
                this.config.id = 'feedback-' + (currents.length + 1);

                currents.push(this);

                //for backward compat
                $container.trigger('create.feedback');
            })
            .on('render', function(){
                var self = this;
                var $component = this.getElement();

                var $closer = $('.icon-close', $component);
                var timeout = _.isPlainObject(this.config.timeout) ? this.config.timeout[this.config.level] : this.config.timeout;

                $closer.off('click').on('click', function(e){
                    e.preventDefault();
                    self.destroy();
                });

                if(_.isNumber(timeout) && timeout > 0){
                    _.delay(function(){
                        self.close();
                    }, timeout);
                }

                //for backward compat
                $container.trigger('display.feedback');
            })
            .on('destroy', function(){
                //for backward compat
                $container.trigger('close.feedback');

                _.remove(currents, this);
            })
            .init(config);
    };

    return feedbackFactory;
});
