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
 */

define([
    'jquery',
    'lodash',
    'ui/mediaEditor/plugins/mediaDimension/mediaDimensionComponent'
], function ($, _, mediaDimensionComponent) {
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
            slider: {
                min: 0,
                max: 100,
                start: 100
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

    var getFields = function getFields($container) {
        var $fields = {
            '%': {width: null},
            px: {
                width: null,
                height: null
            }
        };
        var $percentBlock = $('.media-sizer-percent', $container);
        var $percentEditorContainer = $('.item-editor-unit-input-box', $percentBlock);
        var $pxBlock = $('.media-sizer-pixel', $container);
        var $pxEditorContainer = $('.item-editor-unit-input-box', $pxBlock);
        $fields['%'].width = $('input[name=width]', $percentEditorContainer);
        $fields.px.width = $('input[name=width]', $pxEditorContainer);
        $fields.px.height = $('input[name=height]', $pxEditorContainer);
        return $fields;
    };

    var conf, $controlContainer, media, $editableContainer;

    var beforeEach = function beforeEach() {
        var $visibleContainer = $('.visible-test');
        var $container = $('#qunit-fixture');
        var $img = $('<img src="js/test/ui/mediaEditor/sample/space.jpg" style="display: none">');
        $editableContainer = $('<div class="editable-container" style="width: 900px">');
        $controlContainer = $('<div class="control-container" style="position: relative; max-width: 185px">');
        conf = _.cloneDeep(workingConfiguration);
        if ($visibleContainer.length) {
            $visibleContainer.html('');
        } else {
            $visibleContainer = $('<div class="visible-test">');
            $visibleContainer.insertAfter($container);
        }
        $visibleContainer.append($editableContainer);
        $editableContainer.append($controlContainer);
        $editableContainer.append($img);
        media = {
            $node: $img,
            type: 'image/jpeg',
            src: $img.attr('src'),
            width: 500,
            height: 735
        };
    };

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        QUnit.expect(3);
        beforeEach();

        assert.ok(typeof mediaDimensionComponent === 'function', 'the module exposes a function');
        assert.ok(typeof mediaDimensionComponent($controlContainer, media, conf) === 'object', 'the factory creates an object');
        assert.notEqual(mediaDimensionComponent($controlContainer, media, conf), mediaDimensionComponent($controlContainer, media, conf), 'the factory creates new objects');
    });

    QUnit.test('component', function (assert) {
        var component;
        beforeEach();
        QUnit.expect(2);

        component = mediaDimensionComponent($controlContainer, media, conf);

        assert.ok(typeof component.render === 'function', 'the component has a render method');
        assert.ok(typeof component.destroy === 'function', 'the component has a destroy method');
    });

    QUnit.test('eventifier', function (assert) {
        var component;
        beforeEach();
        QUnit.expect(3);

        component = mediaDimensionComponent($controlContainer, media, conf);

        assert.ok(typeof component.on === 'function', 'the component has a on method');
        assert.ok(typeof component.off === 'function', 'the component has a off method');
        assert.ok(typeof component.trigger === 'function', 'the component has a trigger method');
    });

    QUnit.module('Component');

    QUnit.asyncTest('On showResponsiveToggle property', function (assert) {
        QUnit.expect(1);
        beforeEach();
        conf.showResponsiveToggle = true;
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                assert.equal($('.media-sizer', $controlContainer).hasClass('media-sizer-responsivetoggle-off'), false,
                    'Media sizer does not have a class to hide the responsive toggle');
                QUnit.start();
            });
    });

    QUnit.asyncTest('Off showResponsiveToggle property', function (assert) {
        QUnit.expect(1);
        beforeEach();
        conf.showResponsiveToggle = false;
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                assert.ok($('.media-sizer', $controlContainer).hasClass('media-sizer-responsivetoggle-off'), 'Media sizer has a class to hide the responsive toggle');
                QUnit.start();
            });
    });

    QUnit.asyncTest('Parameter currentUtil set to percent', function (assert) {
        QUnit.expect(2);
        beforeEach();
        conf.showResponsiveToggle = false;
        conf.sizeProps.currentUtil = '%';
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $controlContainer);
                var $pixelBlock = $('.media-sizer-pixel', $controlContainer);
                assert.ok($percentBlock.css('display') === 'block', 'Block responsive is visible');
                assert.ok($pixelBlock.css('display') === 'none', 'Block pixel is hidden');
                QUnit.start();
            });
    });

    QUnit.asyncTest('Parameter currentUtil set to pixel', function (assert) {
        QUnit.expect(2);
        beforeEach();
        conf.showResponsiveToggle = false;
        conf.sizeProps.currentUtil = 'px';
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $controlContainer);
                var $pixelBlock = $('.media-sizer-pixel', $controlContainer);
                assert.ok($percentBlock.css('display') === 'none', 'Block responsive is hidden');
                assert.ok($pixelBlock.css('display') === 'block', 'Block pixel is visible');
                QUnit.start();
            });
    });

    QUnit.asyncTest('Picture bigger than container', function (assert) {
        QUnit.expect(5);
        beforeEach();
        $editableContainer.width(200);
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $controlContainer);
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);

                var $pixelBlock = $('.media-sizer-pixel', $controlContainer);
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $pxEditorContainer);
                var $heightInput = $('input[name=height]', $pxEditorContainer);

                assert.equal($percentInput.val(), 100, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '100%', 'Slider has been set to 100%');
                assert.equal($percentInput.val(), 100, 'Width value is set to 3%');
                assert.equal($widthInput.val(), 200, 'Width value is set to 3');
                assert.equal($heightInput.val(), 294, 'Height value is set to 3');

                QUnit.start();
            });
    });

    QUnit.asyncTest('Picture smaller than container', function (assert) {
        QUnit.expect(5);
        beforeEach();
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $controlContainer);
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);

                var $pixelBlock = $('.media-sizer-pixel', $controlContainer);
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $pxEditorContainer);
                var $heightInput = $('input[name=height]', $pxEditorContainer);

                assert.equal($percentInput.val(), 56, 'Width value is set to 56 percent');
                assert.equal($sliderPosEl.prop('style').left, '55.5556%', 'Slider has been set to 55.5556%');
                assert.equal($percentInput.val(), 56, 'Width value is set to 56%');
                assert.equal($widthInput.val(), 500, 'Width value is set to 500');
                assert.equal($heightInput.val(), 735, 'Height value is set to 735');

                QUnit.start();
            });
    });

    QUnit.asyncTest('Responsive slider mode [percent]', function (assert) {
        QUnit.expect(9);
        beforeEach();
        conf.showResponsiveToggle = false;
        conf.sizeProps.currentUtil = '%';
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', $controlContainer);
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);
                var $sliderEl = $('.media-sizer-slider', $percentBlock);

                // init with 100
                assert.equal($percentInput.val(), 56, 'Width value is set to 56 percent');
                assert.equal($sliderPosEl.prop('style').left, '55.5556%', 'Slider has been set to 55.5556%');

                // percent changed in input to change the slider position as a result
                $percentInput.val(3);
                $percentInput.trigger('keyup');

                assert.equal($percentInput.val(), 3, 'Width value is set to 3%');
                assert.equal($sliderPosEl.prop('style').left, '3%', 'Slider has been set to 3%');

                // and now if change slider value it will change value of the input
                $sliderEl.val(37);
                assert.equal($sliderPosEl.prop('style').left, '37%', 'Slider has been set to 37%');
                assert.equal($percentInput.val(), 3, 'Input was not updated and still equals 3');
                $sliderEl.trigger('slide');
                assert.equal($percentInput.val(), 37, 'Input was updated to 37');

                $sliderEl.val(12);
                assert.equal($sliderPosEl.prop('style').left, '12%', 'Slider has been set to 12%');
                $sliderPosEl.trigger('slide');
                assert.equal($percentInput.val(), 12, 'Input was updated to 12');

                QUnit.start();
            });
    });

    QUnit.asyncTest('Allowed symbols in the input fields', function (assert) {
        QUnit.expect(21);
        beforeEach();
        conf.showResponsiveToggle = false;
        conf.currentUtil = '%';
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $fields = getFields($controlContainer);

                var checkInput = function checkInput (unit, dim, checker) {
                    var input = $fields[unit][dim];
                    var keyup, keydown, code;
                    var i;
                    checker.value = '' + checker.value;
                    input.val('');
                    for (i = 0; i < checker.value.length; i++) {
                        // get charcode doesn't work because of numpad keys (charcode returns ascii)
                        code = keyboardKeyCodes['KEYCODES'].hasOwnProperty(checker.value[i]) ? parseInt(keyboardKeyCodes['KEYCODES'][checker.value[i]]) : 0;
                        keydown = $.Event("keydown", { keyCode: code });
                        input.trigger(keydown);
                        if ( !keydown.isDefaultPrevented() ) {
                            input.val(input.val() + checker.value[i]);
                        }
                        keyup = $.Event("keyup", { keyCode: code });
                        input.trigger(keyup);
                    }
                    assert.equal(input.val(), checker.expected, '[' + unit + '][' + dim + '] The value "' + checker.value + '" transformed to ' + checker.expected);
                };

                var units = ['px', '%'];
                var dims = ['height', 'width'];

                var valuesToCheck = [{
                    value: '1',
                    expected: 1
                }, {
                    value: 99.9, // 99.9 = 100 and 9 at the end
                    expected: 100
                }, {
                    value: 100,
                    expected: 100
                }, {
                    value: '1.01',
                    expected: 1
                }];

                _.forEach(valuesToCheck, function(val) {
                    _.forEach(units, function (unit) {
                        _.forEach(dims, function (dim) {
                            if (unit === '%' && dim === 'height') {
                                return;
                            }
                            checkInput(unit, dim, val);
                        });
                    });
                });

                checkInput('%', 'width', {value: '1234.0000000000', expected: 100});
                checkInput('px', 'width', {value: '1234.0000000000', expected: 1234});
                checkInput('px', 'height', {value: '1234.0000000000', expected: 1234});
                checkInput('%', 'width', {value: '2333', expected: 100});
                checkInput('px', 'height', {value: '2333', expected: 2333});
                checkInput('px', 'width', {value: '2333', expected: 2333});
                // value 1.1111 => 1. will be 1, then 1111
                checkInput('%', 'width', {value: 1.1111, expected: 100});
                checkInput('px', 'width', {value: 1.1111, expected: 1111});
                checkInput('px', 'height', {value: 1.1111, expected: 1111});

                QUnit.start();
            });
    });

    QUnit.asyncTest('Pixels mode', function (assert) {
        QUnit.expect(3);
        beforeEach();
        conf.showResponsiveToggle = false;
        conf.sizeProps.currentUtil = 'px';
        conf.syncDimensions = true;
        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $pixelBlock = $('.media-sizer-pixel', $controlContainer);
                var $editorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $editorContainer);
                var $heightInput = $('input[name=height]', $editorContainer);

                assert.equal($widthInput.val(), 500, 'Width value is set to 100');

                // change width in input to change the slider position as a result (and height input value)
                $widthInput.val(3).trigger('keyup');
                assert.equal($heightInput.val(), 4, 'Height value is set to 3');

                // same with height
                $heightInput.val(5);
                $heightInput.trigger('keyup');
                assert.equal($widthInput.val(), 3, 'Height value is set to 3');

                QUnit.start();
            });
    });

    QUnit.asyncTest('Workflow', function (assert) {
        QUnit.expect(17);
        beforeEach();
        conf.showResponsiveToggle = true;
        conf.sizeProps.currentUtil = 'px';
        conf.syncDimensions = false;

        mediaDimensionComponent($controlContainer, media, conf)
            .on('render', function () {
                var $pixelBlock = $('.media-sizer-pixel', $controlContainer);
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $pxEditorContainer);
                var $heightInput = $('input[name=height]', $pxEditorContainer);

                var $percentBlock = $('.media-sizer-percent', $controlContainer);
                var $pcEditorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $pcInput = $('input[name=width]', $pcEditorContainer);
                var $pcSliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $pcSliderPosEl = $('.noUi-origin', $pcSliderBox);
                var $pcSliderEl = $('.media-sizer-slider', $percentBlock);

                assert.ok(true, 'component created without synchronisation in the px mode [100x100 & 100% & ratio=1]');
                assert.equal($widthInput.val(), 500, 'Width = 500');
                assert.equal($heightInput.val(), 735, 'Height = 735');
                assert.equal($pcInput.val(), 56, 'Percent input = 56');
                assert.equal($pcSliderPosEl.prop('style').left, '55.5556%', 'Slider pc = 55.5556%');

                assert.ok(true, 'change width to 27 [27x100 & 100% & ratio=27/100=0.27]');
                $widthInput.val(27);
                $widthInput.trigger('keyup'); // to apply changes in State
                assert.equal($heightInput.val(), 735, 'Height = 735');
                assert.equal($pcInput.val(), 3, 'Percent input = 3');
                assert.equal($pcSliderPosEl.prop('style').left, '3%', 'Slider pc = 3%');

                assert.ok(true, 'change height to 13.5 [13.5x27 & 100% & ratio=13.5/27=0.5]');
                $heightInput.val(14).trigger('keyup');
                assert.equal($widthInput.val(), 27, 'Width = 27');
                assert.equal($pcInput.val(), 3, 'Percent input = 3');
                assert.equal($pcSliderPosEl.prop('style').left, '3%', 'Slider pc = 3%');

                assert.ok(true, 'change percent by PC slider to 20% [100x100 & 20% & ratio=1]');
                $pcSliderEl.val(20).trigger('slide');
                assert.equal($pcSliderPosEl.prop('style').left, '20%', 'Slider pc = 20%');
                assert.equal($widthInput.val(), 180, 'Width = 180');
                assert.equal($heightInput.val(), 265, 'Height = 265');

                QUnit.start();
            });
    });

    QUnit.module('Demo');

    QUnit.test('Preview components workflow', function () {
        beforeEach();
        conf.showResponsiveToggle = true;
        conf.sizeProps.currentUtil = '%';
        conf.syncDimensions = true;
        mediaDimensionComponent($controlContainer, media, conf);
        QUnit.ok(true);
    });
});
