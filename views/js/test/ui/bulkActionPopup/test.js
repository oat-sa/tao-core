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
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'ui/bulkActionPopup',
    'ui/cascadingComboBox',
    'lib/simulator/jquery.simulate'
], function($, _, bulkActionPopup, cascadingComboBox){
    'use strict';

    QUnit.module('Bulk Action Popup');

    QUnit.test('render (all options)', function(assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            categoriesSelector: cascadingComboBox({
                categoriesDefinitions: [
                    {
                        id: 'reason1',
                        placeholder: 'Reason 1'
                    },
                    {
                        id: 'reason2',
                        placeholder: 'Reason 2'
                    },
                    {
                        id: 'reason3',
                        placeholder: 'Reason 3'
                    }
                ],
                categories : [
                    {
                        id : 'optionA',
                        label : 'option A',
                        categories : [
                            {
                                id : 'optionA1',
                                label : 'option A-1',
                                categories : [
                                    {id : 'option A1a', label : 'option A-1-a'},
                                    {id : 'option A1b', label : 'option A-1-b'},
                                    {id : 'option A1c', label : 'option A-1-c'}
                                ]
                            },
                            {
                                id : 'optionA2',
                                label : 'option A-2',
                                categories : [
                                    {id : 'option A2a', label : 'option A-2-a'},
                                    {id : 'option A2b', label : 'option A-2-b'}
                                ]
                            },
                            {
                                label : 'option A-3'
                            }
                        ]
                    },
                    {
                        id : 'optionB',
                        label : 'option B',
                        categories : [
                            {id : 'option B1', label : 'option B-1'},
                            {id : 'option B2', label : 'option B-2'},
                            {id : 'option B3', label : 'option B-3'},
                            {id : 'option B4', label : 'option B-4'}
                        ]
                    },
                    {
                        id : 'option_C',
                        label : 'option C'
                    }
                ]
            }),
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                },
                {
                    id : 'uri_ns#i0000002',
                    label : 'Test Taker 2'
                },
                {
                    id : 'uri_ns#i0000003',
                    label : 'Test Taker 3'
                },
                {
                    id : 'uri_ns#i0000004',
                    label : 'Test Taker 4'
                },
                {
                    id : 'uri_ns#i0000005',
                    label : 'Test Taker 5'
                },
                {
                    id : 'uri_ns#i0000006',
                    label : 'Test Taker 6'
                },
                {
                    id : 'uri_ns#i0000007',
                    label : 'Test Taker 7'
                },
                {
                    id : 'uri_ns#i0000008',
                    label : 'Test Taker 8'
                },
                {
                    id : 'uri_ns#i0000009',
                    label : 'Test Taker 9'
                },
                {
                    id : 'uri_ns#i0000010',
                    label : 'Test Taker 10'
                },
                {
                    id : 'uri_ns#i0000011',
                    label : 'Test Taker 11'
                },
                {
                    id : 'uri_ns#i0000012',
                    label : 'Test Taker with exessiiiiiiiiiiiiive loooooooooooooong loooooooooooooong label'
                }
            ],
            deniedResources : [
                {
                    id : 'uri_ns#i1000001',
                    label : 'Test Taker a',
                    reason : 'too tired'
                },
                {
                    id : 'uri_ns#i1000002',
                    label : 'Test Taker b',
                    reason : 'too sleepy'
                },
                {
                    id : 'uri_ns#i1000003',
                    label : 'Test Taker c',
                    reason : 'too affraid'
                },
                {
                    id : 'uri_ns#i1000004',
                    label : 'Test Taker d',
                    reason : 'does not want to'
                }
            ]
        };
        var $element;
        var instance = bulkActionPopup(config);
        assert.equal($container[0], instance.getContainer()[0], 'container ok');

        $element = $container.children('.bulk-action-popup');
        assert.equal($element.length, 1, 'element ok');
        assert.equal($element.find('.applicables li').length, 12, 'allowed resources are displayed');
        assert.equal($element.find('.no-applicables li').length, 4, 'denied resources are displayed');
        assert.equal($element.children('.reason').length, 1, 'the reason box is displayed');
    });

    QUnit.test('render (without reason)', function(assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : false,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                },
                {
                    id : 'uri_ns#i0000002',
                    label : 'Test Taker 2'
                },
                {
                    id : 'uri_ns#i0000003',
                    label : 'Test Taker 3'
                },
                {
                    id : 'uri_ns#i0000004',
                    label : 'Test Taker 4'
                },
                {
                    id : 'uri_ns#i0000005',
                    label : 'Test Taker 5'
                },
                {
                    id : 'uri_ns#i0000006',
                    label : 'Test Taker 6'
                },
                {
                    id : 'uri_ns#i0000007',
                    label : 'Test Taker 7'
                },
                {
                    id : 'uri_ns#i0000008',
                    label : 'Test Taker 8'
                },
                {
                    id : 'uri_ns#i0000009',
                    label : 'Test Taker 9'
                },
                {
                    id : 'uri_ns#i0000010',
                    label : 'Test Taker 10'
                },
                {
                    id : 'uri_ns#i0000011',
                    label : 'Test Taker 11'
                },
                {
                    id : 'uri_ns#i0000012',
                    label : 'Test Taker with exessiiiiiiiiiiiiive loooooooooooooong loooooooooooooong label'
                }
            ],
            deniedResources : [
                {
                    id : 'uri_ns#i1000001',
                    label : 'Test Taker a',
                    reason : 'too tired'
                },
                {
                    id : 'uri_ns#i1000002',
                    label : 'Test Taker b',
                    reason : 'too sleepy'
                },
                {
                    id : 'uri_ns#i1000003',
                    label : 'Test Taker c',
                    reason : 'too affraid'
                },
                {
                    id : 'uri_ns#i1000004',
                    label : 'Test Taker d',
                    reason : 'does not want to'
                }
            ]
        };
        var $element;
        var instance = bulkActionPopup(config);
        assert.equal($container[0], instance.getContainer()[0], 'container ok');

        $element = $container.children('.bulk-action-popup');
        assert.equal($element.length, 1, 'element ok');
        assert.equal($element.find('.applicables li').length, 12, 'allowed resources are displayed');
        assert.equal($element.find('.no-applicables li').length, 4, 'denied resources are displayed');
        assert.equal($element.children('.reason').length, 0, 'the reason box is displayed');

    });

    QUnit.asyncTest('cancel (click)', function(assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('cancel', function(){
                assert.ok(true, 'canceled');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });

        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');

        $container.find('.cancel').click();
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.asyncTest('ok (click)', function(assert){
        var theReason = 'The Reason.';
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('ok', function(state){
                assert.equal(state.comment, theReason, 'the reason has been sent');
                assert.ok(true, 'ok !');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });

        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        $container.find('.done').click();
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.asyncTest('cancel (api)', function(assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('cancel', function(){
                assert.ok(true, 'canceled');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });


        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');

        instance.cancel();
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.asyncTest('ok (api)', function(assert){
        var theReason = 'The Reason.';
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('ok', function(state){
                assert.equal(state.comment, theReason, 'the reason has been sent');
                assert.ok(true, 'ok !');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });

        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        assert.equal(instance.validate(), true, 'The dialog has been validated');
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.asyncTest('cancel (shortcut)', function(assert){

        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('cancel', function(){
                assert.ok(true, 'canceled');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });

        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');

        $container.simulate('keydown', {
            charCode: 0,
            keyCode: 27,
            which: 27,
            code: 'esc',
            key: '',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.asyncTest('ok (shortcut)', function(assert){
        var theReason = 'The Reason.';
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('ok', function(state){
                assert.equal(state.comment, theReason, 'the reason has been sent');
                assert.ok(true, 'ok !');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });

        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        $container.simulate('keydown', {
            charCode: 0,
            keyCode: 13,
            which: 13,
            code: 'enter',
            key: '',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.asyncTest('disabled shortcut', function(assert){
        var $container = $('#fixture-1');
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            reason : true,
            allowShortcuts: false,
            allowedResources : [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config)
            .on('cancel', function(){
                assert.ok(true, 'canceled');
                QUnit.start();
            }).on('destroy', function(){
                assert.ok(true, 'destroyed');
                QUnit.start();
            });

        QUnit.stop(1);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');

        $container.simulate('keydown', {
            charCode: 0,
            keyCode: 27,
            which: 27,
            code: 'esc',
            key: '',
            ctrlKey: false,
            shiftKey: false,
            altKey: false,
            metaKey: false
        });
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element has not been removed');

        instance.cancel();
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.test('requiredReasonEmpty (click)', function(assert){

        var theReason = '';
        var $container = $('#fixture-1');
        var config = {
            renderTo: $container,
            actionName: 'Resume Test Session',
            resourceType: 'test taker',
            reason: true,
            requiredReason: true,
            allowedResources: [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config);

        QUnit.expect(5);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        $container.find('.done').click();
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element is not removed');

        assert.equal($container.find('.feedback-error', $container).length, 1, 'the interaction has error');
    });

    QUnit.test('requiredReasonEmpty (api)', function(assert){

        var theReason = '';
        var $container = $('#fixture-1');
        var config = {
            renderTo: $container,
            actionName: 'Resume Test Session',
            resourceType: 'test taker',
            reason: true,
            requiredReason: true,
            allowedResources: [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config);

        QUnit.expect(6);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        assert.equal(instance.validate(), false, 'The dialog cannot be validated');
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element is not removed');

        assert.equal($container.find('.feedback-error', $container).length, 1, 'the interaction has error');
    });

    QUnit.test('requiredReasonFilled (click)', function(assert){

        var theReason = 'reason';
        var $container = $('#fixture-1');
        var config = {
            renderTo: $container,
            actionName: 'Resume Test Session',
            resourceType: 'test taker',
            reason: true,
            requiredReason: true,
            allowedResources: [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config);

        QUnit.expect(5);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        $container.find('.done').click();
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.find('.feedback-error', $container).length, 0, 'the interaction hasn\'t error');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });

    QUnit.test('requiredReasonFilled (api)', function(assert){

        var theReason = 'reason';
        var $container = $('#fixture-1');
        var config = {
            renderTo: $container,
            actionName: 'Resume Test Session',
            resourceType: 'test taker',
            reason: true,
            requiredReason: true,
            allowedResources: [
                {
                    id : 'uri_ns#i0000001',
                    label : 'Test Taker 1'
                }
            ]
        };

        var instance = bulkActionPopup(config);

        QUnit.expect(6);

        assert.equal($container[0], instance.getContainer()[0], 'container ok');
        assert.equal($container.children('.bulk-action-popup').length, 1, 'element ok');
        $container.find('textarea').text(theReason).change();

        assert.equal(instance.validate(), true, 'The dialog has been validated');
        assert.equal($container[0], instance.getContainer()[0], 'container is still there');
        assert.equal($container.find('.feedback-error', $container).length, 0, 'the interaction hasn\'t error');
        assert.equal($container.children('.bulk-action-popup').length, 0, 'element is removed');
    });
});
