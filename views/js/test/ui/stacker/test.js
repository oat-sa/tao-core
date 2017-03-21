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
    'ui/stacker'
], function ($, stackerFactory) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('stacker');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.ok(typeof stackerFactory === 'function', 'The module expose a function');
    });

    QUnit.module('getCurrent()');

    QUnit.test('returns the current index value', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            $div2 = $container.find('#div2'),
            $div3 = $container.find('#div3');

        QUnit.expect(3);

        stacker.bringToFront($div1);
        assert.equal(stacker.getCurrent(), 1010, 'index has been correctly incremented');

        stacker.bringToFront($div2);
        assert.equal(stacker.getCurrent(), 1020, 'index has been correctly incremented');

        stacker.bringToFront($div3);
        assert.equal(stacker.getCurrent(), 1030, 'index has been correctly incremented');
    });

    QUnit.module('.bringToFront()');

    QUnit.test('set the highest zIndex', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1');

        QUnit.expect(3);

        assert.equal($div1.css('z-index'), 'auto', 'no z-index is set');

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), stacker.getCurrent(), 'z-index has been set to ' + stacker.getCurrent());

        stacker.reset($div1);
        assert.equal($div1.css('z-index'), 'auto', 'z-index has been removed');
    });

    QUnit.test('does not increase z-index if already max', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            index;

        QUnit.expect(3);

        assert.equal($div1.css('z-index'), 'auto', 'no z-index is set');

        stacker.bringToFront($div1);
        index = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index, 'z-index has been set');

        stacker.bringToFront($div1);
        assert.equal($div1.css('z-index'), index, 'z-index has not been increased again');
    });

    QUnit.test('increase z-index of multiple elements', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            $div2 = $container.find('#div2'),
            $div3 = $container.find('#div3'),
            index1,
            index2 = 'auto',
            index3 = 'auto';

        QUnit.expect(18);

        stacker.bringToFront($div1);
        index1 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        stacker.bringToFront($div2);
        index2 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        stacker.bringToFront($div3);
        index3 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        stacker.bringToFront($div1);
        index1 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        stacker.bringToFront($div3);
        index3 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        stacker.bringToFront($div2);
        index2 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
    });

    QUnit.module('.autoBringToFront()');

    QUnit.test('set the highest zIndex on mousedown', function (assert) {
        var stacker = stackerFactory(),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            $div2 = $container.find('#div2'),
            $div3 = $container.find('#div3'),
            index1,
            index2 = 'auto',
            index3 = 'auto';

        QUnit.expect(18);

        stacker.autoBringToFront($div1);
        stacker.autoBringToFront($div2);
        stacker.autoBringToFront($div3);

        $div1.trigger('mousedown');
        index1 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        $div2.trigger('mousedown');
        index2 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        $div3.trigger('mousedown');
        index3 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        $div1.trigger('mousedown');
        index1 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        $div3.trigger('mousedown');
        index3 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');

        $div2.trigger('mousedown');
        index2 = stacker.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
    });

    QUnit.module('Scopes');

    QUnit.test('handle different scopes', function(assert) {
        var stacker1 = stackerFactory('scope1'),
            stacker2 = stackerFactory('scope2'),
            stacker3 = stackerFactory('scope3'),
            $container = $(fixtureContainer),
            $div1 = $container.find('#div1'),
            $div2 = $container.find('#div2'),
            $div3 = $container.find('#div3'),
            $div4 = $container.find('#div4'),
            $div5 = $container.find('#div5'),
            $div6 = $container.find('#div6'),
            index1,
            index2 = 'auto',
            index3 = 'auto',
            index4 = 'auto',
            index5 = 'auto',
            index6 = 'auto';

        QUnit.expect(36);

        stacker1.bringToFront($div1);
        index1 = stacker1.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
        assert.equal($div4.css('z-index'), index4, 'div4 has the correct z-index');
        assert.equal($div5.css('z-index'), index5, 'div5 has the correct z-index');
        assert.equal($div6.css('z-index'), index6, 'div6 has the correct z-index');

        stacker2.bringToFront($div2);
        index2 = stacker2.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
        assert.equal($div4.css('z-index'), index4, 'div4 has the correct z-index');
        assert.equal($div5.css('z-index'), index5, 'div5 has the correct z-index');
        assert.equal($div6.css('z-index'), index6, 'div6 has the correct z-index');

        stacker3.bringToFront($div3);
        index3 = stacker3.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
        assert.equal($div4.css('z-index'), index4, 'div4 has the correct z-index');
        assert.equal($div5.css('z-index'), index5, 'div5 has the correct z-index');
        assert.equal($div6.css('z-index'), index6, 'div6 has the correct z-index');

        stacker1.bringToFront($div4);
        index4 = stacker1.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
        assert.equal($div4.css('z-index'), index4, 'div4 has the correct z-index');
        assert.equal($div5.css('z-index'), index5, 'div5 has the correct z-index');
        assert.equal($div6.css('z-index'), index6, 'div6 has the correct z-index');

        stacker3.bringToFront($div6);
        index6 = stacker3.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
        assert.equal($div4.css('z-index'), index4, 'div4 has the correct z-index');
        assert.equal($div5.css('z-index'), index5, 'div5 has the correct z-index');
        assert.equal($div6.css('z-index'), index6, 'div6 has the correct z-index');

        stacker2.bringToFront($div5);
        index5 = stacker2.getCurrent();
        assert.equal($div1.css('z-index'), index1, 'div1 has the correct z-index');
        assert.equal($div2.css('z-index'), index2, 'div2 has the correct z-index');
        assert.equal($div3.css('z-index'), index3, 'div3 has the correct z-index');
        assert.equal($div4.css('z-index'), index4, 'div4 has the correct z-index');
        assert.equal($div5.css('z-index'), index5, 'div5 has the correct z-index');
        assert.equal($div6.css('z-index'), index6, 'div6 has the correct z-index');
    });

});