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
 * Test the module ui/pageStatus
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/pageStatus'
], function ($, pageStatusFactory) {
    'use strict';

    QUnit.module('pageStatus');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.equal(typeof pageStatusFactory, 'function', "The pageStatus module exposes an function");
    });

    QUnit.test('api', function (assert) {
        QUnit.expect(5);
        var pageStatus = pageStatusFactory();

        assert.equal(typeof pageStatus, 'object', "The factory creates an object");
        assert.notEqual(pageStatus, pageStatusFactory(), "The factory creates a new object");
        assert.equal(typeof pageStatus.on, 'function', "The pageStatus module expose the on method");
        assert.equal(typeof pageStatus.off, 'function', "The pageStatus module expose the off method");
        assert.equal(typeof pageStatus.trigger, 'function', "The pageStatus module expose the trigger method");

    });

    QUnit.asyncTest('popup status', function (assert) {
        QUnit.expect(9);

        var popup = window.open('/','test','width=300,height=300,visible=none');

        var pageStatus = pageStatusFactory({
            window :  popup
        });

        var counter = 0;

        pageStatus
            .on('statuschange', function(status){
                assert.ok(true, 'The statuschange event is triggered');
            })
            .on('focus', function(){
                assert.ok(true, 'The focus event is triggered');
            })
            .on('hide', function(){
                assert.ok(true, 'The blur event is triggered');
            })
            .on('blur', function(){
                assert.ok(true, 'The blur event is triggered');
            })
            .on('unload', function(){
                assert.ok(true, 'The unload event is triggered');
            });

        setTimeout(function() {
            popup.focus();
        }, 100);

        setTimeout(function() {
            popup.close();
            QUnit.start();
        }, 200);
    });
});
