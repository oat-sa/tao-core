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
 * Copyright (c) 2018  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksander Zagovorychev <olexander.zagovorychev@1pt.com>
 */

define([
    'jquery',
    'ui/datatable'
], function ($) {
    "use strict";

    QUnit.module('Datatable pagination behavior');

    QUnit.asyncTest('disabled', function (assert) {
        var $elt = $('#container-1');

        QUnit.expect(6);
        assert.ok($elt.length === 1, 'Test the fixture is available');

        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.icon-backward').length === 1, 'there is 1 backward button');
            assert.ok($elt.find('.icon-forward').length === 1, 'there is 1 forward button');
            assert.ok($elt.find('.icon-backward').parents('button').prop('disabled'), 'the backward button is disabled');
            assert.ok($elt.find('.icon-forward').parents('button').prop('disabled'), 'the forward button is disabled');
            QUnit.start();
        });

        $elt.datatable({
            url: 'js/test/ui/datatable/data.json',
            'model': [{
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'name',
                label: 'Name',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'roles',
                label: 'Roles',
                sortable: false
            }, {
                id: 'dataLg',
                label: 'Data Language',
                sortable: true
            }, {
                id: 'guiLg',
                label: 'Interface Language',
                sortable: true
            }]
        });
    });

    QUnit.asyncTest('enabled', function (assert) {
        var $elt = $('#container-1');

        QUnit.expect(7);
        assert.ok($elt.length === 1, 'Test the fixture is available');


        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');
            assert.ok($elt.find('.icon-backward').length === 1, 'there is 1 backward buttons');
            assert.ok($elt.find('.icon-forward').length === 1, 'there is 1 forward buttons');
            assert.ok($elt.find('.icon-forward:first').parents('button').prop('disabled') === false, 'the forward button is enabled');
            assert.ok($elt.find('.icon-forward:last').parents('button').prop('disabled') === false, 'the forward button is disabled');
            assert.ok($elt.find('.icon-backward:first').parents('button').prop('disabled'), 'the backward button is disabled (on the 1st page)');
            QUnit.start();
        });
        $elt.datatable({
            url: 'js/test/ui/datatable/largedata.json',
            'model': [{
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'password',
                label: 'Pass',
                sortable: true
            }, {
                id: 'title',
                label: 'Title',
                sortable: true
            }, {
                id: 'firstname',
                label: 'First',
                sortable: true
            }, {
                id: 'lastname',
                label: 'Last',
                sortable: true
            }, {
                id: 'gender',
                label: 'Gender',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'picture',
                label: 'picture',
                sortable: true
            }, {
                id: 'address',
                label: 'Address',
                sortable: true
            }]
        });
    });

    QUnit.asyncTest('Simple pagination blocker', function (assert) {
        var $elt = $('#container-1');
        var forwardBtn, backwardBtn;

        QUnit.expect(13);
        assert.ok($elt.length === 1, 'Test the fixture is available');

        QUnit.stop(3);

        $elt.on('create.datatable', function () {
            assert.ok($elt.find('.datatable').length === 1, 'the layout has been inserted');

            backwardBtn = $elt.find('.icon-backward').parents('button');
            forwardBtn = $elt.find('.icon-forward').parents('button');

            assert.ok(backwardBtn.length === 1, 'there is 1 backward button');
            assert.ok(forwardBtn.length === 1, 'there is 1 forward button');
            assert.notEqual(forwardBtn.attr('disabled'), 'disabled', 'Next button must not be disabled');
            assert.equal(backwardBtn.attr('disabled'), 'disabled', 'Prev button must be disabled');
            QUnit.start();
        });

        $elt.on('query.datatable', function (event, ajaxConfig) {
            assert.equal(typeof ajaxConfig, 'object', 'the query event is triggered and provides an object');
            assert.equal(typeof ajaxConfig.url, 'string', 'the query event provides an object containing the target url');
            assert.equal(typeof ajaxConfig.data, 'object', 'the query event provides an object containing the request parameters');

            backwardBtn = $elt.find('.icon-backward').parents('button');
            forwardBtn = $elt.find('.icon-forward').parents('button');

            if (backwardBtn.length && forwardBtn.length) {
                // first query will be before the render action
                assert.equal(forwardBtn.attr('disabled'), 'disabled', 'Next button must be disabled');
                assert.equal(backwardBtn.attr('disabled'), 'disabled', 'Prev button must be disabled');
            }
            QUnit.start();
        });

        $elt.on('beforeload.datatable', function (event, response) {
            assert.equal(typeof response, 'object', 'the beforeload event is triggered and provides the response data');

            backwardBtn = $elt.find('.icon-backward').parents('button');
            forwardBtn = $elt.find('.icon-forward').parents('button');

            if (backwardBtn.length && forwardBtn.length) {
                // first query will be before the render action
                assert.equal(forwardBtn.attr('disabled'), 'disabled', 'Next button must be disabled');
                assert.equal(backwardBtn.attr('disabled'), 'disabled', 'Prev button must be disabled');
            }
            QUnit.start();
        });

        $elt.on('load.datatable', function (event, response) {
            assert.equal(typeof response, 'object', 'the load event is triggered and provides the response data');

            backwardBtn = $elt.find('.icon-backward').parents('button');
            forwardBtn = $elt.find('.icon-forward').parents('button');

            assert.notEqual(forwardBtn.attr('disabled'), 'disabled', 'Next button must not be disabled');
            assert.equal(backwardBtn.attr('disabled'), 'disabled', 'Prev button must be disabled');
            QUnit.start();
        });

        // simple pagination provider by default
        $elt.datatable({
            url: 'js/test/ui/datatable/largedata.json',
            'model': [{
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'password',
                label: 'Pass',
                sortable: true
            }, {
                id: 'title',
                label: 'Title',
                sortable: true
            }, {
                id: 'firstname',
                label: 'First',
                sortable: true
            }, {
                id: 'lastname',
                label: 'Last',
                sortable: true
            }, {
                id: 'gender',
                label: 'Gender',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'picture',
                label: 'picture',
                sortable: true
            }, {
                id: 'address',
                label: 'Address',
                sortable: true
            }]
        });
    });
});
