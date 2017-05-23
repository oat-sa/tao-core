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
 * Helper to manage scrolling
 *
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'jquery',
    'core/promise',
    'ui/autoscroll',
    'util/shortcut'
], function (_, $, Promise, autoscroll, shortcuts) {
    'use strict';

    var scrollHelper;

    var ns = '.scroller';

    scrollHelper = {
        /**
         * Scroll the container so the given element is put at the top of the visible area
         * @param {String|jQuery|HTMLElement} element
         * @param {String|jQuery|HTMLElement} container
         * @param {Number} scrollSpeed - in milliseconds
         * @returns {Promise} Returns a Promise that will always be resolved when the scroll is done
         */
        scrollTo: function scrollTo(element, container, scrollSpeed) {
            return new Promise(function(resolve) {
                var $element = $(element),
                    $container = $(container || $element.parent()),
                    currentScrollTop,
                    scrollTop;

                if ($element.length && $container.length) {
                    currentScrollTop = $container.scrollTop();
                    scrollTop = $element.offset().top - $container.offset().top + currentScrollTop;

                    if (scrollTop !== currentScrollTop) {
                        $container
                            .animate({ scrollTop: scrollTop }, scrollSpeed)
                            .promise()
                            .done(resolve);
                    } else {
                        resolve();
                    }
                } else {
                    resolve();
                }
            });
        },

        /**
         * Disable default behavior of scrolling related events (mouse and keyboard)
         */
        disableScrolling: function disableScrolling() {
            ['MouseScrollUp', 'MouseScrollDown', 'ArrowUp', 'ArrowDown']
                .forEach(function(shortcutName) {
                    shortcuts.add(shortcutName + ns, function(e) {
                        e.preventDefault();
                    }, {
                        // This is weird. If not specified, scrolling won't be re-enabled, as this specific shortcut
                        // registry instance has { prevent: true } as a default setting. And it seems that it keeps preventing
                        // default behavior event when all handlers have been unregistered.
                        prevent: false
                    });
                });
        },

        /**
         * Renable scrolling events behavior
         */
        enableScrolling: function enableScrolling() {
            shortcuts.remove(ns);
        }
    };


    return scrollHelper;
});
