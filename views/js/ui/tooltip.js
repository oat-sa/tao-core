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

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success', 'danger'],
        themesMap = {
            'default': defaultTpl({class:'tooltip-plain'}),
            'dark': defaultTpl({class:'tooltip-dark'}),
            'error': defaultTpl({class:'tooltip-red'}),
            'success':defaultTpl({class:'tooltip-green'}),
            'info': defaultTpl({class:'tooltip-blue'}),
            'warning': defaultTpl({class:'tooltip-orange'}),
            'danger': defaultTpl({class:'tooltip-danger'})
        },
        defaultOptions = {
            template:themesMap['default'],
            html:true,
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

    /**
     * Look up for tooltips and initialize them
     *
     * @public
     * @param {jQueryElement} $container - the root context to lookup inside
     */
    return {
        lookup: function(element){
            var themeName;
            var setTooltip = function (el, inst) {
                if($(el).data('$tooltip')){
                    $(el).data('$tooltip').dispose();
                    $(el).removeData('$tooltip');
                }
                $(el).data('$tooltip', inst);
            };
            if(element && (element instanceof Element || element instanceof HTMLDocument || element.jquery)){
                $('[data-tooltip]', element).each(function(){
                    var $content = DataAttrHandler.getTarget('tooltip', $(this));
                    var opt;
                    themeName = _.contains(themes, element.data('tooltip-theme')) ? element.data('tooltip-theme') : 'default';
                    opt = {
                        template:themesMap[themeName]
                    };
                    if($content.length){
                        _.merge(opt, { title: $content[0] }, defaultOptions);
                    }
                    setTooltip(this, new Tooltip(this, opt));
                });
            } else {
                throw new Error("Tooltip should be connected to DOM Element");
            }

        },
        instance: function (el, settings){
            if(!el && !(el instanceof Element || el instanceof HTMLDocument || el.jquery)){
                throw new Error("Tooltip should be connected to DOM Element");
            }
            var themeName = _.contains(themes, settings.theme) ? settings.theme : 'default';
            var template = {
                template:themesMap[themeName]
            };
            return new Tooltip(el, _.merge(defaultOptions, template, settings));
        }

    }
});
