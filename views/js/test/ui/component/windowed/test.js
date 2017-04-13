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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
/**
 * @author Christophe Noël <christophe@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/component',
    'ui/component/windowed'
], function ($, componentFactory, makeWindowed) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makeWindowed === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'getTitleBar' },
            { title: 'getBody' }
        ])
        .test('component API', function(data, assert) {
            var component = makeWindowed(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.title);
        });



    QUnit.module('Visual test');

    QUnit.asyncTest('display and play', function (assert) {
        var component = componentFactory(),
            $container = $('#outside');

        QUnit.expect(1);

        makeWindowed(component);

        component
            .on('render', function(){
                assert.ok(true);
                QUnit.start();
            })
            .init({
                minWidth: 300,
                maxWidth: 700,
                minHeight: 150,
                maxHeight: 450
            })
            .render($container)
            .setSize(500, 300)
            .center();
    });

});