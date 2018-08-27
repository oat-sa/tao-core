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
    'ui/animable/absorbable/absorbable'
], function($, _, componentFactory, makeAbsorbable) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.ok(typeof makeAbsorbable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'absorb', method: 'absorb' },
            { title: 'absorbBurst', method: 'absorbBurst' },
        ])
        .test('component API', function(data, assert) {
            var component = makeAbsorbable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.method], 'function', 'The component has the method ' + data.method);
        });

    QUnit.module('Behavior');

    QUnit.asyncTest('absorb', function(assert) {
        var $container = $('#qunit-fixture');
        var $target = $('body');
        makeAbsorbable(componentFactory())
            .init()
            .render($container)
            .absorb($target).then(function(){
            assert.ok(true, 'absorbed');
            QUnit.start();
        });
    });

    QUnit.asyncTest('absorb burst', function(assert) {
        var $container = $('#qunit-fixture');
        var $target = $('body');
        makeAbsorbable(componentFactory())
            .init()
            .render($container)
            .absorbBurst($target, [0, 100, 200]).then(function(){
            assert.ok(true, 'burst absorbed');
            QUnit.start();
        });
    });

    QUnit.module('Visual');

    QUnit.test('playground', function(assert) {
        var $container = $('#visual');
        var $count = $container.find('.count');
        var $trigger = $container.find('.trigger');
        var $absorb = $container.find('.absorb');
        var absorbable = makeAbsorbable(componentFactory())
            .init()
            .render($container.find('.target'));

        $trigger.click(function(){
            var i;
            var burstTiming = [];
            for(i = 0; i< $count.val(); i++){
                burstTiming.push(i*300);
            }
            absorbable.absorbBurst($absorb, burstTiming);
        });

        assert.ok(true, 'started');
    });
});
