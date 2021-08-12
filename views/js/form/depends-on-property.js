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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA ;
 */

 define([
    'jquery',
    'lodash'
], function ($, _) {
    'use strict';
    function _isHiddenDependsOn($container) {
        const $typeElt = $('.property-type', $container);
        if (!$typeElt || !$typeElt.val() || !$typeElt.val().trim()) {
            return true;
        }

        const $listElt = $('.property-listvalues', $container);
        if ($listElt && $listElt.val() && $listElt.val().trim()) {
            return false;
        }

        return true;
    }

    function toggleDependsOn($dependsOnSelectbox, $wrapper, $container) {
        $container = $container || $('.property-edit-container-open .property-heading-label ~ .property-edit-container');
        if (!$container.length) {
            return;
        }
    
        $dependsOnSelectbox = $dependsOnSelectbox || $container.find('.property-depends-on');
        if (!$dependsOnSelectbox.length) {
           return;
        }   

        if (!$wrapper) {
            $wrapper = $dependsOnSelectbox;
            while (!_.isEqual($wrapper.parent()[0], $container[0])) {
                $wrapper = $wrapper.parent();
            }
        }

        if (!_isHiddenDependsOn($container)) {
            $dependsOnSelectbox.removeAttr('disabled');
            $wrapper.show();
            return;
        }

        $dependsOnSelectbox.prop('disabled', "disabled");
        $wrapper.hide();
    }

    return {
        toggle: toggleDependsOn,
    }
});


