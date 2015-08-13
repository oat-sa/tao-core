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
 * This component manage the navigation bar of TAO.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 * @author Dieter Raber <dieter@taotesting.com>
 */
define(['jquery', 'lodash'], function($, _) {

    'use strict';

    var $body = $('body'),
        $navContainer = $('header.dark-bar'),
        $nav = $navContainer.find($('nav')),
        navHeight = $nav.find('.main-menu').height() || 0,
        navIsOversized = $body.hasClass('oversized-nav');

    /**
     * The regular height of the header is ~64px. If it's higher than that
     * this means that the right menu has slipped under the left one due
     * to a lack of space. This can happen when the logo is very long and/or
     * many extensions are installed.
     *
     * @returns {{init: init}}
     */
    var checkHeight = function checkHeight() {
        if($navContainer.height() > navHeight) {
            $body.addClass('oversized-nav');
            navIsOversized = true;
        }
        else if (navIsOversized) {
            $body.removeClass('oversized-nav');
            navIsOversized = false;
        }
    };

    /**
     * @exports layout/nav
     */
    return {

        /**
         * Initialize the navigation bar
         *
         * @author Bertrand Chevrier <bertrand@taotesting.com>
         */
        init : function(){
            //here the bindings are controllers or even the name of any AMD file to load
            $('[data-action]', $nav).off('click').on('click', function(e){
                e.preventDefault();
                var binding = $(this).data('action');
                if(binding){
                    require([binding], function(controller){
                        if(controller &&  typeof controller.start === 'function'){
                            controller.start();
                        }
                    });
                }
            });

            // check the height of the header on load and on resize
            checkHeight();
            $(window).off('resize.navheight').on('resize.navheight', _.debounce(function () {
                checkHeight();
            }, 150));
        }
    };
});
