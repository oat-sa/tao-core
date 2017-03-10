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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'ui/button',
    'css!taoCss/tao-3.css',
    'css!taoCss/tao-main-style.css'
], function ($, _, button) {
    'use strict';

    var renderCases;
    var buttonApi = [
        {title: 'init'},
        {title: 'destroy'},
        {title: 'render'},
        {title: 'show'},
        {title: 'hide'},
        {title: 'enable'},
        {title: 'disable'},
        {title: 'is'},
        {title: 'setState'},
        {title: 'getId'},
        {title: 'getContainer'},
        {title: 'getElement'},
        {title: 'getTemplate'},
        {title: 'setTemplate'}
    ];


    QUnit.module('button');


    QUnit.test('module', function (assert) {
        QUnit.expect(3);

        assert.equal(typeof button, 'function', "The button module exposes a function");
        assert.equal(typeof button(), 'object', "The button factory produces an object");
        assert.notStrictEqual(button(), button(), "The button factory provides a different object on each call");
    });


    QUnit
        .cases(buttonApi)
        .test('instance API ', function (data, assert) {
            var instance = button();
            QUnit.expect(1);
            assert.equal(typeof instance[data.title], 'function', 'The button instance exposes a "' + data.title + '" function');
        });


    QUnit.test('init', function (assert) {
        var buttonId = 'myButton';
        var undef;
        var config = {
            nothing: undef,
            dummy: null,
            id: buttonId
        };
        var instance = button(config);

        QUnit.expect(5);

        assert.notEqual(instance.config, config, 'The button instance must duplicate the config set');
        assert.equal(instance.hasOwnProperty('nothing'), false, 'The button instance must not accept undefined config properties');
        assert.equal(instance.hasOwnProperty('dummy'), false, 'The button instance must not accept null config properties');
        assert.equal(instance.getId(), buttonId, 'The button instance must catch the ID');
        assert.equal(instance.is('rendered'), false, 'The button instance must not be rendered');

        instance.destroy();
    });


    renderCases = [{
        title: 'simple',
        config: {
            id: 'btn1',
            label: 'Button 1'
        }
    }, {
        title: 'info',
        config: {
            id: 'btn1',
            type: 'info',
            label: 'Button 1'
        }
    }, {
        title: 'title',
        config: {
            id: 'btn1',
            title: 'My awesome button',
            label: 'Button 1'
        }
    }, {
        title: 'small',
        config: {
            id: 'btn1',
            small: true,
            label: 'Button 1'
        }
    }, {
        title: 'large',
        config: {
            id: 'btn1',
            small: false,
            label: 'Button 1'
        }
    }, {
        title: 'icon',
        config: {
            id: 'btn1',
            icon: 'foo',
            label: 'Button 1'
        }
    }];
    QUnit.cases(renderCases)
        .test('render ', function (data, assert) {
            var $dummy = $('<div class="dummy" />');
            var $container = $('#fixture-1').append($dummy);
            var config = _.merge({
                renderTo: $container,
                replace: true
            }, data.config);
            var instance;

            QUnit.expect(17);

            // check place before render
            assert.equal($container.children().length, 1, 'The container already contains an element');
            assert.equal($container.children().get(0), $dummy.get(0), 'The container contains the dummy element');
            assert.equal($container.find('.dummy').length, 1, 'The container contains an element of the class dummy');

            // create an instance with auto rendering
            instance = button(config);

            // check the rendered header
            assert.equal($container.find('.dummy').length, 0, 'The container does not contain an element of the class dummy');
            assert.equal(instance.is('rendered'), true, 'The button instance must be rendered');
            assert.equal(typeof instance.getElement(), 'object', 'The button instance returns the rendered content as an object');
            assert.equal(instance.getElement().length, 1, 'The button instance returns the rendered content');
            assert.equal(instance.getElement().parent().get(0), $container.get(0), 'The button instance is rendered inside the right container');
            assert.ok(instance.getElement().is('button'), 'The button instance has rendered the button');
            assert.ok(instance.getElement().is('[data-control="' + data.config.id + '"]'), 'The button instance has the right identifier');
            assert.equal(instance.getElement().text().trim(), data.config.label, 'The button instance has rendered the button  with label ' + data.config.label);

            if (data.config.type) {
                assert.equal(instance.getElement().hasClass('btn-' + data.config.type), true, 'The button instance has rendered the button with type ' + data.config.type);
            } else {
                assert.equal(instance.getElement().attr('class').indexOf('btn-'), -1, 'The button instance has rendered the button without any type');
            }

            if (data.config.title) {
                assert.equal(instance.getElement().attr('title'), data.config.title, 'The button instance has rendered the button with title ' + data.config.title);
            } else {
                assert.equal(typeof instance.getElement().attr('title'), 'undefined', 'The button instance has rendered the button without any title');
            }

            if (typeof data.config.small !== 'undefined') {
                assert.equal(instance.getElement().hasClass('small'), !!data.config.small, 'The button instance has rendered the button with the right small style');
            } else {
                assert.equal(instance.getElement().hasClass('small'), true, 'The button instance has rendered the button with the small style');
            }

            if (data.config.icon) {
                assert.equal(instance.getElement().find('.icon').hasClass('icon-' + data.config.icon), true, 'The button instance has rendered the icon ' + data.config.icon);
            } else {
                assert.equal(instance.getElement().find('.icon').length, 0, 'The button instance has rendered the button without any icon');
            }

            instance.destroy();

            assert.equal($container.children().length, 0, 'The container is now empty');
            assert.equal(instance.getElement(), null, 'The button instance has removed its rendered content');
        });


    QUnit.test('show/hide', function (assert) {
        var instance = button().render();

        var $component = instance.getElement();

        QUnit.expect(8);

        assert.equal(instance.is('rendered'), true, 'The button instance must be rendered');
        assert.equal($component.length, 1, 'The button instance returns the rendered content');

        assert.equal(instance.is('hidden'), false, 'The button instance is visible');
        assert.equal(instance.getElement().hasClass('hidden'), false, 'The button instance does not have the hidden class');

        instance.hide();

        assert.equal(instance.is('hidden'), true, 'The button instance is hidden');
        assert.equal(instance.getElement().hasClass('hidden'), true, 'The button instance has the hidden class');

        instance.show();

        assert.equal(instance.is('hidden'), false, 'The button instance is visible');
        assert.equal(instance.getElement().hasClass('hidden'), false, 'The button instance does not have the hidden class');

        instance.destroy();
    });


    QUnit.test('enable/disable', function (assert) {
        var instance = button().render();
        var $component = instance.getElement();

        QUnit.expect(8);

        assert.equal(instance.is('rendered'), true, 'The button instance must be rendered');
        assert.equal($component.length, 1, 'The button instance returns the rendered content');

        assert.equal(instance.is('disabled'), false, 'The button instance is enabled');
        assert.equal(instance.getElement().hasClass('disabled'), false, 'The button instance does not have the disabled class');

        instance.disable();

        assert.equal(instance.is('disabled'), true, 'The button instance is disabled');
        assert.equal(instance.getElement().hasClass('disabled'), true, 'The button instance has the disabled class');

        instance.enable();

        assert.equal(instance.is('disabled'), false, 'The button instance is enabled');
        assert.equal(instance.getElement().hasClass('disabled'), false, 'The button instance does not have the disabled class');

        instance.destroy();
    });


    QUnit.test('state', function (assert) {
        var instance = button().render();
        var $component = instance.getElement();

        QUnit.expect(8);

        assert.equal(instance.is('rendered'), true, 'The button instance must be rendered');
        assert.equal($component.length, 1, 'The button instance returns the rendered content');

        assert.equal(instance.is('customState'), false, 'The button instance does not have the customState state');
        assert.equal(instance.getElement().hasClass('customState'), false, 'The button instance does not have the customState class');

        instance.setState('customState', true);

        assert.equal(instance.is('customState'), true, 'The button instance has the customState state');
        assert.equal(instance.getElement().hasClass('customState'), true, 'The button instance has the customState class');

        instance.setState('customState', false);

        assert.equal(instance.is('customState'), false, 'The button instance does not have the customState state');
        assert.equal(instance.getElement().hasClass('customState'), false, 'The button instance does not have the customState class');

        instance.destroy();
    });


    QUnit.asyncTest('events', function (assert) {
        var config = {
            id: 'button1',
            label: 'Button 1'
        };
        var instance = button(config) ;

        instance.on('custom', function () {
            assert.ok(true, 'The button instance can handle custom events');
            QUnit.start();
        });

        instance.on('render', function () {
            assert.ok(true, 'The button instance triggers event when it is rendered');
            QUnit.start();

            instance.getElement().click();
        });

        instance.on('click', function(buttonId) {
            assert.ok(true, 'The button instance call the right action when the button is clicked');
            assert.equal(buttonId, 'button1', 'The button instance provides the button identifier when the button is clicked');
            QUnit.start();
        });

        instance.on('destroy', function () {
            assert.ok(true, 'The button instance triggers event when it is destroyed');
            QUnit.start();
        });

        QUnit.expect(5);
        QUnit.stop(3);

        instance
            .render()
            .trigger('custom')
            .destroy();
    });


    QUnit.module('Visual');


    QUnit.test('simple button', function(assert){
        QUnit.expect(1);
        button({
            id: 'btn1',
            label: 'Button 1'
        })
            .on('render', function(){
                assert.ok(true);
            })
            .on('click', function () {
                console.log('button 1', arguments);
            })
            .render('#outside');
    });

});
