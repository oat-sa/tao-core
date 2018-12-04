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
 * Copyright (c) 2018 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'lodash',
    'core/promise',
    'ui/maths/calculator/core/plugin',
    'ui/maths/calculator/pluginsLoader',
    'ui/maths/calculator/plugins/core/degrad',
    'ui/maths/calculator/plugins/core/history',
    'ui/maths/calculator/plugins/core/remind',
    'ui/maths/calculator/plugins/core/stepNavigation',
    'test/ui/maths/calculator/pluginsLoader/plugin1',
    'test/ui/maths/calculator/pluginsLoader/plugin2'
], function (
    _,
    Promise,
    pluginFactory,
    loadPlugins,
    pluginDegradFactory,
    pluginHistoryFactory,
    pluginRemindFactory,
    pluginStepNavigationFactory,
    plugin1,
    plugin2
) {
    'use strict';

    var defaultPlugins = [
        pluginDegradFactory,
        pluginHistoryFactory,
        pluginRemindFactory,
        pluginStepNavigationFactory
    ];

    QUnit.module('loader');

    QUnit.test('module', function (assert) {
        QUnit.expect(3);
        assert.equal(typeof loadPlugins, 'function', "The module exposes a function");
        assert.ok(loadPlugins() instanceof Promise, "The loader produces a promise");
        assert.notStrictEqual(loadPlugins(), loadPlugins(), "The loader provides a different object on each call");
    });

    QUnit.asyncTest('default', function (assert) {
        QUnit.expect(1);

        loadPlugins()
            .then(function (plugins) {
                assert.deepEqual(plugins, defaultPlugins, 'Default plugins are loaded');
                QUnit.start();
            })
            .catch(function (err) {
                console.error(err);
                assert.ok(false, 'Should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('static plugins', function (assert) {
        var loadedPlugins = [
            plugin1, plugin2
        ];

        QUnit.expect(1);

        loadPlugins({
            test: loadedPlugins
        })
            .then(function (plugins) {
                assert.deepEqual(plugins, defaultPlugins.concat(loadedPlugins), 'All plugins are loaded');
                QUnit.start();
            })
            .catch(function (err) {
                console.error(err);
                assert.ok(false, 'Should not fail!');
                QUnit.start();
            });
    });

    QUnit.asyncTest('dynamic plugins', function (assert) {
        var loadedPlugins = [
            plugin1, plugin2
        ];

        QUnit.expect(1);

        loadPlugins(null, [{
            category: 'test',
            module: 'test/ui/maths/calculator/pluginsLoader/plugin1',
            bundle: 'test/ui/maths/calculator/pluginsLoader/bundle'
        }, {
            category: 'test',
            module: 'test/ui/maths/calculator/pluginsLoader/plugin2',
            bundle: 'test/ui/maths/calculator/pluginsLoader/bundle'
        }])
            .then(function (plugins) {
                assert.deepEqual(plugins, defaultPlugins.concat(loadedPlugins), 'All plugins are loaded');
                QUnit.start();
            })
            .catch(function (err) {
                console.error(err);
                assert.ok(false, 'Should not fail!');
                QUnit.start();
            });
    });
});
