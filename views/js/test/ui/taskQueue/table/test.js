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
    'ui/taskQueue/table'
], function($, _, taskQueueTableFactory){
    'use strict';

    QUnit.module('API');

    QUnit.test('factory', function(assert) {
        QUnit.expect(5);

        var context = 'oneTypeOfSuperLongTask';
        var taskTable;

        assert.equal(typeof taskQueueTableFactory, 'function', "The module exposes a function");

        assert.throws(function(){
            taskQueueTableFactory();
        }, TypeError, 'The component needs to be configured');

        assert.throws(function(){
            taskQueueTableFactory({context:''});
        }, TypeError, 'The component needs a not empty context');

        taskTable = taskQueueTableFactory({context:context});

        assert.equal(typeof taskTable, 'object', 'The factory creates an object');
        assert.notDeepEqual(taskTable, taskQueueTableFactory({context:context}), 'The factory creates new objects');
    });

    var pluginApi = [
        { name : 'init', title : 'init' },
        { name : 'render', title : 'render' },
        { name : 'destroy', title : 'destroy' },
        { name : 'on', title : 'on' },
        { name : 'off', title : 'off' },
        { name : 'trigger', title : 'trigger' },
    ];

    QUnit
        .cases(pluginApi)
        .test('component method ', function(data, assert) {
            QUnit.expect(1);

            var context = 'oneTypeOfSuperLongTask';
            var taskTable = taskQueueTableFactory({context:context});

            assert.equal(typeof taskTable[data.name], 'function', 'The component exposes a "' + data.name + '" function');
        });

    QUnit.module('Behavior');

    QUnit.asyncTest('create table', function (assert){
        QUnit.expect(4);

        var context = 'oneTypeOfSuperLongTask';
        var $fixtureContainer = $('#qunit-fixture');
        var taskTable = taskQueueTableFactory({context:context});

        taskTable
            .on('render', function () {
                var $component = $('.component', $fixtureContainer);

                assert.equal($component.length, 1, 'The component has been appended to the container');
                assert.ok($component.hasClass('rendered'), 'The component has the rendered class');

            })
            .on('loaded', function(){

                var $component = $('.component', $fixtureContainer);
                assert.equal($('.datatable-container > table', $component).length, 1, 'The table is also added');
                assert.equal($('.datatable-container > table tbody tr', $component).length, 3, 'The table contains 3 rows');

                QUnit.start();
            })
            .init({
                dataUrl : '/tao/views/js/test/ui/taskQueue/table/data.json',
                statusUrl : '/tao/views/js/test/ui/taskQueue/table/data-status.json',
                removeUrl : '/tao/views/js/test/ui/taskQueue/table/data-archived.json'
            })
            .render($fixtureContainer);
    });

});
