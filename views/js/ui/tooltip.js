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
 * Copyright (c) 2015-2018 (original work) Open Assessment Technologies SA;
 *
 */
define([
    'jquery',
    'lodash',
    'core/dataattrhandler',
    'lib/popper/popper',
    'lib/popper/tooltip',
    'tpl!ui/tooltip/default'
], function(
    $,
    _,
    DataAttrHandler,
    Popper,
    Tooltip,
    defaultTpl
){
    'use strict';

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success', 'danger'];
    var themesMap = {
        'default': defaultTpl({class:'tooltip-plain'}),
        'dark': defaultTpl({class:'tooltip-dark'}),
        'error': defaultTpl({class:'tooltip-red'}),
        'success':defaultTpl({class:'tooltip-green'}),
        'info': defaultTpl({class:'tooltip-blue'}),
        'warning': defaultTpl({class:'tooltip-orange'}),
        'danger': defaultTpl({class:'tooltip-danger'})
    };
    var defaultOptions = {
        template:themesMap.default,
        html:true,
        trigger:'hover focus',
        popperOptions:{
            positionFixed: true,
            placement:'auto',
            modifiers:{
                preventOverflow:{
                    escapeWithReference:false,
                    enabled:true,
                    padding:6,
                    boundariesElement:'viewport'
                }
            }
        }
    };
    var checkHTMLInstance = function checkHTMLInstance(el){
        return (el instanceof Element || el instanceof HTMLDocument || el.jquery);
    };

    /**
     *   Contains methods to create tooltips.
     *   Made on top of popper.js library (https://popper.js.org/tooltip-documentation.html)
     */
    return {
        /**
         * Lookup a elements that contains the data-tooltip attribute and
         * create the tooltip according to the attributes
         * @param {jQueryElement} $container - the root context to lookup inside
         */
        lookup: function lookup($container){
            var themeName;
            var setTooltip = function (el, inst) {
                if($(el).data('$tooltip')){
                    $(el).data('$tooltip').dispose();
                    $(el).removeData('$tooltip');
                }
                $(el).data('$tooltip', inst);
            };
            if($container && checkHTMLInstance($container)){
                $('[data-tooltip]', $container).each(function(){
                    var $content = DataAttrHandler.getTarget('tooltip', $(this));
                    var opt;
                    themeName = _.contains(themes, $(this).data('tooltip-theme')) ? $(this).data('tooltip-theme') : 'default';
                    opt = {
                        template:themesMap[themeName]
                    };
                    if($content.length){
                        opt = _.merge(defaultOptions, opt, { title: $content[0] });
                    }
                    setTooltip(this, new Tooltip(this, opt));
                });
            } else {
                throw new TypeError("Tooltip should be connected to DOM Element");
            }

        },
        /**
         * create new instance of tooltip based on popper.js lib - {@link https://popper.js.org/tooltip-documentation.html|Popper.js}
         * @param {jQueryElement|HtmlElement} el  - The DOM node used as reference of the tooltip
         * @param {String} message - text message to show inside tooltip.
         * @param {Object} options - options for tooltip. Described in (https://popper.js.org/tooltip-documentation.html#new_Tooltip_new)
         * @returns {Object} - Creates a new popper.js/Tooltip.js instance
         */
        create: function create(el, message, options){
            var calculatedOptions;
            var themeName;
            var template;

            calculatedOptions = options ? _.merge(defaultOptions, options) : defaultOptions;
            themeName = _.contains(themes, calculatedOptions.theme) ? calculatedOptions.theme : 'default';
            template = {
                template:themesMap[themeName]
            };
            if(!el && !checkHTMLInstance(el)){
                throw new TypeError("Tooltip should be connected to DOM Element");
            }
            if(!message && !(checkHTMLInstance(el) || typeof message === 'string')){
                throw new TypeError("Tooltip should have messsage to show");
            }
            return new Tooltip(el, _.merge(calculatedOptions, template, {title:message}));
        },
        /**
         * shortcut for {@link create} method with 'error' theme be default.
         */
        error : function error(element, message, options){
            var theme = { theme : 'error'};
            return this.create(element, message, options ? _.merge(theme, options) : theme);
        },
        /**
         * shortcut for {@link create} method with 'success' theme be default.
         */
        success : function success(element, message, options){
            var theme = { theme : 'success'};
            return this.create(element, message, options ? _.merge(theme, options) : theme);
        },
        /**
         * shortcut for {@link create} method with 'info' theme be default.
         */
        info : function info(element, message, options){
            var theme = { theme : 'info'};
            return this.create(element, message, options ? _.merge(theme, options) : theme);
        },
        /**
         * shortcut for {@link create} method with 'warning' theme be default.
         */
        warning : function warning(element, message, options){
            var theme = { theme : 'warning'};
            return this.create(element, message, options ? _.merge(theme, options) : theme);
        },
        /**
         * shortcut for {@link create} method with 'danger' theme be default.
         */
        danger : function danger(element, message, options){
            var theme = { theme : 'danger'};
            return this.create(element, message, options ? _.merge(theme, options) : theme);
        }

    };
});
