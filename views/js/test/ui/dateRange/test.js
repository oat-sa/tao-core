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
 * Copyright (c) 2016-2019  (original work) Open Assessment Technologies SA;
 *
 */

/**
 * Test the dateRange component
 *
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['ui/dateRange/dateRange'], function(dateRangeFactory) {
    'use strict';

    QUnit.module('API');

    QUnit.cases.init([
        { title : 'init' },
        { title : 'destroy' },
        { title : 'render' },
        { title : 'show' },
        { title : 'hide' },
        { title : 'enable' },
        { title : 'disable' },
        { title : 'is' },
        { title : 'setState' },
        { title : 'getContainer' },
        { title : 'getElement' },
        { title : 'getTemplate' },
        { title : 'setTemplate' },
    ]).test('Component API ', function(data, assert) {
        assert.expect(1);
        assert.equal(typeof dateRangeFactory()[data.title], 'function', 'The range component exposes the component method "' + data.title);
    });

    QUnit.cases.init([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        assert.expect(1);
        assert.equal(typeof dateRangeFactory()[data.title], 'function', 'The range component exposes the eventifier method "' + data.title);
    });

    QUnit.cases.init([
        { title : 'getStart' },
        { title : 'getEnd' },
    ]).test('Picker API ', function(data, assert) {
        assert.expect(1);
        assert.equal(typeof dateRangeFactory()[data.title], 'function', 'The range component exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.test('Lifecycle', function(assert) {
        var container = document.querySelector('#qunit-fixture');
        var done      = assert.async();

        assert.expect(2);

        dateRangeFactory(container)
            .on('init', function(){
                assert.ok(!this.is('rendered'), 'The component is not yet rendered');
            })
            .on('render', function(){
                assert.ok(this.is('rendered'), 'The component is now rendered');

                this.destroy();
            })
            .on('destroy', done);
    });

    QUnit.test('Default configuration', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(6);

        dateRangeFactory(container)
        .on('init', function(){
            assert.equal(container.querySelectorAll('input').length, 0, 'No input field found');
            assert.equal(container.querySelectorAll('button').length, 0, 'No button field found');
        })
        .on('render', function(){
            var element = this.getElement()[0];

            assert.ok(element.querySelector('input[name="periodStart"]') instanceof HTMLInputElement, 'The start field is attached');
            assert.ok(element.querySelector('input[name="periodEnd"]') instanceof HTMLInputElement, 'The end field is attached');

            assert.ok(element.querySelector('button[data-control="filter"]') instanceof HTMLButtonElement, 'The apply button is attached');
            assert.ok(element.querySelector('button[data-control="reset"]') instanceof HTMLButtonElement, 'The reset button is attached');

            done();
        });
    });
});
