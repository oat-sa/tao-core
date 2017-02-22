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
 */
define([
    'jquery',
    'lodash',
    'ui/stacker'
], function ($, _, stackerFactory) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('stacker');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.ok(typeof stackerFactory === 'function', 'The module expose a function');
    });

    QUnit.module('.bringToFront()');

    QUnit.test('set the highest zIndex', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1');

        QUnit.expect(1);

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), 1001, 'correct z-index has been set');
    });

    QUnit.test('does not increase z-index if already max', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1');

        QUnit.expect(2);

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), 1001, 'correct z-index has been set');

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), 1001, 'z-index has not been increased again');
    });

    QUnit.test('increase z-index by configured increment', function (assert) {
        var stacker = stackerFactory({ increment: 5 }),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            $div2 = $container.find('#div2'),
            $div3 = $container.find('#div3');

        QUnit.expect(18);

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), 1005, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 'auto', 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 'auto', 'div3 has the correct z-index');

        stacker.bringToFront($div2);
        assert.equal($div1.css('z-index'), 1005, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 'auto', 'div3 has the correct z-index');

        stacker.bringToFront($div3);
        assert.equal($div1.css('z-index'), 1005, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1015, 'div3 has the correct z-index');

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), 1020, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1015, 'div3 has the correct z-index');

        stacker.bringToFront($div3);
        assert.equal($div1.css('z-index'), 1020, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1025, 'div3 has the correct z-index');

        stacker.bringToFront($div2);
        assert.equal($div1.css('z-index'), 1020, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1030, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1025, 'div13has the correct z-index');
    });

    QUnit.module('.autoBringToFront()');

    QUnit.test('set the highest zIndex on mousedown', function (assert) {
        var stacker = stackerFactory({ increment: 5 }),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            $div2 = $container.find('#div2'),
            $div3 = $container.find('#div3');

        QUnit.expect(18);

        stacker.autoBringToFront($div1);
        stacker.autoBringToFront($div2);
        stacker.autoBringToFront($div3);

        $div1.trigger('mousedown');
        assert.equal($div1.css('z-index'), 1005, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 'auto', 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 'auto', 'div3 has the correct z-index');

        $div2.trigger('mousedown');
        assert.equal($div1.css('z-index'), 1005, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 'auto', 'div3 has the correct z-index');

        $div3.trigger('mousedown');
        assert.equal($div1.css('z-index'), 1005, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1015, 'div3 has the correct z-index');

        $div1.trigger('mousedown');
        assert.equal($div1.css('z-index'), 1020, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1015, 'div3 has the correct z-index');

        $div3.trigger('mousedown');
        assert.equal($div1.css('z-index'), 1020, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1010, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1025, 'div3 has the correct z-index');

        $div2.trigger('mousedown');
        assert.equal($div1.css('z-index'), 1020, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), 1030, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), 1025, 'div13has the correct z-index');
    });


});