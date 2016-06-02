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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'ui/autoscroll'
], function($, autoscroll) {
    'use strict';

    QUnit.module('autoscroll', {
        setup: function(){
            $('#qunit-fixture .container').scrollTop(0);
        }
    });


    QUnit.test('module', function(assert) {
        QUnit.expect(1);
        assert.equal(typeof autoscroll, 'function', "The autoscroll module exposes a function");
    });

    
    QUnit.asyncTest('scroll from bottom', function(assert) {
        QUnit.expect(2);

        var $container = $('#qunit-fixture .container');
        var $element = $('.el-1', $container);

        $container.scrollTop(80);
        assert.equal($container.scrollTop(), 80, 'The container must not display the element at this time');

        autoscroll($element, $container).then(function() {
            assert.equal($container.scrollTop(), 0, 'The container must have been scrolled to display the element');
            QUnit.start();
        });
    });


    QUnit.asyncTest('scroll from top', function(assert) {
        QUnit.expect(2);

        var $container = $('#qunit-fixture .container');
        var $element = $('.el-5', $container);

        $container.scrollTop(0);
        assert.equal($container.scrollTop(), 0, 'The container must not display the element at this time');

        autoscroll($element, $container).then(function() {
            assert.equal($container.scrollTop(), 80, 'The container must have been scrolled to display the element');
            QUnit.start();
        });
    });


    QUnit.asyncTest('auto detect parent', function(assert) {
        QUnit.expect(2);

        var $container = $('#qunit-fixture .container');
        var $element = $('.el-2');

        $container.scrollTop(0);
        assert.equal($container.scrollTop(), 0, 'The container must not display the element at this time');

        autoscroll($element, $container).then(function() {
            assert.equal($container.scrollTop(), 20, 'The container must have been scrolled to display the element');
            QUnit.start();
        });
    });


    QUnit.asyncTest('no scroll if visible', function(assert) {
        QUnit.expect(2);

        var $container = $('#qunit-fixture .container');
        var $element = $('.el-3', $container);

        $container.scrollTop(40);
        assert.equal($container.scrollTop(), 40, 'The container must already display the element at this time');

        autoscroll($element, $container).then(function() {
            assert.equal($container.scrollTop(), 40, 'The container must have been scrolled to display the element');
            QUnit.start();
        });
    });


    QUnit.asyncTest('must always resolve', function(assert) {
        QUnit.expect(1);

        autoscroll().then(function() {
            assert.ok(true, 'The autoscroll resolve the promise even if no element is found');
            QUnit.start();
        });
    });
});
