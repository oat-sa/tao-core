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
    'ui/mediaEditor/mediaEditorComponent'
], function ($, mediaEditorComponent) {
    'use strict';

    QUnit.module('Demo');

    QUnit.test('elements workflow', function (assert) {
        var $container;
        var $demoContainer = $('.demo-container');
        var $controlContainer = $('.control-container', $demoContainer);
        var $editableContainer = $('.editable-container', $controlContainer);
        var $toolsContainer = $('.tools-container', $demoContainer);
        var $img = $('.picture', $editableContainer);
        var media = {
            $node: $img,
            $container: $editableContainer,
            type: 'image/jpeg',
            src: $img.attr('src'),
            width: 500,
            height: 735,
            responsive: true
        };

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        mediaEditorComponent($toolsContainer, media, {
            mediaDimension: {
                active: true
            }
        }).on('change', function(nMedia){
            media = nMedia;
            if (media.responsive) {
                media.$node.css({
                    width: media.width + '%',
                    height: 'auto'
                });
                media.$node.attr('width', media.width + '%');
                media.$node.attr('height', '');
            } else {
                media.$node.css({
                    width: media.width,
                    height: media.height
                });
                media.$node.attr('width', media.width);
                media.$node.attr('height', media.height);
            }
        });
    });
});
