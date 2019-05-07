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
        { title : 'submit' },
        { title : 'reset' },
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
        var container = document.querySelector('#qunit-fixture');
        var done      = assert.async();

        assert.expect(6);

        assert.equal(container.querySelectorAll('input').length, 0, 'No input field found');
        assert.equal(container.querySelectorAll('button').length, 0, 'No button found');

        dateRangeFactory(container)
            .on('ready', function(){
                var element = this.getElement()[0];
                var startField = element.querySelector('input[name="periodStart"]');
                var endField = element.querySelector('input[name="periodEnd"]');
                var applyButton = element.querySelector('button[data-control="filter"]');
                var resetButton = element.querySelector('button[data-control="reset"]');

                assert.ok(startField instanceof HTMLInputElement, 'The start field is attached');
                assert.ok(endField instanceof HTMLInputElement, 'The end field is attached');
                assert.ok(applyButton instanceof HTMLButtonElement, 'The apply button is attached');
                assert.ok(resetButton instanceof HTMLButtonElement, 'The reset button is attached');

                done();
            });
    });

    QUnit.test('Custom buttons configuration', function(assert) {
        var container = document.querySelector('#qunit-fixture');
        var done      = assert.async();

        assert.expect(5);

        assert.equal(container.querySelectorAll('button').length, 0, 'No button found');

        dateRangeFactory(container, {
            resetButton : {
                enable : false
            },
            applyButton : {
                enable : true,
                label : 'Filter',
                title : 'Filter the values'
            },
        })
        .on('ready', function(){
            var element = this.getElement()[0];
            var applyButton = element.querySelector('button[data-control="filter"]');
            var resetButton = element.querySelector('button[data-control="reset"]');

            assert.equal(resetButton, null, 'The reset button is not attached');
            assert.ok(applyButton instanceof HTMLButtonElement, 'The apply button is attached');
            assert.ok(applyButton.textContent.trim(), 'Filter', 'The apply button label is correct');
            assert.ok(applyButton.getAttribute('title'), 'Filter the values', 'The apply button title is correct');

            done();
        });
    });

    QUnit.test('Set range values', function(assert) {
        var container = document.querySelector('#qunit-fixture');
        var done      = assert.async();

        assert.expect(12);

        assert.equal(container.querySelectorAll('button').length, 0, 'No button found');

        dateRangeFactory(container)
        .on('ready', function(){
            var element = this.getElement()[0];
            var startField = element.querySelector('input[name="periodStart"]');
            var endField = element.querySelector('input[name="periodEnd"]');
            var applyButton = element.querySelector('button[data-control="filter"]');

            assert.ok(startField instanceof HTMLInputElement, 'The start field is attached');
            assert.ok(endField instanceof HTMLInputElement, 'The end field is attached');
            assert.ok(applyButton instanceof HTMLButtonElement, 'The apply button is attached');
            assert.equal(startField.value, '', 'The start field value is empty');
            assert.equal(endField.value, '', 'The end field value is empty');
            assert.equal(this.getStart(), '', 'The start value is empty');
            assert.equal(this.getEnd(), '', 'The end value is empty');

            startField.value = '2019-01-01 12:00:00';
            endField.value = '2019-01-02 00:00:00';

            applyButton.click();
        })
        .on('submit', function(start, end){

            assert.equal(start, '2019-01-01 12:00:00', 'The start value is correct');
            assert.equal(end, '2019-01-02 00:00:00', 'The end vaue is correct');
            assert.equal(this.getStart(), '2019-01-01 12:00:00', 'The start value is correct');
            assert.equal(this.getEnd(), '2019-01-02 00:00:00', 'The end value is correct');

            done();
        });
    });

    QUnit.test('Reset range values', function(assert) {
        var container = document.querySelector('#qunit-fixture');
        var done      = assert.async();

        assert.expect(16);

        assert.equal(container.querySelectorAll('button').length, 0, 'No button found');

        dateRangeFactory(container)
            .on('ready', function(){
                var element = this.getElement()[0];
                var startField = element.querySelector('input[name="periodStart"]');
                var endField = element.querySelector('input[name="periodEnd"]');
                var resetButton = element.querySelector('button[data-control="reset"]');

                assert.ok(startField instanceof HTMLInputElement, 'The start field is attached');
                assert.ok(endField instanceof HTMLInputElement, 'The end field is attached');
                assert.ok(resetButton instanceof HTMLButtonElement, 'The reset button is attached');
                assert.equal(startField.value, '', 'The start field value is empty');
                assert.equal(endField.value, '', 'The end field value is empty');
                assert.equal(this.getStart(), '', 'The start value is empty');
                assert.equal(this.getEnd(), '', 'The end value is empty');

                startField.value = '2019-04-01 12:00:00';
                endField.value = '2019-04-02 00:00:00';

                assert.equal(this.getStart(), '2019-04-01 12:00:00', 'The start value is set');
                assert.equal(this.getEnd(), '2019-04-02 00:00:00', 'The end value is set');

                resetButton.click();
            })
            .on('reset', function(){
                var element = this.getElement()[0];
                var startField = element.querySelector('input[name="periodStart"]');
                var endField = element.querySelector('input[name="periodEnd"]');

                assert.ok(startField instanceof HTMLInputElement, 'The start field is attached');
                assert.ok(endField instanceof HTMLInputElement, 'The end field is attached');
                assert.equal(startField.value, '', 'The start field value is empty');
                assert.equal(endField.value, '', 'The end field value is empty');
                assert.equal(this.getStart(), '', 'The start value is empty');
                assert.equal(this.getEnd(), '', 'The end value is empty');

                done();
            });
    });

    QUnit.test('Set via picker', function(assert) {
        var container = document.querySelector('#qunit-fixture');
        var done      = assert.async();

        assert.expect(8);

        assert.equal(container.querySelectorAll('button').length, 0, 'No button found');

        dateRangeFactory(container, {

            startPicker : {
                setup : 'date',
                field : {
                    name : 'periodStart',
                    value : '2019-04-05'
                }
            },
            endPicker : {
                setup : 'date',
                field : {
                    name : 'periodEnd',
                }
            },
        })
        .on('ready', function(){
            var element = this.getElement()[0];
            var startField = element.querySelector('input[name="periodStart"]');
            var startPicker = element.querySelector('.start .datetime-picker .flatpickr-calendar');

            assert.ok(startField instanceof HTMLInputElement, 'The start field is attached');
            assert.ok(startPicker instanceof HTMLElement, 'The start picker is attached');
            assert.ok( ! startPicker.classList.contains('open'), 'The start picker is closed');

            startField.dispatchEvent(new MouseEvent('mousedown', {bubbles : true}));

            setTimeout(function(){
                assert.ok(startPicker.classList.contains('open'), 'The start picker is now open');

                startPicker
                    .querySelector('.flatpickr-day[aria-label="2019-04-10"]')
                    .dispatchEvent(new MouseEvent('mousedown', {bubbles : true}));

            }, 300);
        })
        .on('close', function(target, value){

            assert.equal(target, 'start', 'The start date get changed');
            assert.equal(value, '2019-04-10', 'The start value is set');
            assert.equal(this.getStart(), '2019-04-10', 'The start value is set');

            done();
        });
    });
});
