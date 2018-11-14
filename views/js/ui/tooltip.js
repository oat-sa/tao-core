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
define(['jquery', 'lodash', 'core/dataattrhandler',   'lib/popper/popper.min', 'lib/popper/tooltip.min', 'css!lib/popper/popper.css'], function($, _, DataAttrHandler, Popper, Tooltip ){
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
            popperOptions:{
                positionFixed: true,
                placement:'auto'
            }
        };

    /**
     * redefinition of jquery.qtip plugin http://qtip2.com/
     * the goal is to substitude outdated lib code with new solution (https://popper.js.org/)
     * leaving its original interfaces that are widely used through project.
     * https://github.com/FezVrasta/popper.js/blob/master/docs/_includes/tooltip-documentation.md
     */
    $.fn.qtip = function (command, message, messageData) {
        console.log('command: ', command);
        // console.log('JSON.stringify(this.$popper): ', JSON.stringify(this.data('$popper')));

        if(this.data('$popper') && typeof command === 'string'){
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
                    this.data('$popper')[commandsMap[command]]();
                    if(commandsMap[command] === 'dispose'){
                        this.removeData('$popper')
                    }
            }
        }
        return this;
    };

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
                    html:true,
                    title: $content[0]
                });

            }
            if($elt.data('$popper')) {
                $elt.data('$popper').dispose();
                $elt.removeData('$popper')
            }
            $elt.data('$popper', new Tooltip($elt, options));

        });

    };
});
