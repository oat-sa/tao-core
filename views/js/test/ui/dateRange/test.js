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
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

define(['jquery', 'ui/dateRange', 'jqueryui'], function ($, dateRange) {
    'use strict';

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        QUnit.expect(3);

        assert.ok(typeof dateRange === 'function', 'the module exposes a function');
        assert.ok(typeof dateRange(false, []) === 'object', 'the factory creates an object');
        assert.notEqual(dateRange({}), dateRange({}), 'the factory creates new objects');
    });

    QUnit.test('component', function (assert) {
        QUnit.expect(2);

        var range = dateRange({});

        assert.ok(typeof range.render === 'function', 'the component has a render method');
        assert.ok(typeof range.destroy === 'function', 'the component has a destroy method');
    });

    QUnit.test('eventifier', function (assert) {
        QUnit.expect(3);

        var range = dateRange({});

        assert.ok(typeof range.on === 'function', 'the component has a on method');
        assert.ok(typeof range.off === 'function', 'the component has a off method');
        assert.ok(typeof range.trigger === 'function', 'the component has a trigger method');
    });

    QUnit.module('Component');

    QUnit.test('render', function (assert) {

        QUnit.expect(3);

        var $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        dateRange().on('render', function() {
            assert.equal($('input[name="periodStart"]', $container).length, 1, 'periodStart have been created');
            assert.equal($('input[name="periodEnd"]', $container).length, 1, 'periodEnd have been created');
        }).render($container);
    });

    QUnit.test('destroy', function(assert) {
        QUnit.expect(7);

        var $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        var $range = dateRange().on('render', function() {
            assert.equal($('input[name="periodStart"]', $container).length, 1, 'periodStart have been created');
            assert.equal($('input[name="periodEnd"]', $container).length, 1, 'periodEnd have been created');
        }).render($container);

        assert.equal($('input[name="periodStart"]', $container).length, 1, 'periodStart have been created');
        assert.equal($('input[name="periodEnd"]', $container).length, 1, 'periodEnd have been created');

        $range.destroy();

        assert.equal($('input[name="periodStart"]', $container).length, 0, 'periodStart have been deleted');
        assert.equal($('input[name="periodEnd"]', $container).length, 0, 'periodEnd have been deleted');
    });

    QUnit.module('Attach');

    QUnit.test('render', function (assert) {

        QUnit.expect(4);

        var $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        var $inputFrom = $('<input type="text" name="from">');
        var $inputTo = $('<input type="text" name="to">');

        $container.append($inputFrom);
        $container.append($inputTo);

        assert.equal($('.hasDatepicker', $container).length, 0, 'DateTime have not been attached');

        dateRange({
            startInput: $inputFrom,
            endInput: $inputTo
        }).on('render', function() {
            assert.equal($('input', $container).length, 2, 'DateTime have been created');
            assert.equal($('.hasDatepicker', $container).length, 2, 'DateTime have been attached');
        }).render($container);
    });

    QUnit.test('destroy', function(assert) {
        QUnit.expect(7);

        var $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        var $inputFrom = $('<input type="text" name="from">');
        var $inputTo = $('<input type="text" name="to">');

        $container.append($inputFrom);
        $container.append($inputTo);

        assert.equal($('.hasDatepicker', $container).length, 0, 'DateTime have not been attached');

        var $range = dateRange({
            startInput: $inputFrom,
            endInput: $inputTo
        }).on('render', function() {
            assert.equal($('input', $container).length, 2, 'DateTime have been created');
            assert.equal($('.hasDatepicker', $container).length, 2, 'DateTime have been attached');
        }).render($container);

        $range.destroy();

        assert.equal($('.hasDatepicker', $container).length, 0, 'DateTime have been deleted');
        assert.equal($('input', $container).length, 2, 'Template still exists');
        assert.equal($('.component', $container).length, 0, 'Component have been deleted');
    });
});
