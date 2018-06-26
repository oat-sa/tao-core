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
    'ui/mediaEditor/mediaEditorComponent',
    'css!test/ui/mediaEditor/mediaEditorComponent/styles'
], function ($, _, mediaEditorComponent) {
    'use strict';

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        QUnit.expect(3);

        assert.ok(typeof mediaEditorComponent === 'function', 'the module exposes a function');
        assert.ok(typeof mediaEditorComponent(false, []) === 'object', 'the factory creates an object');
        assert.notEqual(mediaEditorComponent({}), mediaEditorComponent({}), 'the factory creates new objects');
    });

    QUnit.test('component', function (assert) {
        var component;

        QUnit.expect(2);

        component = mediaEditorComponent({});

        assert.ok(typeof component.render === 'function', 'the component has a render method');
        assert.ok(typeof component.destroy === 'function', 'the component has a destroy method');
    });

    QUnit.test('eventifier', function (assert) {
        var component;

        QUnit.expect(3);

        component = mediaEditorComponent({});

        assert.ok(typeof component.on === 'function', 'the component has a on method');
        assert.ok(typeof component.off === 'function', 'the component has a off method');
        assert.ok(typeof component.trigger === 'function', 'the component has a trigger method');
    });

    QUnit.module('Demo');

    QUnit.test('elements workflow', function (assert) {
        var $container;
        var $demoContainer = $('<div class="demo-container">');
        var $controlContainer = $('<div class="control-container">');
        var $editableContainer = $('<div class="editable-container">');
        var $img = $('<img src="js/test/ui/mediaEditor/sample/space.jpg">');

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        $demoContainer.insertAfter($container);
        $demoContainer.append($editableContainer);
        $editableContainer.append($controlContainer);
        $editableContainer.append($img);

        mediaEditorComponent({
            $media: $img,
            tools: {
                mediaDimension: {
                    $container: $controlContainer,
                    active: true
                }
            }
        })
            .on('render', function () {
                // check that control panel initialized and works
                // check that image is shown and manageable
            })
            .render();
    });
});
