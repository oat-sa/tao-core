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
                    width: 600,
                    height: 480
                },
                current: {
                    width: 600,
                    height: 480
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
                    max: 500,
                    start: 500
                }
            }
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

    QUnit.test('Check required properties', function (assert) {
        QUnit.expect(1);
        assert.throws(
            function () { controlPanelComponent({}) },
            function( err ) {
                return err.toString() === 'Error: Control panel of the media editor is required sizeProps parameter';
            },
            'Throws an error on the initialization without required parameters'
        );
    });

    QUnit.asyncTest('Check showResponsiveToggle On property', function (assert) {
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

    QUnit.asyncTest('Check showResponsiveToggle Off property', function (assert) {
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

    QUnit.asyncTest('Check currentUtil parameter is set to percent', function (assert) {
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

    QUnit.asyncTest('Check currentUtil parameter is set to pixel', function (assert) {
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

    QUnit.asyncTest('Responsive mode check slider [percent]', function (assert) {
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

                // try to change percent in input to change the slider position as a result
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

    QUnit.asyncTest('Check allowed symbols in the input fields', function (assert) {
        var $container, conf;
        QUnit.expect(4);

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
                var press = jQuery.Event("keyup", { keyCode: 51 });

                assert.equal($percentInput.val(), 100, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '100%', 'Slider has been set to 100%');

                // try to change percent in input to change the slider position as a result
                $percentInput.val(3);
                $percentInput.trigger(press);
                assert.equal($percentInput.val(), 3, 'Width value is set to 100 percent');
                assert.equal($sliderPosEl.prop('style').left, '3%', 'Slider has been set to 100%');

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
