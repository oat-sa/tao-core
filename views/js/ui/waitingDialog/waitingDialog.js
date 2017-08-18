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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * This component opens a modal dialog,
 * waiting for an event before unlocking the controls.
 *
 * @example
 *   waitingDialog()
 *      .on('proceed', function(){
 *           console.log('done');
 *      })
 *      .on('render', function(){
 *           var self = this.
 *           setTimeout(function(){
 *               self.proceed();
 *           }, 1000);
 *       });
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'ui/dialog'
], function ($, _, __, component, dialog) {
    'use strict';

    /**
     * The default texts
     */
    var defaultConfig = {
        message : __('Waiting'),
        waitContent : __('Please wait while ...'),
        waitButtonText : __('Please wait'),
        proceedContent : __('Wait is over'),
        proceedButtonText : __('Proceed'),
    };

    /**
     * Creates a waiting dialog, auto renders in waiting state
     * @param {Object} [config] - set the component config
     * @param {String} [config.message] - the main dialog message
     * @param {String} [config.waitContent] - the dialog content in waiting state (below the 'message')
     * @param {String} [config.waitButtonText] - the button text while waiting
     * @param {String} [config.proceedContent] - the dialog content when the wait is over
     * @param {String} [config.proceedButtonText] - the button text when the wait is over
     * @param {jQueryElement} [config.container = 'body'] - where to render the dialog
     * @returns {waitingDialog} the component itself
     */
    return function waitingDialogFactory(config){

        //keep some elements refs
        var $button;
        var $content;

        /**
         * @typedef {waitingDialog} the component
         */
        var waitingDialog = component({

            /**
             * Sets the component in waiting state
             * @returns {waitingDialog} the component itself
             * @fires waitingDialog#wait
             */
            beginWait : function beginWait(){
                if(!this.is('waiting')){
                    this.setState('waiting', true);

                    $button
                        .prop('disabled', true)
                        .text(this.config.waitButtonText);

                    $content
                        .text(this.config.waitContent);

                    /**
                     * The component switch to the waiting state
                     * @event waitingDialog#wait
                     */
                    this.trigger('wait');
                }
                return this;
            },

            /**
             * The component is not waiting anymore
             * @returns {waitingDialog} the component itself
             * @fires waitingDialog#unwait
             */
            endWait: function endWait(){

                if(this.is('waiting')){
                    this.setState('waiting', false);

                    $content
                        .text(this.config.proceedContent);

                    $button
                        .text(this.config.proceedButtonText)
                        .removeProp('disabled');

                    /**
                     * The component switch to non waiting state
                     * @event waitingDialog#unwait
                     */
                    this.trigger('unwait');
                }
                return this;
            },

            /**
             * Destroys the component's dialog
             * @returns {waitingDialog} the component itself
             * @fires waitingDialog#destroy
             */
            destroy : function  destroy(){
                if(this.dialog){
                    this.dialog.destroy();
                }
                return this.trigger('destroy');
            }


        }, defaultConfig)
            .on('init', function(){

                this.dialog = dialog({
                    message : this.config.message,
                    content : this.config.waitContent || '',
                    autoRender : false,
                    disableClosing : true,
                    disableEscape   :true,
                    buttons : [{
                        id : 'waiting',
                        type : 'info',
                        label :this.config.waitButtonText,
                        close: true
                    }]
                });

                $button = $('[data-control="waiting"]', this.dialog.getDom());
                $content = $('.content', this.dialog.getDom());

                this.beginWait();
                this.render();
            })
            .on('render', function(){
                var self = this;
                this.dialog
                    .on('closed.modal', function() {
                        if(!self.is('waiting')){

                            /**
                             * The proceed button has been clicked (the dialog is closed)
                             * @event waitingDialog#proceed
                             */
                            self.trigger('proceed');
                        }
                    })
                    .render(this.config.container || 'body');
            });

        _.defer(function(){
            waitingDialog.init(config || {});
        });

        return waitingDialog;
    };
});
