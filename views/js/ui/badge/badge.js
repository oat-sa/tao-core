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
 * A badge component used to indicate the status of a list of process or elements
 *
 * @example
 * badgeFactory({
 *          type : 'info',
 *          value : 2,
 *          loading : true
 *     });
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/hider',
    'ui/component',
    'tpl!ui/badge/tpl/badge',
    'css!ui/badge/css/badge'
], function ($, _, __, hider, component, badgeTpl) {
    'use strict';

    var _defaults = {
        loading: false,
        type: 'info',
        value: 0
    };

    var _allowedTypes = ['success', 'warning', 'error', 'info'];

    var badgeApi = {

        /**
         * Update and refresh the rendering of the badge
         * @param {Object} config - the display config
         * @param {Number} config.value - the number to be display in the badge, if above 99, the 99+ will be displayed instead
         * @param {String} config.type - define the type of the badge (success, error, info)
         * @param {Boolean} [config.loading] - if true, show the loading animation around it
         * @returns {badgeApi}
         */
        update : function update(config){

            var $component = this.getElement();
            var $border = $component.find('.badge-border');
            var $badge = $component.find('.badge').removeClass('badge-info badge-success badge-warning badge-error icon-result-ok');
            var $loader = $component.find('.loader');
            var displayValue;

            _.assign(this.config, config);

            if(this.config && this.config.value){
                displayValue = parseInt(this.config.value, 10);
                displayValue = (displayValue > 99) ? '99+' : displayValue;//only display up to a value of 99

                //set status
                if(_allowedTypes.indexOf(this.config.type) === -1){
                    throw new Error('Invalid badge type : '.this.config.type);
                }
                $badge.addClass('badge-' + this.config.type).html(displayValue);

                //if any is running
                if(this.config.loading){//replace by loading
                    hider.show($loader);
                    hider.hide($border);
                }else{
                    hider.hide($loader);
                    hider.show($border);
                }
            }else{
                //idle state as no border nor loader
                hider.hide($loader);
                hider.hide($border);

                //set the complete state (with the check box icon and clear any number in it)
                $badge.addClass('icon-result-ok').empty();
            }
            return this;
        }
    };

    /**
     * Create a badge that indicates the status and a number
     *
     * @param {Object} config - the component config
     * @param {Number} config.value - the number to be display in the badge, if above 99, the 99+ will be displayed instead
     * @param {String} config.type - define the type of the badge (success, error, info)
     * @param {Boolean} [config.loading] - if true, show the loading animation around it
     * @returns {badge} the component
     */
    return function badgeFactory(config) {
        var initConfig = _.defaults(config || {}, _defaults);

        /**
         * The component
         * @typedef {ui/component} badge
         */
        return component(badgeApi)
            .setTemplate(badgeTpl)
            .on('render', function() {
                this.update(this.config);
            })
            .init(initConfig);
    };

});