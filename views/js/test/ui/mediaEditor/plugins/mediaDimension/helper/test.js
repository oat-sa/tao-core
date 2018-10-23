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
    'ui/mediaEditor/plugins/mediaDimension/helper'
], function ($, _, helper) {
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

    QUnit.module('Width with dimensions');

    QUnit.test('Set width to 50', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        conf.syncDimensions = true;
        QUnit.expect(6);

        // before change
        assert.equal(conf.sizeProps['%'].current.width, 100, 'The percent set at init is correct');
        assert.equal(conf.sizeProps.px.current.width, 100, 'The width set at init is correct');
        assert.equal(conf.sizeProps.px.current.height, 100, 'The height set at init is correct');

        conf = helper.applyDimensions(conf, { width: 50, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 6.25, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 50, 'The width changed according to percent');
        assert.equal(conf.sizeProps.px.current.height, 50, 'The height changed according to percent');
    });

    QUnit.test('Set width to 250 (over the range)', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        conf.syncDimensions = true;
        QUnit.expect(3);

        conf = helper.applyDimensions(conf, { width: 250, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 31.25, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 250, 'The width changed according to percent');
        assert.equal(conf.sizeProps.px.current.height, 250, 'The height changed according to percent');
    });

    QUnit.test('Set height to 250 (over the range)', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        QUnit.expect(3);

        conf.syncDimensions = true;
        conf = helper.applyDimensions(conf, { height: 250, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 31.25, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 250, 'The width changed according to percent');
        assert.equal(conf.sizeProps.px.current.height, 250, 'The height changed according to percent');
    });

    QUnit.test('Calculating decimals', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        conf.syncDimensions = true;
        QUnit.expect(9);

        conf = helper.applyDimensions(conf, { width: 22.345676, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 2.7933, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 22.346, 'The width changed according to percent');
        assert.equal(conf.sizeProps.px.current.height, 22.346, 'The height changed according to percent');

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

        conf = helper.applyDimensions(conf, { width: 22, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 2.75, 'The percent not changed');
        assert.equal(conf.sizeProps.px.current.width, 22, 'The width changed according to percent');
        assert.equal(conf.sizeProps.px.current.height, 15.131, 'The height changed according to percent');

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

        conf = helper.applyDimensions(conf, { width: -22.448, maxWidth: 800 });
        assert.equal(conf.sizeProps['%'].current.width, 1, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, -22.448, 'The width changed according to percent');
        assert.equal(conf.sizeProps.px.current.height, -15.439, 'The height changed according to percent');
    });

    QUnit.test('Change percent', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        conf.syncDimensions = true;
        QUnit.expect(3);

        conf = helper.applyDimensions(conf, { percent: 150, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 100, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 800, 'The width changed');
        assert.equal(conf.sizeProps.px.current.height, 800, 'The height changed according to percent');
    });

    QUnit.test('Change percent should not update the percent height', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        conf.syncDimensions = true;
        QUnit.expect(4);

        conf = helper.applyDimensions(conf, { percent: 15, maxWidth: 800 });

        assert.equal(conf.sizeProps['%'].current.width, 15, 'The percent changed');
        assert.equal(conf.sizeProps['%'].current.height, null, 'The height is null');
        assert.equal(conf.sizeProps.px.current.width, 120, 'The width changed');
        assert.equal(conf.sizeProps.px.current.height, 120, 'The height changed according to percent');
    });

    QUnit.module('Without synchronization');

    QUnit.test('SyncDimensions switching', function (assert) {
        var conf = _.cloneDeep(workingConfiguration);
        QUnit.expect(12);

        conf.syncDimensions = false;
        conf = helper.applyDimensions(conf, { width: 150, maxWidth: 800 });
        assert.equal(conf.sizeProps['%'].current.width, 18.75, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 150, 'The width updated');
        assert.equal(conf.sizeProps.px.current.height, 100, 'The height not touched');

        conf.syncDimensions = true;
        conf = helper.applyDimensions(conf, { height: 150, maxWidth: 800 });
        assert.equal(conf.sizeProps['%'].current.width, 18.75, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 225, 'The width changed');
        assert.equal(conf.sizeProps.px.current.height, 150, 'The height changed');

        conf.syncDimensions = false;
        conf = helper.applyDimensions(conf, { height: 200, maxWidth: 800 });
        assert.equal(conf.sizeProps['%'].current.width, 18.75, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 225, 'The width not changed');
        assert.equal(conf.sizeProps.px.current.height, 200, 'The height changed');

        conf.syncDimensions = true;
        conf = helper.applyDimensions(conf, { width: 200, maxWidth: 800 });
        assert.equal(conf.sizeProps['%'].current.width, 25, 'The percent changed');
        assert.equal(conf.sizeProps.px.current.width, 200, 'The width changed');
        assert.equal(conf.sizeProps.px.current.height, 177.78 , 'The height changed');
    });
});
