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
 */
define(['jquery', 'lodash', 'core/dataattrhandler',   'lib/popper/popper', 'lib/popper/tooltip', 'css!lib/popper/popper.css'], function($, _, DataAttrHandler, Popper, Tooltip ){
    'use strict';

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success', 'danger'],
        themesMap = {
            'default' : '<div class="tooltip qtip-rounded qtip-plain" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            'dark' : '<div class="tooltip qtip-rounded qtip-dark" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            'error' : '<div class="tooltip qtip-rounded qtip-red" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            'success' :'<div class="tooltip qtip-rounded qtip-green" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            'info' : '<div class="tooltip qtip-rounded qtip-blue" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            'warning' : '<div class="tooltip qtip-rounded qtip-orange" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
            'danger' : '<div class="tooltip qtip-rounded qtip-danger" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
        },
        // mapping from old qtip API to new Popper.js+Tooltip.js API calls
        commandsMap = {
            'hide':'hide',
            'blur':'hide',
            'show':'show',
            'toggle':'toggle',
            'update':'updateTitleContent',
            'destroy':'dispose',
            'set':'updateTitleContent'
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
     * redefinition of jquery.qtip plugin http://qtip2.com/
     * the goal is to substitude outdated lib code with new solution (https://popper.js.org/)
     * leaving its original interfaces that are widely used through project.
     * https://github.com/FezVrasta/popper.js/blob/master/docs/_includes/tooltip-documentation.md
     */
    var qtip = function (command, message, messageData) {
        // there were two types of requests for qtip jquery plugin (with object or string):
        // 1) initialize object for the first time : $el.qtip({ options object});
        if (typeof command === 'object') {
            // .data('$popper') - is the way to store current state inside jquery plugin
            if(this.data('$popper')) {
                this.data('$popper').dispose();
                this.removeData('$popper');
            }
            // fit old  options  format for themes to new
            if (themesMap[command.theme]){
                command.template = themesMap[command.theme];
                delete command.theme;
            }
            // fit old content option to new
            if(command.content){
                command.title = command.content.text ;
                delete command.content;
            }
            this.data('$popper', new Tooltip(this, command));

        // 2) sending text (String) commands to  element that is already initialized : $el.qtip("show")
        }else if(this.data('$popper') && typeof command === 'string'){
            switch (command) {
                case 'update':
                    this.data('$popper')[commandsMap[command]](message);
                    break;
                case 'set':
                // covers this particular behavior:
                // $scoreInput.qtip('set', 'content.text', options.tooltipContent.required);
                    if(message === 'content.text'){
                        this.data('$popper')[commandsMap[command]](messageData);
                    }
                    break;
                default:
                    if(command === 'api'){
                        return this.data('$popper');
                    }
                    this.data('$popper')[commandsMap[command]]();
                    if(commandsMap[command] === 'dispose'){
                        this.removeData('$popper');
                    }
            }
        }

        return this;
    };
    $.fn.qtip = qtip;
    $.qtip = qtip;

    /**
     * Look up for tooltips and initialize them
     *
     * @public
     * @param {jQueryElement} $container - the root context to lookup inside
     */
    return function lookupSelector($container){

        $('[data-tooltip]', $container).each(function(){
            var $elt = $(this),
                $content = DataAttrHandler.getTarget('tooltip', $elt),
                themeName = _.contains(themes, $elt.data('tooltip-theme')) ? $elt.data('tooltip-theme') : 'default',
                options = _.merge({}, defaultOptions, {
                    template:themesMap[themeName]
                });
            if($content.length){

                _.merge(options,{
                    title: $content[0]
                });

            }
            $elt.qtip(options);

        });

    };
});
