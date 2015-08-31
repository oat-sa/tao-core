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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define([
    'jquery',
    'util/adaptSize'
], function($, adaptSize){

    QUnit.test('API', function (assert) {
        QUnit.expect(4);

        assert.ok(typeof adaptSize === 'object', 'adaptSize returns an object');
        assert.ok(typeof adaptSize.width === 'function', 'Exposes method width()');
        assert.ok(typeof adaptSize.height === 'function', 'Exposes method height()');
        assert.ok(typeof adaptSize.both === 'function', 'Exposes method both()');
    });

    QUnit.test('Height adaptation', function (assert) {
        QUnit.expect(1);

        var $targets = $('.target'),
            $e1 = $('#e-1'),
            $e2 = $('#e-2'),
            $e3 = $('#e-3');

        // expect the adapter to set all other to the same height
        adaptSize.height($targets);

        assert.ok($e1.height() === $e2.height() && $e1.height() === $e3.height() && $e1.height() > 0, 'Adapts height of several elements');
    });


    QUnit.test('Width adaptation', function (assert) {
        QUnit.expect(1);

        var $targets = $('.target'),
            $e1 = $('#e-1'),
            $e2 = $('#e-2'),
            $e3 = $('#e-3');

        // expect the adapter to set all other to the same height
        adaptSize.width($targets);

        assert.ok($e1.width() === $e2.width() && $e1.width() === $e3.width() && $e1.width() > 0, 'Adapts width of several elements');
    });




    QUnit.test('Size adaptation, both sides', function (assert) {
        QUnit.expect(1);

        var $targets = $('.target'),
            $e1 = $('#e-1'),
            $e2 = $('#e-2'),
            $e3 = $('#e-3');

        // expect the adapter to set all other to the same height
        adaptSize.both($targets);

        var w = $e1.width() === $e2.width() && $e1.width() === $e3.width() && $e1.width() > 0;
        var h = $e1.height() === $e2.height() && $e1.height() === $e3.height() && $e1.height() > 0;

        assert.ok(w && h, 'Adapts height and of several elements in one go');
    });

});


