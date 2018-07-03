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
    'util/image',
    'css!test/ui/mediaEditor/mediaEditorComponent/styles'
], function ($, _, mediaEditorComponent, imageUtil) {
    'use strict';

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

        imageUtil.getSize($img.attr('src'), function(size){
            var media = {
                $node: $img,
                type: 'image/jpeg',
                src: $img.attr('src'),
                width: size.width,
                height: size.height
            };

            mediaEditorComponent($editableContainer, media, {
                mediaDimension: {
                    $container: $controlContainer,
                    active: true
                }
            });
        });
    });
});
