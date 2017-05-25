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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'lodash',
    'ui/form/form'
], function($, _, formFactory) {
    'use strict';

    var formApi;


    QUnit.module('form');


    QUnit.test('module', 3, function(assert) {
        assert.equal(typeof formFactory, 'function', "The form module exposes a function");
        assert.equal(typeof formFactory(), 'object', "The form factory produces an object");
        assert.notStrictEqual(formFactory(), formFactory(), "The mediaplayer factory provides a different object on each call");
    });


    formApi = [
        { name : 'addField', title : 'addField' }
    ];

    QUnit
        .cases(formApi)
        .test('API ', function(data, assert) {
            var instance = formFactory();
            assert.equal(typeof instance[data.name], 'function', 'The form instance exposes a "' + data.title + '" function');
        });

});
