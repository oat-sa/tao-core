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

    var defaultContainerSelector = '#feedback-box';

    //keep a reference to ALL alive feedback
    var currents = [];

    //feedback levels are divided into 2 categories
    var categories = {

        //volatiles messages disappear after a certain amount of time.
        //If 2 messages of the same category appears, only the last one is displayed
        'volatile'      : ['info', 'success'],

        //persistent feedback stay until their are closed.
        //Other persistent feedback are merged to keep all the info.
        //To prevent UI pollution, they may be collapsed  in a notification area
        'persistent'    : ['warning', 'danger', 'error']
    };

    //extract the available levels from the categories
    var levels = _(categories).values().flatten().value();


    //the default options
    var defaultOptions = {
        timeout: {
            info: 2000,
            success: 2000,
            warning: 4000,
            danger: 4000,
            error: 8000
        },
        // Note: value depends on font, font-weight and such.
        // 40 is pretty good in the current setup but will
        // never be exact with a non-proportional font.
        wrapLongWordsAfter: 40,
        encodeHtml : true,
        popup: true
    };

    /**
     * Enables you to create a new feedback.
     * example fb().error("content");
     * @exports ui/feedback
     * @param {jQUeryElement} [$container] - only to specify another container
     * @returns {Object} the feedback object
     * @throws {Error} if the container isn't found
     */
    var feedbackFactory = function feedbackFactory( $container, config ){
        var feedback;

        if(!$container || !$container.length){
            $container = $(defaultContainerSelector);
        }
        if(!$container.length){
            throw new TypeError('The feedback needs to belong to an existing container');
        }

        feedback =  component({

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
                this.config.category = _.findKey(categories, [level]);

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

            info : function info(msg, params, options){
                return this.message('info', msg, params, options).open();
            },

            success : function success(msg, params, options){
                return this.message('success', msg, params, options).open();
            },

            warning : function warning(msg, params, options){
                return this.message('warning', msg, params, options).open();
            },

            danger : function danger(msg, params, options){
                return this.message('danger', msg, params, options).open();
            },

            error : function error(msg, params, options){
                return this.message('error', msg, params, options).open();
            },

            open : function open(){

                //close others
                _(currents)
                    .reject(this)
                    .invoke('close');

                //and display
                this.display();
                return this;
            },

            close : function close(){
                if(this.is('rendered')){
                    this.destroy();
                }
            },

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
