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

        var $targets = $('.target');

        // expect the adapter to set all other to the same height
        adaptSize.height($targets);

        assert.ok($('#e-1').height() === $('#e-2').height() && $('#e-1').height() === $('#e-3').height(), 'Height adaptation');
    });


    QUnit.test('Width adaptation', function (assert) {
        QUnit.expect(1);

        var $targets = $('.target');

        // expect the adapter to set all other to the same height
        adaptSize.width($targets);

        assert.ok($('#e-1').width() === $('#e-2').width() && $('#e-1').width() === $('#e-3').width(), 'Width adaptation');
    });




    QUnit.test('Size adaptation, both sides', function (assert) {
        QUnit.expect(1);

        var $targets = $('.target');

        // expect the adapter to set all other to the same height
        adaptSize.both($targets);

        var w = $('#e-1').width() === $('#e-2').width() && $('#e-1').width() === $('#e-3').width();
        var h = $('#e-1').height() === $('#e-2').height() && $('#e-1').height() === $('#e-3').height();

        assert.ok(w && h, 'Size adaptation, both sides');
    });

});


