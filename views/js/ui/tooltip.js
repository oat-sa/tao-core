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
        defaultOptions = {
            template:themesMap['default'],
            popperOptions:{
                positionFixed: true
            }
        };

    /**
     * redefinition of jquery.qtip plugin http://qtip2.com/
     * the goal is to substitude outdated lib code with new solution (https://popper.js.org/)
     * leaving its original interfaces that are widely used through project.
     * https://github.com/FezVrasta/popper.js/blob/master/docs/_includes/tooltip-documentation.md
     */
    $.fn.qtip = function (options, newValue) {
        
        var reference = this;
        if('object' === typeof options) {
            options = _.merge({}, defaultOptions, options);
        }

        return new Tooltip(reference, options);
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
                themeName = _.contains(themes, $elt.data('tooltip-theme')) ? $elt.data('tooltip-theme') : 'default';
            if($content.length){

                var options = {
                    html:true,
                    placement:'bottom ',
                    title: $content[0],
                    template:themesMap[themeName]
                };

            }

            $elt.qtip(options);
        });
    };
});
