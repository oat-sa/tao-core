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
    'util/url',
    'core/taskQueue/taskQueueModel'
], function (urlHelper, taskQueueModelFactory) {
    'use strict';

    /**
     * Returns the singleton task queue preconfigured with the tao task queue REST API.
     */
    return taskQueueModelFactory({
        url : {
            get: urlHelper.route('get', 'TaskQueueWebApi', 'tao'),
            archive: urlHelper.route('archive', 'TaskQueueWebApi', 'tao'),
            all : urlHelper.route('getAll', 'TaskQueueWebApi', 'tao'),
            download : urlHelper.route('download', 'TaskQueueWebApi', 'tao')
        },
        pollSingleIntervals : [
            {iteration: 3, interval:1000}
        ],
        pollAllIntervals : [
            {iteration: 1, interval:8000},
            {iteration: 0, interval:5000}
        ]
    });
});