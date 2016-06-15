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

    var headless = /PhantomJS/.test(window.navigator.userAgent);

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


    if(headless){

        //testing unloading a popup seems to be the only reliable test on phantomjs
        QUnit.asyncTest('popup unload', function (assert) {
            QUnit.expect(2);

            var popup = window.open('/','test','width=300,height=300,visible=none');

            var pageStatus = pageStatusFactory({
                window :  popup
            });

            pageStatus.on('statuschange', function(status){
                assert.equal(status, 'unload', 'The status is unload');
                setTimeout(function() {
                    QUnit.start();
                }, 500);
            }).on('unload', function(){
                assert.ok(true, 'The unload event is triggered');
            });

            popup.close();
        });

    } else {

        QUnit.asyncTest('popup status', function (assert) {
            QUnit.expect(8);

            var popup = window.open('/','test','width=300,height=300,visible=none');

            var pageStatus = pageStatusFactory({
                window :  popup
            });

            var counter = 0;

            pageStatus
                .on('statuschange', function(status){
                    switch(counter){
                        case 0: assert.equal(status, 'focus', 'The first event is focus'); break;
                        case 1: assert.ok(status === 'hide' || status === 'blur', 'The second event is either hide or blur'); break;
                        case 2: assert.ok(status === 'hide' || status === 'blur', 'The third event is either hide or blur'); break;
                        case 3: assert.equal(status, 'unload', 'The forth event is unload'); break;
                    }

                    counter++;
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
                    QUnit.start();
                });

            popup.focus();

            setTimeout(function() {
                popup.close();
            });
        });
    }
});
