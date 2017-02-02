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
 * Simple component to display a standard report
 *
 * @example
 * report({
 *       actions: [{
 *           id: 'continue',
 *           icon: 'right',
 *           title: 'Continue to next step',
 *           label: 'Continue'
 *       }]
 *   }, {
 *       type: "warning",
 *       message: "<em>Data not imported. All records are <strong>invalid.</strong></em>",
 *       children: [{
 *           type: "error",
 *          message: "Row 1 Student Number Identifier"
 *       }]
 *   }).on('action-continue', function () {
 *       console.log('go to next step');
 *   }).render('body');
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/form',
    'ui/component',
    'tpl!ui/report/layout',
    'tpl!ui/report/feedback'
], function ($, _, __, form, component, layoutTpl, feedbackTpl) {
    'use strict';

    var _defaults = {
        showDetailsButton : true,
        actions:[]
    };

    /**
     * Array of authorized report types
     * @type {Array}
     */
    var authorizedTypes = ['success', 'info', 'warning', 'error'];

    /**
     * Recursive function to render report messages
     *
     * @private
     * @param {Object} data - a standard report object sent by the backend
     * @param {String} data.type - the error type
     * @param {String} data.message - the feedback message
     * @param {Array} [data.children] - children report object
     * @param {Array} [actions] - the actions buttons to be added, only for the first level of the hierarchy
     * @returns {*}
     */
    var _renderFeebacks = function _renderFeebacks(data, actions){
        var children = [];
        if(!data.type || authorizedTypes.indexOf(data.type) === -1){
            throw new TypeError('Unkown report type: '+data.type);
        }
        if(_.isArray(data.children) && data.children.length){
            _.each(data.children, function(child){
                children.push(_renderFeebacks(child));
            });
        }
        data.hasChildren = (children.length > 0);
        data.children = children;
        data.actions = actions;
        return feedbackTpl(data);
    }

    var report = {
        /**
         * Check if the details of the report are currently visible
         * @returns {Boolean}
         */
        isDetailed : function isDetailed(){
            return this.is('detailed');
        },
        /**
         * Show the report details
         * 
         * @returns {this}
         * @fires reportComponent#showDetails
         */
        showDetails : function showDetails(){
            if(this.is('rendered')){
                this.setState('detailed', true);
                this.getElement().find('.fold input').prop('checked', true);
                this.trigger('showDetails');
            }
            return this;
        },
        /**
         * Hide the report details
         *
         * @returns {this}
         * @fires reportComponent#hideDetails
         */
        hideDetails : function hideDetails(){
            if(this.is('rendered')) {
                this.setState('detailed');
                this.getElement().find('.fold input').prop('checked', false);
                this.trigger('hideDetails');
            }
            return this;
        }
    };

    /**
     * Create a simple report component
     *
     * @param {Object} config
     * @param {Boolean} [config.showDetailsButton=true] - display the show/hide details toggle
     * @param {Array} [config.actions] - possibility to add more button controls
     * @param {Object} data - a standard report object
     * @param {String} data.type - the type of the report
     * @param {String} data.message - the message to be included in the report body (html allowed)
     * @param {Array} [data.children] - children report object
     * @returns {reportComponent}
     */
    var reportComponentFactory = function reportComponentFactory(config, data) {

        var initConfig = _.defaults(config || {}, _defaults);

        if(data && _.isArray(data.children) && data.children.length){
             initConfig.detailsButtonVisible = initConfig.showDetailsButton;
        }

        /**
         * THe report component
         * @typedef reportComponent
         * @fires reportComponent#action
         * @fires reportComponent#action-{custom action name}
         */
        return component(report)
            .setTemplate(layoutTpl)
            .on('render', function () {

                var self = this;
                var $content = this.getElement().find('.content');
                var $checkbox = this.getElement().find('.fold input');
                $content.append(_renderFeebacks(data, this.config.actions));

                //init actions:
                $content.on('click', '.action', function(){
                    var actionId = $(this).data('trigger');
                    self.trigger('action-'+actionId);
                    self.trigger('action', actionId);
                });

                $checkbox.click(function(){
                    if(self.isDetailed()){
                        self.hideDetails();
                    }else{
                        self.showDetails();
                    }
                });

            })
            .init(initConfig);
    }

    return reportComponentFactory;
});