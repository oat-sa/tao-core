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

    QUnit.module('Demo');

    QUnit.test('Response mode preview', function () {
        var $tmplContainer = $('.template .response-test');
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

    QUnit.test('Strict mode preview', function () {
        var $tmplContainer = $('.template .strict-test');
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
        conf.responsive = false;
        conf.syncDimensions = true;
        mediaDimensionComponent($toolsContainer, media, conf);
        QUnit.ok(true);
    });

    QUnit.test('Strict mode without sync preview', function () {
        var $tmplContainer = $('.template .strict-no-sync-test');
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
        conf.responsive = false;
        conf.syncDimensions = false;
        mediaDimensionComponent($toolsContainer, media, conf);
        QUnit.ok(true);
    });
});
