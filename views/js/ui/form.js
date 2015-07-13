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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define(['jquery'], function($){

    'use strict';

    /**
     * Toggle radios and checkboxes wrapped into a pseudo label element to simulate the behavior of a label
     * @param {String} selector - to scope the listening
     */
    var pseudoLabel = function pseudoLabel(selector){

        $(document).on('click', selector + ' .pseudo-label-box', function () {
            $(this).find('input').trigger('click').focus();
        });
    };

    /**
     * Prevent clicks and focus on disbled elements
     * @param {String} selector - to scope the listening
     */
    var preventDisabled = function preventDisabled(selector){

        $(document).on('click', selector + ' .disabled, ' + selector + ' :disabled', function (e) {
            e.preventDefault();
            return false;
        });
    };

    /**
     * Manages general behavior on form elements
     *
     * @param {jQueryElement} $container - the root context to lookup inside
     */
    return function listenFormBehavior($container){
        var selector = $container.selector || '.tao-scope';

        pseudoLabel(selector);
        preventDisabled(selector);
    };
});
