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
define([
    'jquery',
    'lodash',
    'ui/component',
    'ui/animable/pulsable/pulsable'
], function($, _, componentFactory, makePulsable) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.ok(typeof makePulsable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'pulse',          method: 'pulse' }
        ])
        .test('component API', function(data, assert) {
            var component = makePulsable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.method);
        });

    QUnit.module('Behavior');

    QUnit.asyncTest('pulse', function(assert) {
        var $container = $('#qunit-fixture');
        makePulsable(componentFactory())
            .init()
            .render($container)
            .pulse(1).then(function(){
                assert.ok(true, 'pulsed');
                QUnit.start();
            });
    });

    QUnit.module('Visual');

    QUnit.test('playground', function(assert) {
        var $container = $('#visual');
        var $count = $container.find('.count');
        var $pulse = $container.find('.pulse');
        var pulsable = makePulsable(componentFactory())
            .init()
            .render($container.find('.target'));

        $pulse.click(function(){
            pulsable.pulse($count.val());
        });

        assert.ok(true, 'started');
    });
});
