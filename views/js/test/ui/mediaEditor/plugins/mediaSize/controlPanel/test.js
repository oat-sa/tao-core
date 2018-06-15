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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksander Zagovorychev <zagovorichev@gmail.com>
 */

define([
    'jquery',
    'lodash',
    'ui/mediaEditor/plugins/mediaSize/controlPanelComponent',
    'nouislider'
], function ($, _, controlPanelComponent) {
    'use strict';

    var workingConfiguration = {
        showResponsiveToggle: true,
        sizeProps: {
            px: {
                natural: {
                    width: 100,
                    height: 100
                },
                current: {
                    width: 100,
                    height: 100
                }
            },
            '%': {
                natural: {
                    width: 100,
                    height: null
                },
                current: {
                    width: 100,
                    height: null
                }
            },
            ratio: {
                natural: 1,
                current: 1
            },
            currentUtil: '%',
            containerWidth: 700,
            sliders: {
                '%': {
                    min: 0,
                    max: 100,
                    start: 100
                },
                px: {
                    min: 0,
                    max: 100,
                    start: 100
                }
            }
        }
    };

    var keyboardKeyCodes = {
        /**
         * The most common keycodes defined by :
         * @type {Object.}
         * @const
         */
        KEYMAP : {
            STRG: 17,
            CTRL: 17,
            CTRLRIGHT: 18,
            CTRLR: 18,
            SHIFT: 16,
            RETURN: 13,
            ENTER: 13,
            BACKSPACE: 8,
            BCKSP:8,
            ALT: 18,
            ALTR: 17,
            ALTRIGHT: 17,
            SPACE: 32,
            WIN: 91,
            MAC: 91,
            FN: null,
            UP: 38,
            DOWN: 40,
            LEFT: 37,
            RIGHT: 39,
            ESC: 27,
            DEL: 46,
            F1: 112,
            F2: 113,
            F3: 114,
            F4: 115,
            F5: 116,
            F6: 117,
            F7: 118,
            F8: 119,
            F9: 120,
            F10: 121,
            F11: 122,
            F12: 123
        },
        /**
         * @type {Object.}
         * @const
         */
        KEYCODES : {
            'backspace' : '8',
            'tab' : '9',
            'enter' : '13',
            'shift' : '16',
            'ctrl' : '17',
            'alt' : '18',
            'pause_break' : '19',
            'caps_lock' : '20',
            'escape' : '27',
            'page_up' : '33',
            'page down' : '34',
            'end' : '35',
            'home' : '36',
            'left_arrow' : '37',
            'up_arrow' : '38',
            'right_arrow' : '39',
            'down_arrow' : '40',
            'insert' : '45',
            'delete' : '46',
            '0' : '48',
            '1' : '49',
            '2' : '50',
            '3' : '51',
            '4' : '52',
            '5' : '53',
            '6' : '54',
            '7' : '55',
            '8' : '56',
            '9' : '57',
            'a' : '65',
            'b' : '66',
            'c' : '67',
            'd' : '68',
            'e' : '69',
            'f' : '70',
            'g' : '71',
            'h' : '72',
            'i' : '73',
            'j' : '74',
            'k' : '75',
            'l' : '76',
            'm' : '77',
            'n' : '78',
            'o' : '79',
            'p' : '80',
            'q' : '81',
            'r' : '82',
            's' : '83',
            't' : '84',
            'u' : '85',
            'v' : '86',
            'w' : '87',
            'x' : '88',
            'y' : '89',
            'z' : '90',
            'left_window key' : '91',
            'right_window key' : '92',
            'select_key' : '93',
            'numpad 0' : '96',
            'numpad 1' : '97',
            'numpad 2' : '98',
            'numpad 3' : '99',
            'numpad 4' : '100',
            'numpad 5' : '101',
            'numpad 6' : '102',
            'numpad 7' : '103',
            'numpad 8' : '104',
            'numpad 9' : '105',
            'multiply' : '106',
            'add' : '107',
            'subtract' : '109',
            '.' : '110',
            'divide' : '111',
            'f1' : '112',
            'f2' : '113',
            'f3' : '114',
            'f4' : '115',
            'f5' : '116',
            'f6' : '117',
            'f7' : '118',
            'f8' : '119',
            'f9' : '120',
            'f10' : '121',
            'f11' : '122',
            'f12' : '123',
            'num_lock' : '144',
            'scroll_lock' : '145',
            'semi_colon' : '186',
            'equal_sign' : '187',
            ',' : '188',
            'dash' : '189',
            'num_pad.' : '190',
            'forward_slash' : '191',
            'grave_accent' : '192',
            'open_bracket' : '219',
            'backslash' : '220',
            'closebracket' : '221',
            'single_quote' : '222'
        }
    };

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        QUnit.expect(3);

        assert.ok(typeof controlPanelComponent === 'function', 'the module exposes a function');
        assert.ok(typeof controlPanelComponent({sizeProps: 'fake'}) === 'object', 'the factory creates an object');
        assert.notEqual(controlPanelComponent({sizeProps: 'fake'}), controlPanelComponent({sizeProps: 'fake'}), 'the factory creates new objects');
    });

    QUnit.test('component', function (assert) {
        var component;

        QUnit.expect(2);

        component = controlPanelComponent({sizeProps: 'fake'});

        assert.ok(typeof component.render === 'function', 'the component has a render method');
        assert.ok(typeof component.destroy === 'function', 'the component has a destroy method');
    });

    QUnit.test('eventifier', function (assert) {
        var component;

        QUnit.expect(3);

        component = controlPanelComponent({sizeProps: 'fake'});

        assert.ok(typeof component.on === 'function', 'the component has a on method');
        assert.ok(typeof component.off === 'function', 'the component has a off method');
        assert.ok(typeof component.trigger === 'function', 'the component has a trigger method');
    });

    QUnit.module('Component');

    QUnit.test('Required properties', function (assert) {
        QUnit.expect(1);
        assert.throws(
            function () { controlPanelComponent({}) },
            function( err ) {
                return err.toString() === 'Error: Control panel of the media editor is required sizeProps parameter';
            },
            'Throws an error on the initialization without required parameters'
        );
    });

    QUnit.asyncTest('On showResponsiveToggle property', function (assert) {
        var $container, conf;
        QUnit.expect(1);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = true;
        controlPanelComponent(conf)
            .on('render', function () {
                assert.equal($('.media-sizer', $container).hasClass('media-sizer-responsivetoggle-off'), false, 'Media sizer has not a class to hide the responsive toggle');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Off showResponsiveToggle property', function (assert) {
        var $container, conf;
        QUnit.expect(1);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = false;
        controlPanelComponent(conf)
            .on('render', function () {
                assert.ok($('.media-sizer', $container).hasClass('media-sizer-responsivetoggle-off'), 'Media sizer has a class to hide the responsive toggle');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Parameter currentUtil set to percent', function (assert) {
        var $container, conf;
        QUnit.expect(2);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = false;
        conf.sizeProps.currentUtil = '%';
        controlPanelComponent(conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $container);
                var $pixelBlock = $('.media-sizer-pixel', $container);
                assert.ok($percentBlock.css('display') === 'block', 'Block responsive is visible');
                assert.ok($pixelBlock.css('display') === 'none', 'Block pixel is hidden');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Parameter currentUtil set to pixel', function (assert) {
        var $container, conf;
        QUnit.expect(2);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = false;
        conf.sizeProps.currentUtil = 'px';
        controlPanelComponent(conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $container);
                var $pixelBlock = $('.media-sizer-pixel', $container);
                assert.ok($percentBlock.css('display') === 'none', 'Block responsive is hidden');
                assert.ok($pixelBlock.css('display') === 'block', 'Block pixel is visible');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Responsive slider mode [percent]', function (assert) {
        var $container, conf;
        QUnit.expect(9);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = false;
        conf.currentUtil = '%';
        controlPanelComponent(conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $container);
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);
                var $sliderEl = $('.media-sizer-slider', $percentBlock);
                var press = jQuery.Event("keyup", { keyCode: 51 });

                assert.equal($percentInput.val(), 100, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '100%', 'Slider has been set to 100%');

                // percent changed in input to change the slider position as a result
                $percentInput.val(3);
                $percentInput.trigger(press);
                assert.equal($percentInput.val(), 3, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '3%', 'Slider has been set to 100%');

                // and now if change slider value it will change value of the input
                conf.$sliders['%'].val(37);
                assert.equal($sliderPosEl.prop('style').left, '37%', 'Slider has been set to 37%');
                assert.equal($percentInput.val(), 3, 'Input was not updated and still equals 3');
                conf.$sliders['%'].trigger('slide');
                assert.equal($percentInput.val(), 37, 'Input was updated to 37');

                $sliderEl.val(12);
                assert.equal($sliderPosEl.prop('style').left, '12%', 'Slider has been set to 12%');
                $sliderPosEl.trigger('slide');
                assert.equal($percentInput.val(), 12, 'Input was updated to 12');

                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Allowed symbols in the input fields', function (assert) {
        var $container, conf;
        QUnit.expect(52);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = false;
        conf.currentUtil = '%';
        controlPanelComponent(conf)
            .on('render', function () {

                var checkInput = function checkInput (unit, dim, checker) {
                    var input = conf.$fields[unit][dim];
                    var keyup, keydown, code;
                    var i;
                    checker.value = '' + checker.value;
                    input.val('');
                    for (i = 0; i < checker.value.length; i++) {
                        // get charcode doesn't work because of numpad
                        code = keyboardKeyCodes['KEYCODES'].hasOwnProperty(checker.value[i]) ? parseInt(keyboardKeyCodes['KEYCODES'][checker.value[i]]) : 0;
                        keydown = jQuery.Event("keydown", { keyCode: code });
                        input.trigger(keydown);
                        if ( !keydown.isDefaultPrevented() ) {
                            input.val(input.val() + checker.value[i]);
                        }
                        keyup = jQuery.Event("keyup", { keyCode: code });
                        input.trigger(keyup);
                    }
                    assert.equal(input.val(), checker.expected, '[' + unit + '][' + dim + '] The value "' + checker.value + '" transformed to ' + checker.expected);
                };

                var units = ['px', '%'];
                var dims = ['height', 'width'];

                var valuesToCheck = [{
                    value: '%',
                    expected: 0
                }, {
                    value: '1',
                    expected: 1
                }, {
                    value: 'a',
                    expected: 0
                }, {
                    value: '!',
                    expected: 0
                }, {
                    value: '!@#$%^&*()_+0-',
                    expected: '0'
                }, {
                    value: 99.99,
                    expected: 99.99
                }, {
                    value: 0,
                    expected: 0
                }, {
                    value: '',
                    expected: 0
                }, {
                    value: 'asdf',
                    expected: 0
                }, {
                    value: 100,
                    expected: 100
                }, {
                    value: '1.01',
                    expected: 1.01
                }, {
                    value: 1.1111,
                    expected: 1.1111
                }, {
                    value: '2333',
                    expected: 100
                }];

                _.forEach(valuesToCheck, function(val) {
                    _.forEach(units, function (unit) {
                        _.forEach(dims, function (dim) {
                            checkInput(unit, dim, val);
                        })
                    });
                });

                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Pixels mode', function (assert) {
        var $container, conf;
        // QUnit.expect(9);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        conf.showResponsiveToggle = false;
        conf.currentUtil = 'px';
        controlPanelComponent(conf)
            .on('render', function () {
                var $pixelBlock = $('.media-sizer-pixel', $container);
                var $editorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $editorContainer);
                var $heightInput = $('input[name=height]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $pixelBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);
                var $sliderEl = $('.media-sizer-slider', $sliderBox);
                var press = jQuery.Event("keyup", { keyCode: 51 });

                assert.equal($widthInput.val(), 100, 'Width value is set to 100');
                assert.equal($sliderPosEl.prop('style').left, '100%', 'Slider has been set to 100%');

                // slider value change will change value of the inputs
                conf.$sliders['px'].val(37);
                assert.equal($sliderPosEl.prop('style').left, '37%', 'Slider has been set to 37%');
                assert.equal($widthInput.val(), 100, 'Width input was not updated and still equals 100');
                assert.equal($heightInput.val(), 100, 'Height input was not updated and still equals 100');
                conf.$sliders['px'].trigger('slide');
                assert.equal($widthInput.val(), 37, 'Width input was updated to 37');
                assert.equal($heightInput.val(), 37, 'Height input was updated to 37');

                // change percent in input to change the slider position as a result
                /*$percentInput.val(3);
                $percentInput.trigger(press);
                assert.equal($percentInput.val(), 3, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '3%', 'Slider has been set to 100%');*/

                /*$sliderEl.val(12);
                assert.equal($sliderPosEl.prop('style').left, '12%', 'Slider has been set to 12%');
                $sliderPosEl.trigger('slide');
                assert.equal($percentInput.val(), 12, 'Input was updated to 12');*/

                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('Sync method', function (assert) {
        var $container, conf;
        // QUnit.expect(9);

        $container = $('#qunit-fixture');
        conf = _.cloneDeep(workingConfiguration);
        controlPanelComponent(conf)
            .on('render', function () {


                QUnit.start();
            })
            .render($container);
    });

    QUnit.module('Demo');

    QUnit.test('preview components workflow', function (assert) {
        var $container, conf;
        var $demoContainer = $('<div class="demo-container" style="position: relative; max-width: 185px">');

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');
        $demoContainer.insertAfter($container);
        conf = _.cloneDeep(workingConfiguration);
        controlPanelComponent(conf)
            .on('render', function () {
                // check that control panel initialized and works
                // check that image is shown and manageable
            })
            .render($demoContainer);
    });
});
