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
    'ui/mediaEditor/plugins/mediaSize/ControlPanelStateComponent'
], function ($, _, ControlPanelStateComponent) {
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

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        QUnit.expect(3);

        assert.ok(typeof ControlPanelStateComponent === 'function', 'the module exposes a function');
        assert.ok(typeof ControlPanelStateComponent({sizeProps: 'fake'}) === 'object', 'the factory creates an object');
        assert.notEqual(ControlPanelStateComponent({sizeProps: 'fake'}), ControlPanelStateComponent({sizeProps: 'fake'}), 'the factory creates new objects');
    });

    QUnit.test('component', function (assert) {
        var component;

        QUnit.expect(2);

        component = ControlPanelStateComponent({sizeProps: 'fake'});

        assert.ok(typeof component.render === 'function', 'the component has a render method');
        assert.ok(typeof component.destroy === 'function', 'the component has a destroy method');
    });

    QUnit.test('eventifier', function (assert) {
        var component;

        QUnit.expect(3);

        component = ControlPanelStateComponent({sizeProps: 'fake'});

        assert.ok(typeof component.on === 'function', 'the component has a on method');
        assert.ok(typeof component.off === 'function', 'the component has a off method');
        assert.ok(typeof component.trigger === 'function', 'the component has a trigger method');
    });

    QUnit.module('Percent');

    QUnit.test('Changed to 1%', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 1, 'The percent changed');
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

    QUnit.test('Calculating decimals', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 3, 'The percent changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 236.85, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 162.9, 'The height changed according to percent');
            });

        QUnit.expect(6);

        // make calculation more difficult
        conf.sizeProps.px = {
            natural: {
                width: 7895,
                height: 5430
            },
            current: {
                width: 7895,
                height: 5430
            }
        };

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height set at init is correct');

        comp.percentChange(3);
    });

    QUnit.test('Changed to 0%', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 0, 'The percent changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 0, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 0, 'The height changed according to percent');
            });

        QUnit.expect(6);

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 100, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 100, 'The height set at init is correct');

        comp.percentChange(0);
    });

    QUnit.test('Changed to 120%', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 120, 'The percent changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 120, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 120, 'The height changed according to percent');
            });

        QUnit.expect(6);

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 100, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 100, 'The height set at init is correct');

        comp.percentChange(120);
    });

    QUnit.module('Width with dimensions');

    QUnit.test('Set width to 50', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 50, 'The percent changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 50, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 50, 'The height changed according to percent');
            });

        QUnit.expect(6);

        conf.syncDimensions = true;

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 100, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 100, 'The height set at init is correct');

        comp.widthChange(50);
    });

    QUnit.test('Set width to 250 (over the range)', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 250, 'The percent changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 250, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 250, 'The height changed according to percent');
            });

        QUnit.expect(6);

        conf.syncDimensions = true;

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 100, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 100, 'The height set at init is correct');

        // over the range
        comp.widthChange(250);
    });

    QUnit.test('Calculating decimals', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 22.34568, 'The percent were changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 22.34568, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 22.34568, 'The height changed according to percent');
            });

        QUnit.expect(6);

        conf.syncDimensions = true;

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 100, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 100, 'The height set at init is correct');

        comp.widthChange(22.345676);
    });

    QUnit.test('Calculating decimals 2', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 0.27866, 'The percent was changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 22, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 31.98711, 'The height changed according to percent');
            });

        QUnit.expect(6);

        conf.syncDimensions = true;
        // make calculation more difficult
        conf.sizeProps.px = {
            natural: {
                width: 7895,
                height: 5430
            },
            current: {
                width: 7895,
                height: 5430
            }
        };

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height set at init is correct');

        comp.widthChange(22);
    });

    QUnit.test('Calculating decimals 3 (with negative decimal)', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, -0.28433, 'The percent was changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, -22.4477, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, -32.63805, 'The height changed according to percent');
            });

        QUnit.expect(6);

        conf.syncDimensions = true;
        // make calculation more difficult
        conf.sizeProps.px = {
            natural: {
                width: 7895,
                height: 5430
            },
            current: {
                width: 7895,
                height: 5430
            }
        };

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height set at init is correct');

        comp.widthChange(-22.4477);
    });

    QUnit.module('Without synchronization');

    QUnit.test('Change percent', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 20, 'The percent was changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 1579, 'The width changed according to percent');
                assert.equal(comp.getProp('sizeProps').px.current.height, 1086, 'The height changed according to percent');
            });

        QUnit.expect(6);

        conf.syncDimensions = false;
        // make calculation more difficult
        conf.sizeProps.px = {
            natural: {
                width: 7895,
                height: 5430
            },
            current: {
                width: 7895,
                height: 5430
            }
        };

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height set at init is correct');

        comp.percentChange(20);
    });

    QUnit.test('Change width', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent was NOT changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 20, 'The width was changed');
                assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height was NOT changed');
            });

        QUnit.expect(6);

        conf.syncDimensions = false;
        // make calculation more difficult
        conf.sizeProps.px = {
            natural: {
                width: 7895,
                height: 5430
            },
            current: {
                width: 7895,
                height: 5430
            }
        };

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height set at init is correct');

        comp.widthChange(20);
    });

    QUnit.test('Change height', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        var comp = ControlPanelStateComponent(conf)
            .on('changed', function() {
                assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent was NOT changed');
                assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width was changed');
                assert.equal(comp.getProp('sizeProps').px.current.height, 20, 'The height was NOT changed');
            });

        QUnit.expect(6);

        conf.syncDimensions = false;
        // make calculation more difficult
        conf.sizeProps.px = {
            natural: {
                width: 7895,
                height: 5430
            },
            current: {
                width: 7895,
                height: 5430
            }
        };

        // before change
        assert.equal(comp.getProp('sizeProps')['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.width, 7895, 'The width set at init is correct');
        assert.equal(comp.getProp('sizeProps').px.current.height, 5430, 'The height set at init is correct');

        comp.heightChange(20);
    });
});
