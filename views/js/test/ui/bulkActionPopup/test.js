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
    'ui/bulkActionPopup'
], function($, _, bulkActionPopup){
    'use strict';

    QUnit.module('component');

    QUnit.test('render', 0, function(assert){

        var $dummy = $('<div class="dummy" />');
        var $container = $('#fixture-1').append($dummy);
        var config = {
            renderTo : $container,
            actionName : 'Resume Test Session',
            resourceType : 'test taker',
            categoriesDefinitions : [
                {
                    id : 'reason1',
                    label : 'Reason 1'
                },
                {
                    id : 'reason2',
                    label : 'Reason 2'
                },
                {
                    id : 'reason3',
                    placeholder : 'Reason 3'
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
            ,
            comment : true,
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
        var instance;

        instance = bulkActionPopup(config);

    });

});
