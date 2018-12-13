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
    'tpl!ui/tooltip/default',
    'tpl!ui/tooltip/dark',
    'tpl!ui/tooltip/error',
    'tpl!ui/tooltip/success',
    'tpl!ui/tooltip/info',
    'tpl!ui/tooltip/warning',
    'tpl!ui/tooltip/danger'
], function(
    $,
    _,
    DataAttrHandler,
    Popper,
    Tooltip,
    defautTpl,
    darkTpl,
    errorTpl,
    successTpl,
    infoTpl,
    warningTpl,
    dangerTpl
){
    'use strict';

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success', 'danger'],
        themesMap = {
            'default': defautTpl(),
            'dark': darkTpl(),
            'error': errorTpl(),
            'success':successTpl(),
            'info': infoTpl(),
            'warning': warningTpl(),
            'danger': dangerTpl()
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
    return function lookupSelector(element, options){
        var themeName;
        var template;
        var instance;
        var setTooltip = function (el, inst) {
            if($(el).data('$tooltip')){
                $(el).data('$tooltip').dispose();
                $(el).removeData('$tooltip');
            }
            $(el).data('$tooltip', inst);
        };
        if(element && (element instanceof Element || element instanceof HTMLDocument || element.jquery)){
            if(!options){
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
            }else{
                themeName = _.contains(themes, options.theme) ? options.theme : 'default';
                template = {
                    template:themesMap[themeName]
                };
                instance = new Tooltip(element, _.merge(defaultOptions, template, options));
                setTooltip(element, instance);
                return instance;
            }
        } else {
            throw new Error("Tooltip should be connected to DOM Element");
        }

    };
});
