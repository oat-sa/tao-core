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
    'lodash',
    'jquery',
    'layout/generisRouter'
], function (_, $, generisRouterFactory) {
    'use strict';

    var location = window.history.location || window.location;
    var testerUrl = location.href;

    QUnit.module('Module');

    QUnit.test('Module export', function (assert) {
        QUnit.expect(3);

        assert.ok(typeof generisRouterFactory === 'function', 'The module expose a factory function');
        assert.ok(typeof generisRouterFactory() === 'object', 'The factory returns an object');
        assert.equal(generisRouterFactory(), generisRouterFactory(), 'The factory always returns the same object');
    });

    QUnit
        .cases([
            {title: 'pushState'},
            {title: 'restoreState'},

            // eventifier
            {title: 'on'},
            {title: 'off'},
            {title: 'trigger'}
        ])
        .test('Instance API', function (data, assert) {
            var instance = generisRouterFactory();
            QUnit.expect(1);

            assert.ok(typeof instance[data.title] === 'function', 'instance implements ' + data.title);
        });

    QUnit.module('.pushState()', {
        setup: function() {
            window.history.replaceState(null, '', testerUrl);
        },
        teardown: function() {
            window.history.replaceState(null, '', testerUrl);
        }
    });

    QUnit.asyncTest('if url has no section, add it and replace current state', function(assert) {
        var generisRouter = generisRouterFactory();
        var url = 'http://tao/tao/Main/index?structure=items&ext=taoItems';
        var section = { id: 'authoring' };
        var restoreWith = 'activate';

        QUnit.expect(4);

        generisRouter
            .off('.test')
            .on('replacestate.test', function(stateUrl) {
                var state = window.history.state;
                assert.ok(true, 'replacestate have been called');
                assert.equal(stateUrl, '/tao/Main/index?structure=items&ext=taoItems&section=authoring');
                assert.equal(state.sectionId, section.id, 'section id param has been correctly set');
                assert.equal(state.restoreWith, restoreWith, 'restoreWith param has been correctly set');
                QUnit.start();
            })
            .on('pushstate.test', function() {
                assert.ok(false, 'I should not be called');
                QUnit.start();
            });

        generisRouter.pushState(url, section, restoreWith);
    });

    QUnit.asyncTest('if url already has a section, push new state', function(assert) {
        var generisRouter = generisRouterFactory();
        var url = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=authoring';
        var section = { id: 'manage_items' };
        var restoreWith = 'activate';

        QUnit.expect(4);

        generisRouter
            .off('.test')
            .on('pushstate.test', function(stateUrl) {
                var state = window.history.state;
                assert.equal(stateUrl, '/tao/Main/index?structure=items&ext=taoItems&section=manage_items');
                assert.ok(true, 'pushstate has been called');
                assert.equal(state.sectionId, section.id, 'section id param has been correctly set');
                assert.equal(state.restoreWith, restoreWith, 'restoreWith param has been correctly set');
                QUnit.start();
            })
            .on('replacestate.test', function() {
                assert.ok(false, 'I should not be called');
                QUnit.start();
            });

        generisRouter.pushState(url, section, restoreWith);
    });

    QUnit.asyncTest('does not push new state if section does not change', function(assert) {
        var generisRouter = generisRouterFactory();
        var url = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=authoring';
        var section = { id: 'authoring' };

        generisRouter
            .off('.test')
            .on('pushstate.test', function() {
                assert.ok(false, 'I should not be called');
                QUnit.start();
            })
            .on('replacestate.test', function() {
                assert.ok(false, 'I should not be called');
                QUnit.start();
            });

        generisRouter.pushState(url, section, 'activate');

        assert.ok(_.isNull(window.history.state), 'state has not been updated');
        QUnit.start();
    });


    QUnit.asyncTest('on popstate, trigger the sectionactivate event if previous state was pushed with the "activate" param', function(assert) {
        var generisRouter = generisRouterFactory();
        var url1 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=authoring';
        var url2 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=manage_items';
        var section1 = { id: 'manage_items' };
        var section2 = { id: 'authoring' };
        var restoreWith = 'activate';

        generisRouter
            .off('.test')
            .on('sectionactivate.test', function(sectionId) {
                assert.ok(true, 'sectionactivate has been called');
                assert.equal(sectionId, 'manage_items', 'correct param is passed to the callback');
                QUnit.start();
            })
            .on('sectionshow.test', function() {
                assert.ok(false, 'I should not be called');
                QUnit.start();
            });

        generisRouter.pushState(url1, section1, restoreWith);
        generisRouter.pushState(url2, section2, restoreWith);

        window.history.back();
    });

    QUnit.asyncTest('on popstate, trigger the sectionshow event if previous state was pushed with the "show" param', function(assert) {
        var generisRouter = generisRouterFactory();
        var url1 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=authoring';
        var url2 = 'http://tao/tao/Main/index?structure=items&ext=taoItems&section=manage_items';
        var section1 = { id: 'manage_items' };
        var section2 = { id: 'authoring' };
        var restoreWith = 'show';

        generisRouter
            .off('.test')
            .on('sectionshow.test', function(sectionId) {
                assert.ok(true, 'sectionshow has been called');
                assert.equal(sectionId, 'manage_items', 'correct param is passed to the callback');
                QUnit.start();
            })
            .on('sectionactivate.test', function() {
                assert.ok(false, 'I should not be called');
                QUnit.start();
            });

        generisRouter.pushState(url1, section1, restoreWith);
        generisRouter.pushState(url2, section2, restoreWith);

        window.history.back();
    });

});