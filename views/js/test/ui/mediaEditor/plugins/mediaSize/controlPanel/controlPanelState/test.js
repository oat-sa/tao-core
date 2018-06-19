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
    'ui/mediaEditor/plugins/mediaSize/controlPanelStateComponent'
], function ($, ControlPanelStateComponent) {
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

    QUnit.module('Component');

    QUnit.test('percent change', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 1, 'The percent were changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 1, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 1, 'The height changed according to percent');
            });

        QUnit.expect(6);

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 100, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 100, 'The height set at init is correct');

        comp.percentChange(1);
    });
});
