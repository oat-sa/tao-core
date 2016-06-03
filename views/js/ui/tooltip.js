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
define(['jquery', 'lodash', 'core/dataattrhandler', 'qtip'], function($, _, DataAttrHandler){
    'use strict';

    var themes = ['dark', 'default', 'info', 'warning', 'error', 'success', 'danger'],
        themesMap = {
            'default' : 'qtip-rounded qtip-plain',
            'dark' : 'qtip-rounded qtip-dark',
            'error' : 'qtip-rounded qtip-red',
            'success' :'qtip-rounded qtip-green',
            'info' : 'qtip-rounded qtip-blue',
            'warning' : 'qtip-rounded qtip-orange',
            'danger' : 'qtip-rounded qtip-danger'
        },
        defaultOptions = {
            theme : 'warning',
            position: {
                my : 'bottom center',
                at : 'top center',
                viewport: $(window),
            },
        };

    var qtipConstructor = $.fn.qtip;

    $.fn.qtip = function (options, notation, newValue) {

        if('object' === typeof options) {
            options = _.merge({}, defaultOptions, options);
            if (options.theme && themesMap[options.theme]) {
                if (options.style === undefined) {
                    options.style = {};
                }
                options.style.classes = themesMap[options.theme];
                options = _.omit(options, ['theme']);
            }
        }

        return qtipConstructor.call(this, options, notation, newValue);
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
                $target = DataAttrHandler.getTarget('tooltip', $elt),
                theme = _.contains(themes, $elt.data('tooltip-theme')) ? $elt.data('tooltip-theme') : 'default';

            $elt.qtip({
                theme : theme,
                content: {
                    text: $target
                }
            });
        });
    };
});
