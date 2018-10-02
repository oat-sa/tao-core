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

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };

        QUnit.expect(3);

        assert.ok(typeof mediaDimensionComponent === 'function', 'the module exposes a function');
        assert.ok(typeof mediaDimensionComponent($toolsContainer, media, conf).on('render', function(){this.destroy();}) === 'object', 'the factory creates an object');
        assert.notEqual(mediaDimensionComponent($toolsContainer, media, conf).on('render', function(){this.destroy();}), mediaDimensionComponent($toolsContainer, media, conf).on('render', function(){this.destroy();}), 'the factory creates new objects');
        $visibleContainer.remove();
    });

    QUnit.test('component', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            component,
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(2);

        component = mediaDimensionComponent($toolsContainer, media, conf).on('render', function(){this.destroy();});

        assert.ok(typeof component.render === 'function', 'the component has a render method');
        assert.ok(typeof component.destroy === 'function', 'the component has a destroy method');
        $visibleContainer.remove();
    });

    QUnit.test('eventifier', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            component,
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(3);

        component = mediaDimensionComponent($toolsContainer, media, conf).on('render', function(){this.destroy();});

        assert.ok(typeof component.on === 'function', 'the component has a on method');
        assert.ok(typeof component.off === 'function', 'the component has a off method');
        assert.ok(typeof component.trigger === 'function', 'the component has a trigger method');
        $visibleContainer.remove();
    });

    QUnit.module('Component');

    QUnit.asyncTest('On showResponsiveToggle property', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(1);
        conf.showResponsiveToggle = true;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                assert.equal($('.media-sizer', this.getContainer()).hasClass('media-sizer-responsivetoggle-off'), false,
                    'Media sizer does not have a class to hide the responsive toggle');
                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Off showResponsiveToggle property', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(1);
        conf.showResponsiveToggle = false;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                assert.ok($('.media-sizer', this.getContainer()).hasClass('media-sizer-responsivetoggle-off'), 'Media sizer has a class to hide the responsive toggle');
                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Parameter currentUtil set to percent', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(2);
        conf.showResponsiveToggle = false;
        conf.responsive = true;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $pixelBlock = $('.media-sizer-pixel', this.getContainer());
                assert.ok($percentBlock.css('display') === 'block', 'Block responsive is visible');
                assert.ok($pixelBlock.css('display') === 'none', 'Block pixel is hidden');
                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Parameter currentUtil set to pixel', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };

        QUnit.expect(2);
        conf.responsive = false;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $pixelBlock = $('.media-sizer-pixel', this.getContainer());

                assert.ok($percentBlock.css('display') === 'none', 'Block responsive is hidden');
                assert.ok($pixelBlock.css('display') === 'block', 'Block pixel is visible');
                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Picture is bigger than the container', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };

        QUnit.expect(5);

        $controlContainer.width(200);
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);

                var $pixelBlock = $('.media-sizer-pixel', this.getContainer());
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $pxEditorContainer);
                var $heightInput = $('input[name=height]', $pxEditorContainer);

                assert.equal($percentInput.val(), 100, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '100%', 'Slider has been set to 100%');
                assert.equal($percentInput.val(), 100, 'Width value is set to 100%');
                assert.equal($widthInput.val(), 200, 'Width value is set to 200');
                assert.equal($heightInput.val(), 294, 'Height value is set to 294');
                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Picture smaller than container', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };

        QUnit.expect(5);

        conf.responsive = false;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);

                var $pixelBlock = $('.media-sizer-pixel', this.getContainer());
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $pxEditorContainer);
                var $heightInput = $('input[name=height]', $pxEditorContainer);

                assert.equal($percentInput.val(), 71, 'Width value is set to 71 percent');
                assert.equal($sliderPosEl.prop('style').left, '71.429%', 'Slider has been set to 55.5556%');
                assert.equal($percentInput.val(), 71, 'Width value is set to 71%');
                assert.equal($widthInput.val(), 500, 'Width value is set to 500');
                assert.equal($heightInput.val(), 735, 'Height value is set to 735');
                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Responsive slider mode [percent]', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(9);
        conf.showResponsiveToggle = false;
        conf.responsive = false;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $editorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $percentInput = $('input[name=width]', $editorContainer);
                var $sliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $sliderPosEl = $('.noUi-origin', $sliderBox);
                var $sliderEl = $('.media-sizer-slider', $percentBlock);

                assert.equal($percentInput.val(), 71, 'Width value is set to 71 percent');
                assert.equal($sliderPosEl.prop('style').left, '71.429%', 'Slider has been set to 71.429%');

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

                this.destroy();
                $visibleContainer.remove();

                QUnit.start();
            });
    });

    QUnit.cases([
        {unit: '%', dim: 'width', value: '1', expected: 1},
        {unit: 'px', dim: 'width', value: '1', expected: 1},
        {unit: 'px', dim: 'height', value: '1', expected: 1},
        // 99.9 = 100 and 9 at the end
        {unit: '%', dim: 'width', value: 99.9, expected: 100},
        {unit: 'px', dim: 'width', value: 99.9, expected: 100},
        {unit: 'px', dim: 'height', value: 99.9, expected: 100},
        {unit: '%', dim: 'width', value: '1.01', expected: 1},
        {unit: 'px', dim: 'width', value: '1.01', expected: 1},
        {unit: 'px', dim: 'height', value: '1.01', expected: 1},
        {unit: '%', dim: 'width', value: 100, expected: 100},
        {unit: 'px', dim: 'width', value: 100, expected: 100},
        {unit: 'px', dim: 'height', value: 100, expected: 100},
        // % can't be 0
        {unit: '%', dim: 'width', value: 0, expected: 1},
        // px can be 0
        {unit: 'px', dim: 'width', value: 0, expected: 0},
        {unit: 'px', dim: 'height', value: 0, expected: 0},
        {unit: '%', dim: 'width', value: '1234.0000000000', expected: 100},
        {unit: 'px', dim: 'width', value: '1234.0000000000', expected: 1234},
        {unit: 'px', dim: 'height', value: '1234.0000000000', expected: 1234},
        {unit: '%', dim: 'width', value: '2333', expected: 100},
        {unit: 'px', dim: 'width', value: '2333', expected: 2333},
        {unit: 'px', dim: 'height', value: '2333', expected: 2333},
        // value 1.1111 => 1. will be 1, then 1111
        {unit: '%', dim: 'width', value: 1.1111, expected: 100},
        {unit: 'px', dim: 'width', value: 1.1111, expected: 1111},
        {unit: 'px', dim: 'height', value: 1.1111, expected: 1111},
    ]).asyncTest('Allowed symbols in the input fields', function (params, assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };

        QUnit.expect(1);
        conf.responsive = params.unit === '%';
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $fields = {
                    '%': {width: null},
                    px: {
                        width: null,
                        height: null
                    }
                };
                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $percentEditorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $pxBlock = $('.media-sizer-pixel', this.getContainer());
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pxBlock);

                var checkInput = function checkInput (unit, dim, value, expected) {
                    var input = $fields[unit][dim];
                    var keyup, keydown, code;
                    var i;
                    value = '' + value;
                    input.val('');
                    for (i = 0; i < value.length; i++) {
                        // get charcode doesn't work because of numpad keys (charcode returns ascii)
                        code = keyboardKeyCodes['KEYCODES'].hasOwnProperty(value[i]) ? parseInt(keyboardKeyCodes['KEYCODES'][value[i]]) : 0;
                        keydown = $.Event("keydown", { keyCode: code });
                        input.trigger(keydown);
                        if ( !keydown.isDefaultPrevented() ) {
                            input.val(input.val() + value[i]);
                        }
                        keyup = $.Event("keyup", { keyCode: code });
                        input.trigger(keyup);
                    }
                    assert.equal(input.val(), expected, '[' + unit + '][' + dim + '] The value "' + value + '" transformed to ' + expected);
                };

                $fields['%'].width = $('input[name=width]', $percentEditorContainer);
                $fields.px.width = $('input[name=width]', $pxEditorContainer);
                $fields.px.height = $('input[name=height]', $pxEditorContainer);

                checkInput(params.unit, params.dim, params.value, params.expected);

                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Pixels mode', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(3);

        conf.showResponsiveToggle = false;
        conf.responsive = false;
        conf.syncDimensions = true;
        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $pixelBlock = $('.media-sizer-pixel', this.getContainer());
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

                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.asyncTest('Workflow', function (assert) {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        QUnit.expect(17);

        conf.showResponsiveToggle = true;
        conf.responsive = false;
        conf.syncDimensions = false;

        mediaDimensionComponent($toolsContainer, media, conf)
            .on('render', function () {
                var $pixelBlock = $('.media-sizer-pixel', this.getContainer());
                var $pxEditorContainer = $('.item-editor-unit-input-box', $pixelBlock);
                var $widthInput = $('input[name=width]', $pxEditorContainer);
                var $heightInput = $('input[name=height]', $pxEditorContainer);

                var $percentBlock = $('.media-sizer-percent', this.getContainer());
                var $pcEditorContainer = $('.item-editor-unit-input-box', $percentBlock);
                var $pcInput = $('input[name=width]', $pcEditorContainer);
                var $pcSliderBox = $('.media-sizer-slider-box', $percentBlock);
                var $pcSliderPosEl = $('.noUi-origin', $pcSliderBox);
                var $pcSliderEl = $('.media-sizer-slider', $percentBlock);

                assert.ok(true, 'component created without synchronisation in the px mode [100x100 & 100% & ratio=1]');
                assert.equal($widthInput.val(), 500, 'Width = 500');
                assert.equal($heightInput.val(), 735, 'Height = 735');
                assert.equal($pcInput.val(), 71, 'Percent input = 71');
                assert.equal($pcSliderPosEl.prop('style').left, '71.429%', 'Slider pc = 71.429%');

                assert.ok(true, 'change width to 27 [27x100 & 100% & ratio=27/100=0.27]');
                $widthInput.val(27);
                $widthInput.trigger('keyup'); // to apply changes in State
                assert.equal($heightInput.val(), 735, 'Height = 735');
                assert.equal($pcInput.val(), 4, 'Percent input = 4');
                assert.equal($pcSliderPosEl.prop('style').left, '3.8571%', 'Slider pc = 3.8571%');

                assert.ok(true, 'change height to 13.5 [13.5x27 & 100% & ratio=13.5/27=0.5]');
                $heightInput.val(14).trigger('keyup');
                assert.equal($widthInput.val(), 27, 'Width = 27');
                assert.equal($pcInput.val(), 4, 'Percent input = 4');
                assert.equal($pcSliderPosEl.prop('style').left, '3.8571%', 'Slider pc = 3.8571%');

                assert.ok(true, 'change percent by PC slider to 20% [100x100 & 20% & ratio=1]');
                $pcSliderEl.val(20).trigger('slide');
                assert.equal($pcSliderPosEl.prop('style').left, '20%', 'Slider pc = 20%');
                assert.equal($widthInput.val(), 140, 'Width = 140');
                assert.equal($heightInput.val(), 206, 'Height = 206');

                this.destroy();
                $visibleContainer.remove();
                QUnit.start();
            });
    });

    QUnit.module('Demo');

    QUnit.test('Preview components workflow', function () {
        var $tmplContainer = $('.template .visible-test');
        var $visibleContainer = $tmplContainer.clone().appendTo('.sandbox'),
            conf = _.cloneDeep(workingConfiguration),
            $controlContainer = $('.control-container', $visibleContainer),
            $img = $('.picture', $visibleContainer),
            $toolsContainer = $('.tools-container', $visibleContainer),
            media = {
                $node: $img,
                $container: $controlContainer,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: 500,
                height: 735
            };
        conf.showResponsiveToggle = true;
        conf.responsive = true;
        conf.syncDimensions = true;
        mediaDimensionComponent($toolsContainer, media, conf);
        QUnit.ok(true);
    });
});
