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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'core/promise',
    'ui/autoscroll'
], function ($, Promise, autoscroll) {
    'use strict';

    var scrollHelper;



    scrollHelper = {
        /**
         * Keep a component element visible inside a container.
         * If the element is outside the container viewport, scroll to display it.
         * @param {String|jQuery|HTMLElement} element
         * @param {String|jQuery|HTMLElement} container
         * @returns {Promise} Returns a Promise that will always be resolved when the scroll is done
         */
        ensureVisible: function ensureVisible(element, container) {
            autoscroll(element, container);
        },

        /**
         * Scroll the container so the given element is put at the top of the visible area
         * @param {String|jQuery|HTMLElement} element
         * @param {String|jQuery|HTMLElement} container
         * @returns {Promise} Returns a Promise that will always be resolved when the scroll is done
         */
        scrollTo: function scrollTo(element, container) {
            return new Promise(function(resolve) {
                var $element = $(element),
                    $container = $(container || $element.parent()),
                    currentScrollTop,
                    scrollTop;

                if ($element.length && $container.length) {
                    currentScrollTop = $container.scrollTop();
                    scrollTop = $element.offset().top - $container.offset().top + currentScrollTop;

                    if (scrollTop !== currentScrollTop) {
                        $container.animate({scrollTop:scrollTop}).promise().done(resolve);
                    } else {
                        resolve();
                    }
                } else {
                    resolve();
                }
            });
        }
    };

    return scrollHelper;
});
