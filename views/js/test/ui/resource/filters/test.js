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

/**
 * Test the module ui/resource/filters
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'ui/resource/filters',
    'json!test/ui/resource/filters/properties.json'
], function($, filtersFactory, propertiesData) {
    'use strict';

    var labelUri = 'http://www.w3.org/2000/01/rdf-schema#label';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof filtersFactory, 'function', "The filtersFactory module exposes a function");
        assert.equal(typeof filtersFactory(), 'object', "The filtersFactory produces an object");
        assert.notStrictEqual(filtersFactory(), filtersFactory(), "The filtersFactory provides a different object on each call");
    });

    QUnit.cases([
        { title : 'init' },
        { title : 'destroy' },
        { title : 'render' },
        { title : 'show' },
        { title : 'hide' },
        { title : 'enable' },
        { title : 'disable' },
        { title : 'is' },
        { title : 'setState' },
        { title : 'getContainer' },
        { title : 'getElement' },
        { title : 'getTemplate' },
        { title : 'setTemplate' },
    ]).test('Component API ', function(data, assert) {
        var instance = filtersFactory();
        assert.equal(typeof instance[data.title], 'function', 'The filters exposes the component method "' + data.title);
    });

    QUnit.cases([
        { title : 'on' },
        { title : 'off' },
        { title : 'trigger' },
        { title : 'before' },
        { title : 'after' },
    ]).test('Eventifier API ', function(data, assert) {
        var instance = filtersFactory();
        assert.equal(typeof instance[data.title], 'function', 'The filters exposes the eventifier method "' + data.title);
    });

    QUnit.cases([
        { title : 'getValues' },
        { title : 'setValue' },
        { title : 'reset' },
        { title : 'getTextualQuery' },
        { title : 'update' }
    ]).test('Instance API ', function(data, assert) {
        var instance = filtersFactory();
        assert.equal(typeof instance[data.title], 'function', 'The filters exposes the method "' + data.title);
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('Lifecycle', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(2);

        filtersFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData
        })
        .on('init', function(){
            assert.ok( !this.is('rendered'), 'The component is not yet rendered');
        })
        .on('render', function(){

            assert.ok(this.is('rendered'), 'The component is now rendered');

            this.destroy();
        })
        .on('destroy', function(){

            QUnit.start();
        });
    });

    QUnit.asyncTest('Rendering', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(11);

        assert.equal($('.filters', $container).length, 0, 'No resource list in the container');

        filtersFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData,
            title : 'Foo',
            applyLabel : 'Bar'
        })
        .on('render', function(){

            var $element = this.getElement();

            assert.equal($('.filters', $container).length, 1, 'The component has been inserted');
            assert.equal($('.filters', $container)[0], $element[0], 'The component element is correct');

            assert.equal($('form', $element).length, 1, 'The component contains a form');
            assert.equal($('fieldset :input', $element).length, 3, 'The component contains 3 input fields');

            assert.equal($('[name="' + labelUri + '"]', $element).length, 1, 'The component contains the label field');
            assert.equal($('[name="http://bertaodev/tao.rdf#i15012259849560117"]', $element).length, 1, 'The component contains the lang field');

            assert.equal($('h2', $element).length, 1, 'The component contains a title');
            assert.equal($('h2', $element).text().trim(), 'Foo', 'The component has the correct title');
            assert.equal($('.toolbar :submit', $element).length, 1, 'The component contains the apply button');
            assert.equal($('.toolbar :submit', $element).text().trim(), 'Bar', 'The apply label is correct');

            QUnit.start();
        });
    });

    QUnit.asyncTest('getValues', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(10);

        assert.equal($('.filters', $container).length, 0, 'No resource list in the container');

        filtersFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData,
        })
        .on('render', function(){

            var $element = this.getElement();
            var $label   = $('[name="' + labelUri + '"]', $element);
            var $apply   = $('.toolbar :submit', $element);
            var values;

            assert.equal($label.length, 1, 'The component has the label field');
            assert.equal($apply.length, 1, 'The component has the apply button');

            assert.equal($label.val(), '', 'The label value is empty');

            values = this.getValues();

            assert.deepEqual(values, {}, 'values is an empty object');

            $label.val('a label');

            values = this.getValues();

            assert.equal(typeof values[labelUri], 'string', 'The label has an entry');
            assert.equal(values[labelUri], 'a label', 'The label has the correct value');

            this.on('change', function(newValues){

                assert.deepEqual(newValues, this.getValues(), 'The apply values are the component values');
                assert.equal(typeof values[labelUri], 'string', 'The label has an entry');
                assert.equal(values[labelUri], 'a label', 'The label has the correct value');
                QUnit.start();
            });

            $apply.trigger('click');
        });
    });

    QUnit.asyncTest('setValue', function(assert) {
        var $container = $('#qunit-fixture');

        QUnit.expect(6);

        filtersFactory($container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData,
        })
        .on('render', function(){

            var $element = this.getElement();
            var $label   = $('[name="' + labelUri + '"]', $element);
            var values;

            assert.equal($label.length, 1, 'The component has the label field');
            assert.equal($label.val(), '', 'The label value is empty');

            values = this.getValues();

            assert.deepEqual(values, {}, 'values is an empty object');

            this.setValue(labelUri, 'Foo Bar');

            assert.equal($label.val(), 'Foo Bar', 'The label field has now a value');

            values = this.getValues();

            assert.equal(typeof values[labelUri], 'string', 'The label has an entry');
            assert.equal(values[labelUri], 'Foo Bar', 'The label has the correct value');

            QUnit.start();
        });
    });


    QUnit.module('Visual');

    QUnit.asyncTest('playground', function(assert) {

        var container = document.getElementById('visual');

        QUnit.expect(1);

        filtersFactory(container, {
            classUri : 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item',
            data : propertiesData
        })
        .on('render', function(){
            assert.ok(true);
            QUnit.start();
        });
    });
});
