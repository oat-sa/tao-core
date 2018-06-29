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
 * Copyright (c) 2013-2018 (original work) Open Assessment Technologies SA ;
 */

/**
 * Test the core/databinder
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'core/databinder'], function($, DataBinder) {
    'use strict';

    var model;

    QUnit.module('2 ways data binding', {
        setup: function() {
            model = {
                "title": "testTitle",
                "testParts": [{
                    "navigationMode": 0,
                    "submissionMode": 0,
                    "assessmentSections": [{
                        "title": "assessmentSectionTitle",
                        "sectionParts": [{
                            "href": "http:\/\/tao26.localdomain\/bertao.rdf#i138356893658013",
                            "identifier": "i138356893658013"
                        }, {
                            "href": "http:\/\/tao26.localdomain\/bertao.rdf#i138356893796686",
                            "identifier": "i138356893796686"
                        }, {
                            "href": "http:\/\/tao26.localdomain\/bertao.rdf#i138356893768139",
                            "identifier": "i138356893768139"
                        }, {
                            "href": "http:\/\/tao26.localdomain\/bertao.rdf#i1383568938403212",
                            "identifier": "i1383568938403212"
                        }]
                    }]
                }]
            };
        }
    });

    QUnit.test('Simple assignment', function(assert) {
        var $container = $('#container-1');

        QUnit.expect(5);
        assert.ok($container.length === 1, 'Test the fixture is available');

        assert.equal($('h1', $container).text(), '', 'h1 is empty');
        assert.equal($('h2', $container).text(), '', 'h2 is empty');

        new DataBinder($container, model).bind();

        assert.equal($('h1', $container).text(), model.title, 'h1 has value assigned');
        assert.equal($('h2', $container).text(), model.testParts[0].assessmentSections[0].sectionParts[1].href, 'h2 has value assigned');
    });

    QUnit.test('Simple value change', function(assert) {
        var $container = $('#container-1');
        var $title = $('h1', $container);

        QUnit.expect(3);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        assert.equal($title.text(), model.title, 'h1 has value assigned');

        $title.text('new title').trigger('change');

        assert.equal(model.title, 'new title', 'model has been updated');
    });

    QUnit.test('Simple value remove', function(assert) {
        var $container = $('#container-1');
        var $title = $('h1', $container);

        QUnit.expect(3);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        assert.equal($title.text(), model.title, 'h1 has value assigned');

        $title.trigger('delete');

        strictEqual(model.title, undefined, 'model title has been removed');
    });

    QUnit.test('Array assignment', function(assert) {
        var $container = $('#container-2');
        var $sectionParts = $('ul', $container);

        QUnit.expect(4);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        strictEqual($sectionParts.find('li').length, model.testParts[0].assessmentSections[0].sectionParts.length, 'the same number of nodes has been inserted');
        assert.equal($sectionParts.find('li:first').text(), model.testParts[0].assessmentSections[0].sectionParts[0].href, 'the first item contains the value');
        assert.equal($sectionParts.find('li:last').text(), model.testParts[0].assessmentSections[0].sectionParts[3].href, 'the last item contains the value');
    });

    QUnit.test('Array value change', function(assert) {
        var $container = $('#container-2');
        var $sectionParts = $('ul', $container);
        var $firstPart;

        QUnit.expect(3);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        $firstPart = $sectionParts.find('li:first');
        assert.equal($firstPart.text(), model.testParts[0].assessmentSections[0].sectionParts[0].href, 'the first item contains the value');

        $firstPart.text('new reference').trigger('change');

        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[0].href, 'new reference', 'array model has been updated');

    });

    QUnit.test('Array value re-order', function(assert) {
        var $container = $('#container-3');
        var $sectionParts = $('ul', $container);
        var $firstPart;
        var $thirdPart;
        var index;

        QUnit.expect(7);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        $firstPart = $sectionParts.find('li:nth-child(1)');
        $thirdPart = $sectionParts.find('li:nth-child(3)');
        assert.equal($firstPart.find('[data-bind="href"]').text(), model.testParts[0].assessmentSections[0].sectionParts[0].href, 'the first item contains the value');
        assert.equal($thirdPart.find('[data-bind="href"]').text(), model.testParts[0].assessmentSections[0].sectionParts[2].href, 'the third item contains the value');

        //reorder from 0123 to 0213
        $firstPart.after($thirdPart);
        $sectionParts.trigger('change');

        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[1].href, "http:\/\/tao26.localdomain\/bertao.rdf#i138356893768139", 'the model value order has been updated');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[2].href, "http:\/\/tao26.localdomain\/bertao.rdf#i138356893796686", 'the model value order has been updated');

        $sectionParts.find('li:nth-child(2)').find('[data-bind="href"]').text('toto').trigger('change');
        index = $sectionParts.find('li:nth-child(2)').attr('data-bind-index');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[1].href, "toto", 'the model value order has been updated');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[1].index, 1, 'the model value index has been updated');
    });

    QUnit.test('Array value remove', function(assert) {
        var $container = $('#container-3');
        var $sectionParts = $('ul', $container);
        var $firstPart;

        QUnit.expect(8);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        $firstPart = $sectionParts.find('li:nth-child(1)');

        strictEqual(model.testParts[0].assessmentSections[0].sectionParts.length, 4, 'model length has been updated');

        $firstPart.trigger('delete').remove();

        strictEqual(model.testParts[0].assessmentSections[0].sectionParts.length, 3, 'model length has been updated');
        strictEqual(model.testParts[0].assessmentSections[0].sectionParts[0].index, 0, 'model element has been removed');
        strictEqual($sectionParts.find('li:nth-child(1)').data('bind-index'), '0', 'the node index is up to date');
        assert.equal($sectionParts.find('li:nth-child(1) [data-bind="href"]').text(), "http:\/\/tao26.localdomain\/bertao.rdf#i138356893796686", 'the model value order has been updated');

        //test rebinding after removal
        $sectionParts.find('li:nth-child(1)  [data-bind="href"]').text('http://new.url').trigger('change');

        assert.equal($sectionParts.find('li:nth-child(1) [data-bind="href"]').text(), 'http://new.url', 'the model value has changed');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[0].href, 'http://new.url', 'the model value has changed');
    });

    QUnit.test('Array value add', function(assert) {
        var $container = $('#container-4');
        var $sectionParts = $('ul', $container);
        var $newSection;

        QUnit.expect(4);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        $newSection = $("<li><span data-bind='identifier'>sectionpart-5</span><span data-bind='href'>http://new.rdf#test</span></li>");

        $sectionParts.append($newSection).trigger('add');

        strictEqual(model.testParts[0].assessmentSections[0].sectionParts.length, 5, 'model length has been updated');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[4].identifier, 'sectionpart-5', 'model element has been added');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[4].href, 'http://new.rdf#test', 'model element has been added');
    });

    QUnit.test('Add value which is not represented in the model', function(assert) {
        var $container = $('#container-4');
        var $sectionParts = $('ul', $container);
        var $newSection;
        var databinder;

        QUnit.expect(2);

        assert.ok($container.length === 1, 'Test the fixture is available');

        $newSection = $("<li>" +
            "<input checked='checked' type='checkbox' value='true' data-bind-encoder='boolean' data-bind='visible'>" +
            "</li>");

        $sectionParts.append($newSection);

        databinder = new DataBinder($container, model);
        databinder.bind();

        assert.equal(model.testParts[0].assessmentSections[0].sectionParts[3].visible, true, 'new element has been added');
    });

    QUnit.test('Array value filter', function(assert) {
        var $container = $('#container-5');
        var $sectionParts = $('ul', $container);

        QUnit.expect(2);

        model.testParts[0].assessmentSections[0].sectionParts[0]['qti-type'] = 'assessmentItemRef';
        model.testParts[0].assessmentSections[0].sectionParts[1]['qti-type'] = 'assessmentSectionRef';
        model.testParts[0].assessmentSections[0].sectionParts[2]['qti-type'] = 'assessmentItemRef';
        model.testParts[0].assessmentSections[0].sectionParts[3]['qti-type'] = 'assessmentSectionRef';

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model, {
            filters: {
                'isItemRef': function(value) {
                    return value['qti-type'] && value['qti-type'] === 'assessmentItemRef';
                }
            }
        }).bind();

        strictEqual($sectionParts.find('li').length, 2, 'only filtered values has been assigned');
    });

    QUnit.test('Rm binding', function(assert) {
        var $container = $('#container-6');

        QUnit.expect(5);

        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        assert.equal(model.title, 'testTitle', 'The title attribute is present');
        $container.find('input:first-child').trigger('change');
        assert.ok(typeof model.title === 'undefined', 'The title attribute has been removed');

        assert.equal(model.testParts[0].assessmentSections[0].sectionParts.length, 4, 'The section parts contains 4 elements');
        $container.find('input:last-child').trigger('change');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts.length, 3, 'A section parts has been remvoved');
    });

    QUnit.asyncTest('Deleter binding', function(assert) {
        var $container = $('#container-7');
        var $3rdDeleter;

        QUnit.expect(6);
        assert.ok($container.length === 1, 'Test the fixture is available');

        new DataBinder($container, model).bind();

        assert.equal($container.find('[data-bind-index]').length, 4, 'All elements have been created');
        assert.equal(model.testParts[0].assessmentSections[0].sectionParts.length, 4, 'The model contains the 4 section parts');

        $3rdDeleter = $container.find('[data-bind-index=2] [data-delete]');
        assert.equal($3rdDeleter.length, 1, 'The 3rd element containts a deleter');

        //mock the behavior of ui/deleter
        $container.on('delete.deleter', function() {
            $container.on('delete.binder', function(e, newModel) {
                assert.equal(newModel.testParts[0].assessmentSections[0].sectionParts.length, 3, 'The model contains now one less section part');
            });
            $container.on('deleted.deleter', function() {
                //we simulate the actual remove after trigerring the event
                setTimeout(function() {
                    assert.equal($container.find('[data-bind-index]').length, 3, 'The element is removed from the DOM');
                    QUnit.start();
                }, 10);
            });

            $3rdDeleter.parent()
                .trigger('deleted.deleter')
                .remove();
        });

        $3rdDeleter.parent().trigger('delete', [true]);
    });
});
