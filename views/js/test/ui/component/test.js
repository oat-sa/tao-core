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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'ui/component'
], function($, _, componentFactory) {
    'use strict';

    QUnit.module('component');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);
        assert.equal(typeof componentFactory, 'function', "The component module exposes a function");
        assert.equal(typeof componentFactory(), 'object', "The component factory produces an object");
        assert.notStrictEqual(componentFactory(), componentFactory(), "The component factory provides a different object on each call");
    });


    QUnit.cases([
        { title : 'init' },
        { title : 'destroy' },
        { title : 'render' },
        { title : 'setSize' },
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
        { title : 'getConfig' }
    ]).test('instance API ', function(data, assert) {
        var instance = componentFactory();
        QUnit.expect(1);
        assert.equal(typeof instance[data.title], 'function', 'The component instance exposes a "' + data.title + '" function');
    });


    QUnit.test('init', function(assert) {
        var specs = {
            value : 10,
            method: function() {

            }
        };
        var defaults = {
            label: 'a label'
        };
        var config = {
            nothing: undefined,
            dummy: null,
            title: 'My Title'
        };
        var instance = componentFactory(specs, defaults).init(config);

        QUnit.expect(10);

        assert.notEqual(instance, specs, 'The component instance must not be the same obect as the list of specs');
        assert.notEqual(instance.config, config, 'The component instance must duplicate the config set');
        assert.equal(instance.hasOwnProperty('nothing'), false, 'The component instance must not accept undefined config properties');
        assert.equal(instance.hasOwnProperty('dummy'), false, 'The component instance must not accept null config properties');
        assert.equal(instance.hasOwnProperty('value'), false, 'The component instance must not accept properties from the list of specs');
        assert.equal(instance.config.title, config.title, 'The component instance must catch the title config');
        assert.equal(instance.config.label, defaults.label, 'The component instance must set the label config');
        assert.equal(instance.is('rendered'), false, 'The component instance must not be rendered');
        assert.equal(typeof instance.method, 'function', 'The component instance must have the functions provided in the list of specs');
        assert.notEqual(instance.method, specs.method, 'The component instance must have created a delegate of the functions provided in the list of specs');

        instance.destroy();
    });


    QUnit.test('render', function(assert) {
        var $dummy1 = $('<div class="dummy" />');
        var $dummy2 = $('<div class="dummy" />');
        var template = '<div class="my-component">TEST</div>';
        var renderedTemplate = '<div class="my-component rendered">TEST</div>';
        var $container1 = $('#fixture-1').append($dummy1);
        var $container2 = $('#fixture-2').append($dummy2);
        var $container3 = $('#fixture-3');
        var instance;

        QUnit.expect(30);

        // auto render at init
        assert.equal($container1.children().length, 1, 'The container1 already contains an element');
        assert.equal($container1.children().get(0), $dummy1.get(0), 'The container1 contains the dummy element');
        assert.equal($container1.find('.dummy').length, 1, 'The container1 contains an element of the class dummy');

        instance = componentFactory().init({
            renderTo: $container1,
            replace: true
        });

        assert.equal($container1.find('.dummy').length, 0, 'The container1 does not contain an element of the class dummy');
        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal(typeof instance.getElement(), 'object', 'The component instance returns the rendered content as an object');
        assert.equal(instance.getElement().length, 1, 'The component instance returns the rendered content');
        assert.equal(instance.getElement().parent().get(0), $container1.get(0), 'The component instance is rendered inside the right container');

        instance.destroy();

        assert.equal($container1.children().length, 0, 'The container1 is now empty');
        assert.equal(instance.getElement(), null, 'The component instance has removed its rendered content');

        // explicit render
        assert.equal($container2.children().length, 1, 'The container2 already contains an element');
        assert.equal($container2.children().get(0), $dummy2.get(0), 'The container2 contains the dummy element');
        assert.equal($container2.find('.dummy').length, 1, 'The container2 contains an element of the class dummy');

        instance = componentFactory().init();
        instance.render($container2);

        assert.equal($container2.find('.dummy').length, 1, 'The container2 contains an element of the class dummy');
        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal(typeof instance.getElement(), 'object', 'The component instance returns the rendered content as an object');
        assert.equal(instance.getElement().length, 1, 'The component instance returns the rendered content');
        assert.equal(instance.getElement().parent().get(0), $container2.get(0), 'The component instance is rendered inside the right container');

        instance.destroy();

        assert.equal($container2.children().length, 1, 'The component has beend removed from the container2');
        assert.equal($container2.find('.dummy').length, 1, 'The container2 contains an element of the class dummy');
        assert.equal(instance.getElement(), null, 'The component instance has removed its rendered content');

        instance = componentFactory().init();
        instance.setTemplate(template);

        assert.equal(typeof instance.getTemplate(), 'function', 'The template used to render the component is a function');
        assert.equal((instance.getTemplate())(), template, 'The built template is the same as the provided one');

        instance.render($container3);

        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal(typeof instance.getElement(), 'object', 'The component instance returns the rendered content as an object');
        assert.equal(instance.getElement().length, 1, 'The component instance returns the rendered content');
        assert.equal(instance.getElement().parent().get(0), $container3.get(0), 'The component instance is rendered inside the right container');
        assert.equal($container3.html(), renderedTemplate, 'The component instance has rendered the right content');

        instance.destroy();

        assert.equal($container3.children().length, 0, 'The container1 is now empty');
        assert.equal(instance.getElement(), null, 'The component instance has removed its rendered content');
    });


    QUnit.test('setSize', function(assert) {
        var $dummy1 = $('<div class="dummy" />');
        var $dummy2 = $('<div class="dummy" />');
        var template = '<div class="my-component">TEST</div>';
        var renderedTemplate = '<div class="my-component rendered">TEST</div>';
        var $container1 = $('#fixture-1').append($dummy1);
        var $container2 = $('#fixture-2').append($dummy2);
        var $container3 = $('#fixture-3');
        var expectedWidth = 200;
        var expectedHeight = 100;
        var instance;
        var getSizeResult;

        QUnit.expect(42);

        // auto render at init
        assert.equal($container1.children().length, 1, 'The container1 already contains an element');
        assert.equal($container1.children().get(0), $dummy1.get(0), 'The container1 contains the dummy element');
        assert.equal($container1.find('.dummy').length, 1, 'The container1 contains an element of the class dummy');

        instance = componentFactory().init({
            renderTo: $container1,
            replace: true,
            width: expectedWidth,
            height: expectedHeight
        });

        assert.equal($container1.find('.dummy').length, 0, 'The container1 does not contain an element of the class dummy');
        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal(typeof instance.getElement(), 'object', 'The component instance returns the rendered content as an object');
        assert.equal(instance.getElement().length, 1, 'The component instance returns the rendered content');
        assert.equal(instance.getElement().parent().get(0), $container1.get(0), 'The component instance is rendered inside the right container');

        assert.equal(instance.getElement().width(), expectedWidth, 'The expected width has been set');
        assert.equal(instance.getElement().height(), expectedHeight, 'The expected height has been set');

        getSizeResult = instance.getSize();
        assert.equal(getSizeResult.width, expectedWidth, '.getSize() returns the expected width');
        assert.equal(getSizeResult.height, expectedHeight, '.getSize() returns the expected height');

        instance.destroy();

        assert.equal($container1.children().length, 0, 'The container1 is now empty');
        assert.equal(instance.getElement(), null, 'The component instance has removed its rendered content');

        // explicit render
        assert.equal($container2.children().length, 1, 'The container2 already contains an element');
        assert.equal($container2.children().get(0), $dummy2.get(0), 'The container2 contains the dummy element');
        assert.equal($container2.find('.dummy').length, 1, 'The container2 contains an element of the class dummy');

        expectedWidth = 250;
        expectedHeight = 150;
        $container2.width(expectedWidth);
        $container2.height(expectedHeight);

        instance = componentFactory().init({
            width: 'auto',
            height: 'auto'
        });
        instance.render($container2);

        assert.equal($container2.find('.dummy').length, 1, 'The container2 contains an element of the class dummy');
        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal(typeof instance.getElement(), 'object', 'The component instance returns the rendered content as an object');
        assert.equal(instance.getElement().length, 1, 'The component instance returns the rendered content');
        assert.equal(instance.getElement().parent().get(0), $container2.get(0), 'The component instance is rendered inside the right container');

        assert.equal(instance.getElement().width(), expectedWidth, 'The expected width has been set');
        assert.equal(instance.getElement().height(), expectedHeight, 'The expected height has been set');

        getSizeResult = instance.getSize();
        assert.equal(getSizeResult.width, expectedWidth, '.getSize() returns the expected width');
        assert.equal(getSizeResult.height, expectedHeight, '.getSize() returns the expected height');

        instance.destroy();

        assert.equal($container2.children().length, 1, 'The component has beend removed from the container2');
        assert.equal($container2.find('.dummy').length, 1, 'The container2 contains an element of the class dummy');
        assert.equal(instance.getElement(), null, 'The component instance has removed its rendered content');

        expectedWidth = 200;
        expectedHeight = 100;

        instance = componentFactory().init();
        instance.setTemplate(template);

        assert.equal(typeof instance.getTemplate(), 'function', 'The template used to render the component is a function');
        assert.equal((instance.getTemplate())(), template, 'The built template is the same as the provided one');

        instance.render($container3);

        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal(typeof instance.getElement(), 'object', 'The component instance returns the rendered content as an object');
        assert.equal(instance.getElement().length, 1, 'The component instance returns the rendered content');
        assert.equal(instance.getElement().parent().get(0), $container3.get(0), 'The component instance is rendered inside the right container');
        assert.equal($container3.html(), renderedTemplate, 'The component instance has rendered the right content');

        instance.setSize(expectedWidth, expectedHeight);
        assert.equal(instance.getElement().width(), expectedWidth, 'The expected width has been set');
        assert.equal(instance.getElement().height(), expectedHeight, 'The expected height has been set');

        getSizeResult = instance.getSize();
        assert.equal(getSizeResult.width, expectedWidth, '.getSize() returns the expected width');
        assert.equal(getSizeResult.height, expectedHeight, '.getSize() returns the expected height');

        instance.destroy();

        assert.equal($container3.children().length, 0, 'The container1 is now empty');
        assert.equal(instance.getElement(), null, 'The component instance has removed its rendered content');
    });

    QUnit
        .cases([
            {
                title: 'content-box, no extra size, margin not included',
                boxSizing: 'content-box', width: 100, height: 100, margin: 0, padding: 0, border: 0, includeMargin: false,
                outerWidth: 100, outerHeight: 100
            }, {
                title: 'content-box, with margin, margin not included',
                boxSizing: 'content-box', width: 100, height: 100, margin: 10, padding: 0, border: 0, includeMargin: false,
                outerWidth: 100, outerHeight: 100
            }, {
                title: 'content-box, with margin, margin included',
                boxSizing: 'content-box', width: 100, height: 100, margin: 10, padding: 0, border: 0, includeMargin: true,
                outerWidth: 120, outerHeight: 120
            }, {
                title: 'content-box, with margin/padding, margin included',
                boxSizing: 'content-box', width: 100, height: 100, margin: 10, padding: 10, border: 0, includeMargin: true,
                outerWidth: 140, outerHeight: 140
            }, {
                title: 'content-box, with margin/padding/border, margin included',
                boxSizing: 'content-box', width: 100, height: 100, margin: 10, padding: 10, border: 10, includeMargin: true,
                outerWidth: 160, outerHeight: 160
            }, {
                title: 'border-box, no extra size, margin not included',
                boxSizing: 'border-box', width: 100, height: 100, margin: 0, padding: 0, border: 0, includeMargin: false,
                outerWidth: 100, outerHeight: 100
            }, {
                title: 'border-box, with margin, margin not included',
                boxSizing: 'border-box', width: 100, height: 100, margin: 10, padding: 0, border: 0, includeMargin: false,
                outerWidth: 100, outerHeight: 100
            }, {
                title: 'border-box, with margin, margin included',
                boxSizing: 'border-box', width: 100, height: 100, margin: 10, padding: 0, border: 0, includeMargin: true,
                outerWidth: 120, outerHeight: 120
            }, {
                title: 'border-box, with margin/padding, margin included',
                boxSizing: 'border-box', width: 100, height: 100, margin: 10, padding: 10, border: 0, includeMargin: true,
                outerWidth: 120, outerHeight: 120
            }, {
                title: 'border-box, with margin/padding/border, margin included',
                boxSizing: 'border-box', width: 100, height: 100, margin: 10, padding: 10, border: 10, includeMargin: true,
                outerWidth: 120, outerHeight: 120
            }
        ])
        .asyncTest('getOuterSize()', function(data, assert) {
            var template = '<div>TEST</div>',
                $container1 = $('#fixture-1');

            QUnit.expect(2);

            componentFactory()
                .setTemplate(template)
                .on('render', function() {
                    var $component = this.getElement(),
                        outerSize;

                    $component.css({
                        'box-sizing': data.boxSizing,
                        'padding': data.padding + 'px',
                        'margin': data.margin + 'px',
                        'border': data.border + 'px solid black'
                    });

                    outerSize = this.getOuterSize(data.includeMargin);
                    assert.equal(outerSize.width, data.outerWidth, 'getOuterSize() returns the correct width');
                    assert.equal(outerSize.height, data.outerHeight, 'getOuterSize() returns the correct height');

                    QUnit.start();
                })
                .init({
                    renderTo: $container1,
                    replace: true,
                    width: data.width,
                    height: data.height
                });
        });


    QUnit.test('show/hide', function(assert) {
        var instance = componentFactory()
                        .init()
                        .render();

        var $component = instance.getElement();

        QUnit.expect(8);

        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal($component.length, 1, 'The component instance returns the rendered content');

        assert.equal(instance.is('hidden'), false, 'The component instance is visible');
        assert.equal(instance.getElement().hasClass('hidden'), false, 'The component instance does not have the hidden class');

        instance.hide();

        assert.equal(instance.is('hidden'), true, 'The component instance is hidden');
        assert.equal(instance.getElement().hasClass('hidden'), true, 'The component instance has the hidden class');

        instance.show();

        assert.equal(instance.is('hidden'), false, 'The component instance is visible');
        assert.equal(instance.getElement().hasClass('hidden'), false, 'The component instance does not have the hidden class');

        instance.destroy();
    });


    QUnit.test('enable/disable', function(assert) {
        var instance = componentFactory()
                        .init()
                        .render();
        var $component = instance.getElement();

        QUnit.expect(8);

        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal($component.length, 1, 'The component instance returns the rendered content');

        assert.equal(instance.is('disabled'), false, 'The component instance is enabled');
        assert.equal(instance.getElement().hasClass('disabled'), false, 'The component instance does not have the disabled class');

        instance.disable();

        assert.equal(instance.is('disabled'), true, 'The component instance is disabled');
        assert.equal(instance.getElement().hasClass('disabled'), true, 'The component instance has the disabled class');

        instance.enable();

        assert.equal(instance.is('disabled'), false, 'The component instance is enabled');
        assert.equal(instance.getElement().hasClass('disabled'), false, 'The component instance does not have the disabled class');

        instance.destroy();
    });


    QUnit.test('state', function(assert) {
        var instance = componentFactory()
                        .init()
                        .render();
        var $component = instance.getElement();

        QUnit.expect(8);

        assert.equal(instance.is('rendered'), true, 'The component instance must be rendered');
        assert.equal($component.length, 1, 'The component instance returns the rendered content');

        assert.equal(instance.is('customState'), false, 'The component instance does not have the customState state');
        assert.equal(instance.getElement().hasClass('customState'), false, 'The component instance does not have the customState class');

        instance.setState('customState', true);

        assert.equal(instance.is('customState'), true, 'The component instance has the customState state');
        assert.equal(instance.getElement().hasClass('customState'), true, 'The component instance has the customState class');

        instance.setState('customState', false);

        assert.equal(instance.is('customState'), false, 'The component instance does not have the customState state');
        assert.equal(instance.getElement().hasClass('customState'), false, 'The component instance does not have the customState class');

        instance.destroy();
    });


    QUnit.asyncTest('events', function(assert) {
        var instance = componentFactory();
        var expectedWidth = 200;
        var expectedHeight = 100;

        QUnit.expect(7);
        QUnit.stop(4);

        instance.on('custom', function() {
            assert.ok(true, 'The component instance can handle custom events');
            QUnit.start();
        });

        instance.on('init', function() {
            assert.ok(true, 'The component instance triggers event when it is initialized');
            QUnit.start();
        });

        instance.on('render', function() {
            assert.ok(true, 'The component instance triggers event when it is rendered');
            QUnit.start();
        });

        instance.on('setsize', function(width, height) {
            assert.ok(true, 'The component instance triggers event when it is resized');
            assert.equal(width, expectedWidth, 'The right width has been provided');
            assert.equal(height, expectedHeight, 'The right height has been provided');
            QUnit.start();
        });

        instance.on('destroy', function() {
            assert.ok(true, 'The component instance triggers event when it is destroyed');
            QUnit.start();
        });

        instance
            .init()
            .render()
            .setSize(expectedWidth, expectedHeight)
            .trigger('custom')
            .destroy();
    });

    QUnit.asyncTest('extends', function (assert) {
        var expectedValue = 'pouet!';
        var instance = componentFactory({
            yolo: function(val) {
                assert.ok(true, 'The additional method has been called');
                assert.equal(val, expectedValue, 'The expected value has been provided');
                QUnit.start();
            }
        });

        QUnit.expect(2);

        instance.yolo(expectedValue);
    });

    QUnit.test('getConfig', function(assert) {
        var defaults = {
            label : 'default',
            value : 12
        };
        var config = {
            label: 'config',
            init  : true
        };
        var instance;

        QUnit.expect(2);

        instance = componentFactory({}, defaults);
        assert.deepEqual(instance.getConfig(), defaults, 'The component contains the default config');

        instance.init(config);

        assert.deepEqual(instance.getConfig(), {
            label : 'config',
            init  : true,
            value : 12
        }, 'The component contains the init config');
    });
});
