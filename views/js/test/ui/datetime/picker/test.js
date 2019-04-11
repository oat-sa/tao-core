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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the date time picker component
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'ui/datetime/picker'
], function(dateTimePicker) {
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
        assert.equal(typeof dateTimePicker()[data.title], 'function', 'The picker exposes the component method "' + data.title);
    });

    QUnit.cases.init([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        assert.expect(1);
        assert.equal(typeof dateTimePicker()[data.title], 'function', 'The picker exposes the eventifier method "' + data.title);
    });

    QUnit.cases.init([
        { title : 'clear' },
        { title : 'close' },
        { title : 'open' },
        { title : 'toggle' },
        { title : 'getFormat' },
        { title : 'getValue' },
        { title : 'setValue' },
        { title : 'getSelectedDates' },
        { title : 'updateConstraints' },
    ]).test('Picker API ', function(data, assert) {
        assert.expect(1);
        assert.equal(typeof dateTimePicker()[data.title], 'function', 'The picker exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.test('Lifecycle', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(4);

        dateTimePicker(container)
            .on('init', function(){
                assert.ok(!this.is('rendered'), 'The component is not yet rendered');
                assert.ok(!this.is('ready'), 'The component is not yet ready');
            })
            .on('render', function(){
                assert.ok(this.is('rendered'), 'The component is now rendered');
                assert.ok(!this.is('ready'), 'The component is not yet ready');

                this.destroy();
            })
            .on('destroy', done);
    });

    QUnit.test('Default Input field', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(6);

        dateTimePicker(container, {
            setup : 'date',
            format : 'DD/MM/YYYY'
        })
        .on('init', function(){
            assert.equal(container.querySelectorAll('input').length, 0, 'No input field found');
        })
        .on('render', function(){
            var field = this.getElement()[0].querySelector('input');
            assert.ok(field instanceof HTMLInputElement, 'The input field has been created');
            assert.equal(field.disabled, true, 'The input field starts disabled');
            assert.equal(field.placeholder, 'dd/mm/yyyy', 'The field placeholder is the format by default ');
        })
        .on('ready', function(){
            var field = this.getElement()[0].querySelector('input');
            assert.ok(field instanceof HTMLInputElement, 'The input field has been created');
            assert.equal(field.disabled, false, 'The input field is enabled once ready');

            done();
        });
    });

    QUnit.test('Customized Input field', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(9);

        dateTimePicker(container, {
            setup : 'time',
            field :  {
                id :          'lunch-time-12',
                name :        'lunch-time',
                placeholder : 'Enter lunch time',
                value :       '11 :55',
                pattern :     '[0-9]{2}:[0-9]{2}',
                label :       'Usual lunch time'
            }
        })
        .on('init', function(){
            assert.equal(container.querySelectorAll('input').length, 0, 'No input field found');
        })
        .on('ready', function(){
            var field = this.getElement()[0].querySelector('input');
            assert.ok(field instanceof HTMLInputElement, 'The input field has been created');

            assert.equal(field.disabled, false, 'The input field is enabled');
            assert.equal(field.id, 'lunch-time-12', 'The input field id has the configured value');
            assert.equal(field.name, 'lunch-time', 'The input field name has the configured value');
            assert.equal(field.placeholder, 'Enter lunch time', 'The input field placeholder has the configured value');
            assert.equal(field.value, '11:55', 'The input field value has the configured value');
            assert.equal(field.pattern, '[0-9]{2}:[0-9]{2}', 'The input field pattern has the configured value');
            assert.equal(field.getAttribute('aria-label'), 'Usual lunch time', 'The input field label has the configured value');

            done();
        });
    });

    QUnit.test('Input field with controls', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(8);

        dateTimePicker(container, {
            setup : 'datetime',
            controlButtons : true,
            field :  {
                name : 'arrival'
            }
        })
        .on('init', function(){
            assert.equal(container.querySelectorAll('input').length, 0, 'No input field found');
            assert.equal(container.querySelectorAll('.picker-toggle').length, 0, 'The toggle button is not attached');
            assert.equal(container.querySelectorAll('.picker-clear').length, 0, 'The clear button is not attached');
        })
        .on('ready', function(){
            var element = this.getElement()[0];
            var field   = element.querySelector('input');

            assert.ok(field instanceof HTMLInputElement, 'The input field has been created');

            assert.equal(field.disabled, false, 'The input field is enabled');
            assert.equal(field.name, 'arrival', 'The input field name has the configured value');
            assert.equal(element.querySelectorAll('.picker-toggle').length, 1, 'The toggle button is added');
            assert.equal(element.querySelectorAll('.picker-clear').length, 1, 'The clear button is added');

            done();
        });
    });

    QUnit.test('Replace Input field', function(assert) {
        var container = document.querySelector('#qunit-fixture form fieldset');
        var done      = assert.async();
        var originalField = container.querySelector('input');

        assert.expect(12);

        assert.ok(originalField instanceof HTMLInputElement, 'The original field exists');
        assert.ok(originalField.parentNode instanceof HTMLElement, 'The original field is attached');
        assert.equal(originalField.id, 'today-12', 'The original field id is defined');
        assert.equal(originalField.name, 'today', 'The original field name is defined');
        assert.equal(originalField.placeholder, 'Enter today\'s date', 'The original field placeholder is defined');
        assert.equal(originalField.value, '03/04/2019', 'The original field value is defined');

        dateTimePicker(container, {
            setup : 'date',
            format : 'DD/MM/YYYY',
            replaceField : originalField
        })
        .on('ready', function(){
            var field = this.getElement()[0].querySelector('input');

            assert.equal(originalField.parentNode, null, 'The original field is detached');

            assert.ok(field instanceof HTMLInputElement, 'The input field has been created');
            assert.equal(field.id, 'today-12', 'The id has been taken from the original field');
            assert.equal(field.name, 'today', 'The name has been taken from the original field');
            assert.equal(field.placeholder, 'Enter today\'s date', 'The placeholder has been taken from the original field');
            assert.equal(field.value, '03/04/2019', 'The value has been taken from the original field');

            done();
        });
    });

    QUnit.test('Open the picker', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(4);

        dateTimePicker(container)
            .on('init', function(){
                assert.equal(container.querySelectorAll('.flatpickr-calendar').length, 0, 'The picker is not rendered');
            })
            .on('ready', function(){
                var picker = this.getElement()[0].querySelector('.flatpickr-calendar');
                assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
                assert.ok(!picker.classList.contains('open'), 'The picker is closed');

                this.open();
            })
            .on('open', function(){
                var picker = this.getElement()[0].querySelector('.flatpickr-calendar');
                assert.ok(picker.classList.contains('open'), 'The picker is open');

                done();
            });
    });

    QUnit.test('Open / Close the picker', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(5);

        dateTimePicker(container)
            .on('init', function(){
                assert.equal(container.querySelectorAll('.flatpickr-calendar').length, 0, 'The picker is not rendered');
            })
            .on('ready', function(){
                var picker = container.querySelector('.flatpickr-calendar');
                assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
                assert.ok(!picker.classList.contains('open'), 'The picker is closed');

                this.open();
            })
            .on('open', function(){
                var picker = this.getElement()[0].querySelector('.flatpickr-calendar');
                assert.ok(picker.classList.contains('open'), 'The picker is open');

                this.close();
            })
            .on('close', function(){
                var picker = this.getElement()[0].querySelector('.flatpickr-calendar');
                assert.ok(!picker.classList.contains('open'), 'The picker is closed');

                done();
            });
    });

    QUnit.test('Toggle the picker', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(5);

        dateTimePicker(container)
            .on('init', function(){
                assert.equal(container.querySelectorAll('.flatpickr-calendar').length, 0, 'The picker is not rendered');
            })
            .on('ready', function(){
                var picker = container.querySelector('.flatpickr-calendar');
                assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
                assert.ok(!picker.classList.contains('open'), 'The picker is closed');

                this.toggle();
            })
            .on('open', function(){
                var picker = this.getElement()[0].querySelector('.flatpickr-calendar');
                assert.ok(picker.classList.contains('open'), 'The picker is open');

                this.toggle();
            })
            .on('close', function(){
                var picker = this.getElement()[0].querySelector('.flatpickr-calendar');
                assert.ok(!picker.classList.contains('open'), 'The picker is closed');

                done();
            });
    });

    QUnit.cases.init([{
        title : 'default date format and locale',
        config : {
            setup : 'date'
        },
        format : 'YYYY-MM-DD'
    }, {
        title : 'default date format and "en" locale',
        config : {
            setup : 'date',
            locale : 'en',
            useLocalizedFormat : true
        },
        format : 'MM/DD/YYYY'
    }, {
        title : 'given date format and "en" locale',
        config : {
            setup : 'date',
            locale : 'en',
            format : 'MM-DD-YYYY'
        },
        format : 'MM-DD-YYYY'
    }, {
        title : 'default datetime format and "de" locale',
        config : {
            setup : 'datetime',
            locale : 'de',
            useLocalizedFormat : true
        },
        format : 'DD.MM.YYYY HH:mm'
    }, {
        title : 'given datetime format and "fr" locale',
        config : {
            setup : 'datetime',
            locale : 'fr',
            format : 'DD/MM/YYYY HH:mm'
        },
        format : 'DD/MM/YYYY HH:mm'
    }, {
        title : 'default time format and "en" locale (ampm)',
        config : {
            setup : 'time',
            locale : 'en',
            useLocalizedFormat : true
        },
        format : 'h:mm A'
    }, {
        title : 'given time format and "en" locale (no ampm)',
        config : {
            setup : 'time',
            locale : 'en',
            format : 'HH:mm'
        },
        format : 'HH:mm'
    }, {
        title : 'given date time format with seconds',
        config : {
            setup : 'datetime',
            format : 'YYYY-MM-DD HH:mm:SS'
        },
        format : 'YYYY-MM-DD HH:mm:SS'
    }]).test('format ', function(data, assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(1);

        dateTimePicker(container, data.config)
            .on('render', function(){

                assert.equal(this.getFormat(), data.format, 'The format is correct');
                done();
            });
    });

    QUnit.test('Select a date in picker', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(7);

        dateTimePicker(container)
            .on('ready', function(){
                var element =  this.getElement()[0];
                var picker = element.querySelector('.flatpickr-calendar');
                var field  = element.querySelector('input');

                assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
                assert.ok(!picker.classList.contains('open'), 'The picker is closed');

                assert.ok(field instanceof HTMLElement,  'The field is attached to the element');
                assert.equal(field.value, '', 'The field has no value');

                field.dispatchEvent(new MouseEvent('mousedown', {bubbles : true}));
            })
            .on('open', function(){
                var element =  this.getElement()[0];
                var picker = element.querySelector('.flatpickr-calendar');

                assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
                assert.ok(picker.classList.contains('open'), 'The picker is now open');

                picker
                    .querySelector('.flatpickr-day.today')
                    .dispatchEvent(new MouseEvent('mousedown', {bubbles : true}));
            })
            .on('close', function(){
                var field  = this.getElement()[0].querySelector('input');
                assert.ok(field.value.length > 0, 'A value is set');

                done();
            });
    });

    QUnit.test('Enter the selected date', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(9);

        dateTimePicker(container, {
            setup : 'date',
            format : 'DD/MM/YYYY'
        })
        .on('ready', function(){
            var element =  this.getElement()[0];
            var picker = element.querySelector('.flatpickr-calendar');
            var field  = element.querySelector('input');

            assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
            assert.ok(!picker.classList.contains('open'), 'The picker is closed');

            assert.ok(field instanceof HTMLElement,  'The field is attached to the element');
            assert.equal(field.value, '', 'The field has no value');

            field.dispatchEvent(new MouseEvent('mousedown', {bubbles : true}));
        })
        .on('open', function(){
            var element = this.getElement()[0];
            var field   = element.querySelector('input');

            this.on('change', function(){
                var picker = element.querySelector('.flatpickr-calendar');
                var day    = picker.querySelector('.flatpickr-day.selected');
                var month  = picker.querySelector('.flatpickr-current-month .cur-month');
                var year   = picker.querySelector('.flatpickr-current-month .cur-year');

                this.off('change');

                assert.ok(picker.classList.contains('open'), 'The picker is now open');
                assert.equal(day.textContent.trim(), '3', 'The selected date is correct');
                assert.equal(month.textContent.trim(), 'April', 'The selected month is correct');
                assert.equal(year.value.trim(), '2019', 'The selected year is correct');

                assert.equal(day.getAttribute('aria-label'), '03/04/2019', 'The aria date label is correct');

                done();
            });

            field.value = '03/04/2019';
            field.dispatchEvent(new KeyboardEvent('keydown',{
                code : 'Enter',
                key : 'Enter',
                charKode : 13,
                keyCode : 13,
                bubbles  : true
            }));
        });
    });

    QUnit.test('Set the value', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(12);

        dateTimePicker(container, {
            setup : 'date',
            format : 'YYYY-MM-DD'
        })
        .on('ready', function(){
            var element =  this.getElement()[0];
            var picker = element.querySelector('.flatpickr-calendar');
            var field  = element.querySelector('input');

            assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
            assert.ok(!picker.classList.contains('open'), 'The picker is closed');

            assert.ok(field instanceof HTMLElement,  'The field is attached to the element');
            assert.equal(field.value, '', 'The field has no value');
            assert.equal(this.getValue(), '', 'The current value is empty');

            this.setValue('2019-05-01');

            assert.equal(field.value, '2019-05-01', 'The field has been updated');
            assert.equal(this.getValue(), '2019-05-01', 'The current value has been updated');

            this.open();
        })
        .on('open', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');
            var day     = picker.querySelector('.flatpickr-day.selected');
            var month   = picker.querySelector('.flatpickr-current-month .cur-month');
            var year    = picker.querySelector('.flatpickr-current-month .cur-year');

            assert.ok(picker.classList.contains('open'), 'The picker is now open');
            assert.equal(day.textContent.trim(), '1', 'The selected date is correct');
            assert.equal(month.textContent.trim(), 'May', 'The selected month is correct');
            assert.equal(year.value.trim(), '2019', 'The selected year is correct');

            assert.equal(day.getAttribute('aria-label'), '2019-05-01', 'The aria date label is correct');

            done();
        });
    });

    QUnit.test('Get value on change', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(7);

        dateTimePicker(container, {
            setup : 'date',
            format : 'MM.DD.YYYY',
            field : {
                name : 'easter',
                value : '04.30.2015'
            }
        })
        .on('init', function(){
            assert.equal(this.getValue(), null, 'The value is not yet set');
        })
        .on('ready', function(){
            assert.equal(this.getValue(), '04.30.2015', 'The default value is set');
            this.open();
        })
        .on('open', function(){
            var element =  this.getElement()[0];
            var picker = element.querySelector('.flatpickr-calendar');

            assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
            assert.ok(picker.classList.contains('open'), 'The picker is now open');

            picker
                .querySelector('.flatpickr-day:not(.prevMonthDay)') //04.01.2015
                .dispatchEvent(new MouseEvent('mousedown', {bubbles : true}));
        })
        .on('change', function(value){
            var field  = this.getElement()[0].querySelector('input[name="easter"]');
            assert.equal(field.value, '04.01.2015', 'The field value is correct');
            assert.equal(this.getValue(), '04.01.2015', 'The method value is correct');
            assert.equal(value, '04.01.2015', 'The event parameter value is correct');

            done();
        });
    });

    QUnit.test('Clearing', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(6);

        dateTimePicker(container, {
            setup : 'datetime',
            locale : 'fr',
            useLocalizedFormat : true,
            field : {
                value : '03/04/2019 03:33'
            }
        })
        .on('ready', function(){
            this.open();
        })
        .on('open', function(){
            var element =  this.getElement()[0];
            var picker = element.querySelector('.flatpickr-calendar');

            assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
            assert.ok(picker.classList.contains('open'), 'The picker is now open');
            assert.equal(this.getValue(), '03/04/2019 03:33', 'The value is the default value');

            this.clear();
        })
        .on('clear', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');
            var field   = element.querySelector('input');

            assert.ok(!picker.classList.contains('open'), 'The picker is now closed');

            assert.equal(field.value, '', 'The field value has been cleared');
            assert.equal(this.getValue(), '', 'The method value is correct');

            done();
        });
    });

    QUnit.test('Toggle via control button', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(5);

        dateTimePicker(container, {
            setup : 'time',
            locale : 'en',
            useLocalizedFormat : true,
            controlButtons : true
        })
        .on('ready', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');
            var toggle  = element.querySelector('.picker-toggle');

            assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
            assert.ok(!picker.classList.contains('open'), 'The picker is closed');

            assert.ok(toggle instanceof HTMLButtonElement, 'The toggle control button has been created');

            toggle.click();
        })
        .on('open', function(){
            var element =  this.getElement()[0];
            var picker = element.querySelector('.flatpickr-calendar');

            assert.ok(picker.classList.contains('open'), 'The picker is now open');

            //picker internal state seems to not be synchronous, even if we rely on it's events...
            setTimeout(function(){
                element.querySelector('.picker-toggle').click();
            }, 0);
        })
        .on('close', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');

            assert.ok(!picker.classList.contains('open'), 'The picker is now closed');

            done();
        });
    });

    QUnit.test('Clear via control button', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(6);

        dateTimePicker(container, {
            setup : 'time',
            locale : 'de',
            format : 'HH:mm',
            controlButtons : true,
            field : {
                value : '14:15'
            }
        })
        .on('ready', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');
            var clear   = element.querySelector('.picker-clear');

            assert.ok(picker instanceof HTMLElement,  'The picker is attached to the element');
            assert.ok(!picker.classList.contains('open'), 'The picker is closed');

            assert.ok(clear instanceof HTMLButtonElement, 'The clear control button has been created');

            assert.equal(this.getValue(), '14:15', 'The current value is the default value');

            clear.click();
        })
        .on('clear', function(){
            var element = this.getElement()[0];
            var field   = element.querySelector('input');

            assert.equal(field.value, '',     'The field value has been cleared');
            assert.equal(this.getValue(), '', 'The method value is correct');

            done();
        });
    });

    QUnit.test('Disable the component', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(9);

        dateTimePicker(container, {
            setup : 'date',
            format : 'YYYY-MM-DD',
            controlButtons : true
        })
        .on('ready', function(){
            var element = this.getElement()[0];
            var field   = element.querySelector('input');
            var toggle  = element.querySelector('.picker-toggle');
            var clear   = element.querySelector('.picker-clear');

            assert.ok(field instanceof HTMLInputElement,   'The input field has been created');
            assert.ok(toggle instanceof HTMLButtonElement, 'The toggle control button has been created');
            assert.ok(clear instanceof HTMLButtonElement,  'The clear control button has been created');

            assert.equal(field.disabled, false,  'The input field starts enabled');
            assert.equal(toggle.disabled, false, 'The toggle control starts enabled');
            assert.equal(clear.disabled, false,  'The clear control starts enabled');

            this.disable();
        })
        .on('disable', function(){
            var element = this.getElement()[0];
            var field  = element.querySelector('input');
            var toggle  = element.querySelector('.picker-toggle');
            var clear  = element.querySelector('.picker-clear');

            assert.equal(field.disabled, true,  'The input field is now disabled');
            assert.equal(toggle.disabled, true, 'The toggle control is now disabled');
            assert.equal(clear.disabled, true,  'The clear control is now disabled');

            done();
        });
    });

    QUnit.test('Date constraints', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(10);

        dateTimePicker(container, {
            setup : 'date',
            format : 'YYYY-MM-DD',
            constraints : {
                minDate : '2019-04-05',
                maxDate : '2019-04-10'
            }
        })
        .on('ready', function(){
            this.open();
        })
        .on('open', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');
            var month   = picker.querySelector('.flatpickr-current-month .cur-month');
            var year    = picker.querySelector('.flatpickr-current-month .cur-year');

            var firstOfApril        = picker.querySelector('.flatpickr-day[aria-label="2019-04-01"]');
            var forthOfApril        = picker.querySelector('.flatpickr-day[aria-label="2019-04-04"]');
            var fifthOfApril        = picker.querySelector('.flatpickr-day[aria-label="2019-04-05"]');
            var seventhOfApril      = picker.querySelector('.flatpickr-day[aria-label="2019-04-07"]');
            var teenthOfApril       = picker.querySelector('.flatpickr-day[aria-label="2019-04-10"]');
            var eleventhOfApril     = picker.querySelector('.flatpickr-day[aria-label="2019-04-11"]');
            var twentysecondOfApril = picker.querySelector('.flatpickr-day[aria-label="2019-04-22"]');

            assert.ok(picker.classList.contains('open'), 'The picker is now open');
            assert.equal(month.textContent.trim(), 'April', 'The selected month is correct');
            assert.equal(year.value.trim(), '2019', 'The selected year is correct');

            assert.ok(firstOfApril.classList.contains('disabled'),        'The 1st of April is disbaled');
            assert.ok(forthOfApril.classList.contains('disabled'),        'The 4th of April is disbaled');
            assert.ok( ! fifthOfApril.classList.contains('disabled'),     'The 5th of April is NOT disbaled');
            assert.ok( ! seventhOfApril.classList.contains('disabled'),   'The r75th of April is NOT disbaled');
            assert.ok( ! teenthOfApril.classList.contains('disabled'),    'The 10th of April is NOT disbaled');
            assert.ok(eleventhOfApril.classList.contains('disabled'),     'The 11th of April is disbaled');
            assert.ok(twentysecondOfApril.classList.contains('disabled'), 'The 22nd of April is disbaled');

            done();
        });
    });

    QUnit.test('Update constraints', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(6);

        dateTimePicker(container, {
            setup : 'date',
            format : 'YYYY-MM-DD'
        })
        .on('ready', function(){
            this.open();
        })
        .on('open', function(){
            var element = this.getElement()[0];
            var picker  = element.querySelector('.flatpickr-calendar');
            var month   = picker.querySelector('.flatpickr-current-month .cur-month');
            var year    = picker.querySelector('.flatpickr-current-month .cur-year');

            assert.ok(picker.classList.contains('open'), 'The picker is now open');
            assert.equal(month.textContent.trim(), 'April', 'The selected month is correct');
            assert.equal(year.value.trim(), '2019', 'The selected year is correct');

            assert.equal(picker.querySelectorAll('.flatpickr-day.disabled').length, 0, 'All dates can be selected');

            this.updateConstraints('minDate', '2019-04-12');
            this.updateConstraints('maxDate', '2019-04-13');

            assert.ok(picker.querySelectorAll('.flatpickr-day.disabled').length > 0, 'Some Dates are now disabled');
            assert.equal(picker.querySelectorAll('.flatpickr-day:not(.disabled)').length, 2, 'Ony 3 dates can be seleted');

            done();
        });
    });

    QUnit.test('Get selected dates', function(assert) {
        var container = document.querySelector('#qunit-fixture > div');
        var done      = assert.async();

        assert.expect(10);

        dateTimePicker(container, {
            setup : 'date',
            format : 'YYYY-MM-DD'
        })
        .on('ready', function(){
            assert.equal(this.getSelectedDates(), false, 'No date is currently selected');

            this.setValue('2019-05-01');
            this.open();
        })
        .on('open', function(){
            var element = this.getElement()[0];
            var selection;
            var picker  = element.querySelector('.flatpickr-calendar');
            var month   = picker.querySelector('.flatpickr-current-month .cur-month');
            var year    = picker.querySelector('.flatpickr-current-month .cur-year');
            var day     = picker.querySelector('.flatpickr-day.selected');

            assert.ok(picker.classList.contains('open'), 'The picker is now open');
            assert.equal(month.textContent.trim(), 'May', 'The selected month is correct');
            assert.equal(year.value.trim(), '2019', 'The selected year is correct');
            assert.equal(day.textContent.trim(), '1', 'The selected day is correct');

            selection = this.getSelectedDates();
            assert.equal(selection.length, 1, 'The selection contains one date');
            assert.ok(selection[0] instanceof Date, 'The selection contains a Date');
            assert.equal(selection[0].getFullYear(), 2019, 'The selection year is correct');
            assert.equal(selection[0].getMonth(), 4, 'The selection month (index) is correct');
            assert.equal(selection[0].getDate(), 1, 'The selection day is correct');

            done();
        });
    });


    QUnit.module('Visual');

    QUnit.test('date range', function(assert) {
        var done = assert.async();
        assert.expect(1);

        dateTimePicker(document.querySelector('#visual .date-range'), {
            setup : 'date-range'
        })
        .on('render', function(){
            assert.ok(true);
            done();
        });
    });

    QUnit.test('date time range', function(assert) {
        var done = assert.async();
        assert.expect(1);

        dateTimePicker(document.querySelector('#visual .datetime-range'), {
            setup : 'datetime-range'
        })
        .on('render', function(){
            assert.ok(true);
            done();
        });
    });

    QUnit.test('date', function(assert) {
        var done = assert.async();
        assert.expect(1);

        dateTimePicker(document.querySelector('#visual .date'), {
            setup : 'date'
        })
        .on('render', function(){
            assert.ok(true);
            done();
        });
    });
    QUnit.test('time', function(assert) {
        var done = assert.async();
        assert.expect(1);

        dateTimePicker(document.querySelector('#visual .time'), {
            setup : 'time'
        })
        .on('render', function(){
            assert.ok(true);
            done();
        });
    });
    QUnit.test('datetime', function(assert) {
        var done = assert.async();
        assert.expect(1);

        dateTimePicker('#visual .datetime', {
            setup : 'datetime'
        })
        .on('render', function(){
            assert.ok(true);
            done();
        });
    });
    QUnit.test('trigger', function(assert) {
        var done = assert.async();
        assert.expect(1);

        dateTimePicker(document.querySelector('#visual .date-controls'), {
            setup : 'date',
            controlButtons : true
        })
        .on('render', function(){
            assert.ok(true);
            done();
        });
    });

    QUnit.test('manual', function(assert) {
        var form                 = document.querySelector('#visual form');
        var destinationContainer = document.querySelector('#visual .destination');
        var currentValue         = document.querySelector('#visual .value');

        assert.expect(1);

        form.addEventListener('submit', function(e){
            var formData;
            e.preventDefault();

            formData = new FormData(form);

            destinationContainer.innerHTML = '';
            currentValue.textContent = '';

            dateTimePicker(destinationContainer, {
                setup :              formData.get('setup'),
                controlButtons :     formData.has('control-buttons'),
                locale :             formData.get('locale'),
                useLocalizedFormat : !!formData.get('locale')
            })
            .on('change', function(value){
                currentValue.textContent = value;
            });
            return false;
        });

        assert.ok(true);
    });

});
