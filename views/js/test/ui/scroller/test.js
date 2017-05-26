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
    'ui/scroller'
], function ($, scroller) {
    'use strict';

    QUnit.module('Module');

    QUnit.test('Module export', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof scroller === 'object', 'The module expose an object');
    });

    QUnit
        .cases([
            { title: 'scrollTo' },
            { title: 'enableScrolling' },
            { title: 'disableScrolling' }
        ])
        .test('API', function (data, assert) {
            QUnit.expect(1);
            assert.ok(typeof scroller[data.title] === 'function', 'instance implements ' + data.title);
        });

    QUnit.module('ScrollTo');

    QUnit
        .cases([
            { title: 'scroll down',     selector: '.el-5', startPosition: 0,    expectedPosition: 80 },
            { title: 'scroll up',       selector: '.el-1', startPosition: 120,  expectedPosition: 0 },
            { title: 'no scroll',       selector: '.el-4', startPosition: 60,   expectedPosition: 60 },
            { title: 'no element',      selector: '.el-X', startPosition: 0,    expectedPosition: 0 }
        ])
        .asyncTest('scroll to element', function(data, assert) {
            var $container = $('.container', '#qunit-fixture'),
                $element = $container.find(data.selector);

            QUnit.expect(2);

            $container.scrollTop(data.startPosition);
            assert.equal($container.scrollTop(), data.startPosition, 'The container has the correct start position');

            scroller.scrollTo($element, $container).then(function() {
                assert.equal($container.scrollTop(), data.expectedPosition, 'The container have been scroller to the right position');
                QUnit.start();
            });
        });

});