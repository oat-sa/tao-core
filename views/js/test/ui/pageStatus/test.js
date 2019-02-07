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
    'lodash',
    'jquery',
    'ui/pageStatus'
], function (_, $, pageStatusFactory) {
    'use strict';

    var isHeadless = /HeadlessChrome/.test(navigator.userAgent);


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


    if (isHeadless){
        QUnit.asyncTest('popup status', function (assert) {

            var popup = window.open('/tao/views/js/test/ui/pageStatus/blank.html','test','width=300,height=300,visible=none');

            var pageStatus = pageStatusFactory({
                window :  popup
            });
            QUnit.expect(4);

            pageStatus
                .on('statuschange', _.once(function(status){
                    assert.ok(true, 'The statuschange event is triggered');
                }))
                .on('hide', _.once(function(){
                    assert.ok(true, 'The hide event is triggered');
                }))
                .on('load', _.once(function(){
                    assert.ok(true, 'The load event is triggered');
                }))
                .on('unload', _.once(function(){
                    assert.ok(true, 'The unload event is triggered');
                }));


            _.delay(function() {
                popup.close();
            },100);

            setTimeout(function () {
                QUnit.start();
            }, 300)
        });


    }else{
        QUnit.asyncTest('popup status', function (assert) {

            var popup = window.open('/tao/views/js/test/ui/pageStatus/blank.html','test','width=300,height=300,visible=none');
            var secondPopup;

            var pageStatus = pageStatusFactory({
                window :  popup
            });

            QUnit.expect(6);

            pageStatus
                .on('statuschange', _.once(function(status){
                    assert.ok(true, 'The statuschange event is triggered');
                }))
                .on('focus', _.once(function(){
                    assert.ok(true, 'The focus event is triggered');
                }))
                .on('hide', _.once(function(){
                    assert.ok(true, 'The hide event is triggered');
                }))
                .on('load', _.once(function(){
                    assert.ok(true, 'The load event is triggered');
                }))
                .on('blur', _.once(function(){
                    assert.ok(true, 'The blur event is triggered');
                }))
                .on('unload', _.once(function(){
                    assert.ok(true, 'The unload event is triggered');
                }));


            _.delay(function() {
                secondPopup = window.open('/tao/views/js/test/ui/pageStatus/blank.html','test2','width=300,height=300');
                _.delay(function () {
                    popup.close();

                },200)
            },100);

            setTimeout(function () {
                secondPopup.close();
                QUnit.start();
            }, 400)
        });

    }
});
