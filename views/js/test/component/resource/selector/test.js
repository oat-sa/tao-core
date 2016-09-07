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
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'component/resource/selector',
    'json!test/component/resource/selector/classes.json'
], function(resourceSelector, classesData) {
    'use strict';

    var resourceSelectorApi = [
        { name : 'init', title : 'init' },
        { name : 'destroy', title : 'destroy' },
        { name : 'render', title : 'render' },
        { name : 'show', title : 'show' },
        { name : 'hide', title : 'hide' },
        { name : 'enable', title : 'enable' },
        { name : 'disable', title : 'disable' },
        { name : 'is', title : 'is' },
        { name : 'setState', title : 'setState' },
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getElement', title : 'getElement' },
        { name : 'getTemplate', title : 'getTemplate' },
        { name : 'setTemplate', title : 'setTemplate' }
    ];

    QUnit.module('API');


    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof resourceSelector, 'function', "The resourceSelector module exposes a function");
        assert.equal(typeof resourceSelector(), 'object', "The resourceSelector factory produces an object");
        assert.notStrictEqual(resourceSelector(), resourceSelector(), "The resourceSelector factory provides a different object on each call");
    });

    QUnit
        .cases(resourceSelectorApi)
        .test('instance API ', function(data, assert) {
            var instance = resourceSelector();
            assert.equal(typeof instance[data.name], 'function', 'The resourceSelector instance exposes a "' + data.title + '" function');
        });



    QUnit.module('Visual');


    QUnit.asyncTest('playground', function(assert) {
        QUnit.expect(1);

        resourceSelector()
            .on('render', function(){
                assert.ok(true);
            })
            .init({
                type: 'TestTaker',
                classUri: "http://www.tao.lu/Ontologies/TAOSubject.rdf#Subject",
                classesData : classesData
            })
           .render(document.getElementById('visual'));
    });

});
