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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'core/promise'
], function ($, Promise) {
    'use strict';

    /**
     * Keep a component element visible inside a container.
     * If the element is outside the container viewport, scroll to display it.
     * @param {String|jQuery|HTMLElement} element
     * @param {String|jQuery|HTMLElement} container
     * @returns {Promise} Returns a Promise that will always be resolved when the scroll is done
     */
    function autoscroll(element, container) {
        return new Promise(function(resolve) {
            var $element = $(element);
            var $container = $(container || $element.parent());
            var currentScrollTop, minScrollTop, maxScrollTop, scrollTop;

            if ($element.length && $container.length) {
                currentScrollTop = $container.scrollTop();
                maxScrollTop = $element.offset().top - $container.offset().top + currentScrollTop;
                minScrollTop = maxScrollTop - $container.height() + $element.outerHeight();

                scrollTop = Math.max(Math.min(maxScrollTop, currentScrollTop), minScrollTop);
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

    return autoscroll;
});
