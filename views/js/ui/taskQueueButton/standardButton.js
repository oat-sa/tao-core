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
 * Component to create a simple button that will command task creation
 *
 * @example minimal example to build a task
 * standardTaskButtonFactory({
 *    type : 'info',
 *    icon : 'delivery',
 *    title : __('Publish the test'),
 *    label : __('Publish'),
 *    taskQueue : taskQueue,
 *    taskCreationUrl : 'the/url/to/task/creation/service'
 * }).render($container);
 *
 * @example simple example to build a task with additional request params
 * standardTaskButtonFactory({
 *    type : 'info',
 *    icon : 'delivery',
 *    title : __('Publish the test'),
 *    label : __('Publish'),
 *    taskQueue : taskQueue,
 *    taskCreationUrl : 'the/url/to/task/creation/service',
 *    taskCreationData : {foo : 'bar}
 * }).render($container);
 *
 * @example full example using taskCreationData as a function and taskReportContainer to display the report in
 * standardTaskButtonFactory({
 *    type : 'info',
 *    icon : 'delivery',
 *    title : __('Publish the test'),
 *    label : __('Publish'),
 *    taskQueue : taskQueue,
 *    taskCreationUrl : $form.prop('action'),
 *    taskCreationData : function getTaskCreationData(){
 *        return $form.serializeArray();
 *    },
 *    taskReportContainer : $container
 * }).render($container);
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'core/promise',
    'ui/report',
    'ui/feedback',
    'layout/loading-bar',
    'ui/loadingButton/loadingButton',
    'ui/taskQueueButton/taskable'
], function ($, _, __, Promise, reportFactory, feedback, loadingBar, loadingButton, makeTaskable) {
    'use strict';

    var defaultConfig = {
    };

    /**
     * Builds a standard task creation button
     *
     * @param {Object} config - the component config
     * @param {String} config.type - the icon type (info, success, error)
     * @param {String} config.icon - the button icon
     * @param {String} config.label - the button's label
     * @param {String} [config.title] - the button's title
     * @param {String} [config.terminatedLabel] - the button's label when terminated
     * @param {Object} config.taskQueue - the task queue model to be used
     * @param {String} config.taskCreationUrl - endpoint to create a task
     * @param {Object|Function} [config.taskCreationData] - the parameters that will be send to the task creation request
     * @param {JQuery} [config.taskReportContainer] - the container where the inline report can be printed to
     * @returns {standardTaskButton} the component
     *
     * @event started - Emitted when the button is clicked and the triggered action supposed to be started
     * @event terminated - Emitted when the button action is stopped, interrupted
     * @event reset - Emitted when the button revert from the terminated stated to the initial one
     * @event finished - When task is finished within the polling duration allowed by the task queue model
     * @event enqueued - when task has not time to finish within the polling duration allowed by the task queue model
     */
    return function standardTaskButtonFactory(config) {

        var component;

        //prepare the config and
        config = _.defaults(config || {}, defaultConfig);

        //create the base loading button and make it taskable
        component = makeTaskable(loadingButton(config));

        /**
         * The component
         * @typedef {ui/component} standardTaskButton
         */
        return component.on('started', function(){
            this.createTask();
        }).on('finished', function(){
            this.terminate().reset();
        }).on('enqueued', function(){
            this.terminate().reset();
        });
    };
});
