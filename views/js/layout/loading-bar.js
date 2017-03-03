/*
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

/**
 * Loading bar a.k.a. Knight Rider
 *
 * @author dieter <dieter@taotesting.com>
 */
define(['jquery'],
    function ($) {
        'use strict';

        var $loadingBar = $('.loading-bar'),
            originalHeight = $loadingBar.height(),
            $win = $(window),
            $doc = $(document),
            $contentWrap    = $('.content-wrap'),
            headerElements  = {
                $versionWarning: $contentWrap.find('.version-warning'),
                $header: $contentWrap.find('header:first()')
            },
            headerHeight = getHeaderHeight(headerElements);

        /**
         * the TAO header can have three different forms
         * 1. version warning on alpha/beta + main navi
         * 2. main navi only on regular version
         * 3. nothing in the case of LTI
         *
         * @param headerElements
         */
        function getHeaderHeight(headerElements){
            var $element;
            headerHeight = 0;
            for($element in headerElements) {
                if(headerElements[$element].length && headerElements[$element].is(':visible')) {
                    headerHeight += headerElements[$element].outerHeight();
                }
            }
            return headerHeight;
        }

        /**
         * Update height of cover element
         */
        function updateHeight() {
            if (!$loadingBar.hasClass('loading')) {
                return;
            }
            // status of height would change for instance when version warning is hidden
            headerHeight = getHeaderHeight(headerElements);

            if (headerHeight <= $win.scrollTop()) {
                $loadingBar.addClass('fixed');
            } else {
                $loadingBar.removeClass('fixed');
            }

            if ($loadingBar.hasClass('loadingbar-covered')) {
                $loadingBar.height($doc.height());
            } else {
                $loadingBar.height('');
            }
        }

        $win.on('scroll.loadingbar', function () {
            updateHeight();
        });

        return {
            /**
             * Show loading bar
             * @param {Boolean} [covered = true] - - whether overlay HTML element should be added (disable GUI).
             */
            start: function (covered) {
                if (typeof covered === 'undefined') {
                    covered = true;
                }
                if ($loadingBar.hasClass('loading')) {
                    $loadingBar.stop();
                }
                $loadingBar.addClass('loading');
                $loadingBar.toggleClass('loadingbar-covered', covered);
                updateHeight();
            },
            stop: function () {
                $loadingBar.removeClass('loading fixed').height(originalHeight);
            }
        };
    });
