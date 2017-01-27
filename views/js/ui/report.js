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
        actions:[]
    };

    var authorizedTypes = ['success', 'info', 'warning', 'error'];

    /**
     * Recursive function to render report messages
     *
     * @param {Object} data - a standard report object sent by the backend
     * @param {String} data.type - the error type
     * @param {String} data.message - the feedback message
     * @param {Array} [data.children] - children report object
     * @param {Array} [actions] - the actions buttons to be added, only for the first level of the hierarchy
     * @returns {*}
     */
    var renderFeebacks = function renderFeebacks(data, actions){
        var children = [];
        if(!data.type || authorizedTypes.indexOf(data.type) === -1){
            throw new TypeError('Unkown report type: '+data.type);
        }
        if(_.isArray(data.children) && data.children.length){
            _.each(data.children, function(child){
                children.push(renderFeebacks(child));
            });
        }
        data.hasChildren = (children.length > 0);
        data.children = children;
        data.actions = actions;
        return feedbackTpl(data);
    }

    var report = {
        isDetailed : function isDetailed(){
            return this.$component.hasClass('detailed');
        },
        showDetails : function showDetails(){
            this.$component.addClass('detailed');
            this.$component.find('.fold input').prop('checked', true);
            this.trigger('showDetails');
            return this;
        },
        hideDetails : function hideDetails(){
            this.$component.removeClass('detailed');
            this.$component.find('.fold input').prop('checked', false);
            this.trigger('hideDetails');
            return this;
        }
    };

    /**
     * simple component to display a standard report
     *
     * @param config
     * @param data
     * @returns {*}
     */
    var reportComponent = function reportComponent(config, data) {

        var initConfig = _.defaults(config || {}, _defaults);

        if(data && _.isArray(data.children) && data.children.length){
             initConfig.hasDetailedReport = true;
        }

        return component(report, _defaults)
            .setTemplate(layoutTpl)
            .on('render', function () {

                var self = this;
                var $content = this.$component.find('.content');
                var $checkbox = this.$component.find('.fold input');
                $content.append(renderFeebacks(data, this.config.actions));

                //init actions:
                $content.on('click', '.action', function(){
                    var actionId = $(this).data('trigger');
                    self.trigger('action-'+actionId);
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

    return reportComponent;
});