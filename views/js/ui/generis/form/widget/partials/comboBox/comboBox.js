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

define([
    'jquery',
    'lodash',
    'i18n',
    'ui/generis/form/widgets/_widget',
    'tpl!ui/generis/form/widgets/comboBox/comboBox',
    'css!tao/ui/generis/form/widgets/_widget'
], function(
    $,
    _,
    __,
    widgetFactory,
    tpl
) {
    'use strict';

    /**
     * The factory
     * @returns {ui/component}
     */
    function factory() {
        return widgetFactory()
        .setTemplate(tpl)
        .on('render', function () {
            var $el = this.getElement();

            if (this.config.value) {
                $el
                .find('select option[value="' + this.config.value + '"]')
                .attr({ selected: 'selected' });
            }
        });
    }

    return factory;
});