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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'lodash',
    'ui/generis/validator/validator',
    'ui/generis/widget/comboBox/comboBox',
    'ui/generis/widget/comboSearchBox/comboSearchBox',
    'json!test/ui/generis/widget/comboSearchBox/data.json'
], function(
    $,
    _,
    validator,
    comboBox,
    comboSearchBox,
    data
) {
    'use strict';


    /**
     * Visual Test
     */
    QUnit.module('Visual Test');

    QUnit.test('Display and play', function (assert) {
        var prop, tb1, tb2;

        prop = data.properties[0];
        prop.range = data.values[prop.range];

        tb1 = comboBox({}, prop)
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        tb2 = comboSearchBox({}, prop)
        .on('render', function () {
            assert.ok(true);
        })
        .render('#display-and-play > form > fieldset');

        $('#validate').on('click', function (e) {
            e.preventDefault();

            tb1.validate();
            console.log(tb1.serialize());

            tb2.validate();
            console.log(tb2.serialize());

            return false;
        });
    });
});

