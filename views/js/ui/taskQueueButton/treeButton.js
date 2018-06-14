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
 * A button component used to trigger lengthy action from the tree
 *
 * @example
 * treeTaskButtonFactory({
 *    icon : 'export',
 *    label : __('Export CSV'),
 *    taskQueue : taskQueue,
 *    taskCreationUrl : 'the/url/to/task/creation/service',
 * }).render($container).start();
 *
 * @example defer the definition of the taskCreationUrl later:
 * var button = treeTaskButtonFactory({
 *    icon : 'export',
 *    label : __('Export CSV'),
 *    taskQueue : taskQueue
 * }).render($container).start();
 * button.setTaskConfig({taskCreationUrl : 'the/url/to/task/creation/service'}).start();
 *
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/feedback',
    'ui/component',
    'layout/loading-bar',
    'ui/taskQueueButton/taskable',
    'tpl!ui/taskQueueButton/tpl/treeButton',
    'css!ui/taskQueueButton/css/treeButton'
], function ($, _, __, feedback, component, loadingBar, makeTaskable, buttonTpl) {
    'use strict';

    var _defaults = {
        icon : 'property-advanced',
        label : 'OK'
    };

    var buttonApi = {
        /**
         * Start the button spinning
         * @returns {treeTaskButton}
         */
        start : function start(){
            this.createTask();
            this.setState('started', true);
            this.trigger('start');
            return this;
        },
        /**
         * Stop the button spinning
         * @returns {treeTaskButton}
         */
        stop : function stop(){
            if(this.is('started')){
                this.setState('started', false);
                this.trigger('stop');
            }
            return this;
        }
    };

    /**
     * Create a button that will trigger a task creation when clicked
     *
     * @param {Object} config - the component config
     * @param {String} config.icon - the button icon
     * @param {String} config.label - the button's label
     * @param {Object} config.taskQueue - the task queue model to be used
     * @param {String} config.taskCreationUrl - endpoint to create a task
     * @param {Object|Function} [config.taskCreationData] - the parameters that will be send to the task creation request
     * @param {JQuery} [config.taskReportContainer] - the container where the inline report can be printed to
     * @return {treeTaskButton} the component
     *
     * @event start - When the button starts spinning
     * @event stop - When the button stops spinning
     * @event finished - When task is finished within the polling duration allowed by the task queue model
     * @event enqueued - when task has not time to finish within the polling duration allowed by the task queue model
     */
    return function treeTaskButtonFactory(config) {
        var initConfig = _.defaults(config || {}, _defaults);

        /**
         * @typedef {treeTaskButton} the component
         */
        return makeTaskable(component(buttonApi))
            .on('finished', function(){
                this.stop();
            })
            .on('enqueued', function(){
                this.stop();
            })
            .setTemplate(buttonTpl)
            .init(initConfig);
    };

});