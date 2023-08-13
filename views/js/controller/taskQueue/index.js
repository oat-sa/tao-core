/**
 * @author Jérôme Bogaert <jerome@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'util/url',
    'layout/section',
    'core/request',
    'ui/feedback',
    'ui/dialog/confirm',
    'ui/datatable'
], function ($, _, __, urlHelper, section, request, feedback, dialogConfirm) {
    'use strict';

    /**
     * Make a request to the server for a token-protected user action
     * @param {String} uri - the user uri
     * @param {String} action
     * @param {String} confirmMessage
     */
    var runTaskQueueAction = function runTaskQueueAction(uri, action, confirmMessage) {
        var data = {
            uri: uri
        };

        dialogConfirm(confirmMessage, function() {
            request({
                url: urlHelper.route(action, 'TaskQueueWebApi', 'tao'),
                data: data,
                method: 'POST'
            })
            .then(function(response) {
                if (response.success) {
                    feedback().success(response.message);
                }
                $('#task-queue-list').datatable('refresh');
            })
            .catch(function(err) {
                feedback().error(err);
            });
        });
    };

    /**
     * @param {string} uri
     * @param {object} row
     */
    var stopTask = function stopTask(uri, row) {
        runTaskQueueAction(uri, 'unlock', __('Please confirm stopping task %s', row.id));
    };

    /**
     * @param {string} uri
     * @param {object} row
     */
    var startTask = function startTask(uri, row) {
        runTaskQueueAction(uri, 'unlock', __('Please confirm starting task %s', row.id));
    };

    /**
     * The user index controller
     * @exports controller/taskQueue/index
     */
    return {
        start : function() {
            const $taskQueueList = $('#task-queue-list');

            section.on('show', function (section) {
                if (section.id === 'settings_task_queue') {
                    $taskQueueList.datatable('refresh');
                }
            });

            const actions = {
                start: startTask,
                stop: stopTask,
            };

            // initialize the user manager component
            $taskQueueList.on('load.datatable', function (e, dataset) {
                _.forEach(dataset.data, function(row) {
                    const lockBtn = '[data-item-identifier="' + row.id + '"] button.lock';
                    const unlockBtn = '[data-item-identifier="' + row.id + '"] button.unlock';

                    if (row.lockable) {
                        $(row.locked ? lockBtn : unlockBtn, $userList).hide();
                    } else {
                        _.forEach([lockBtn, unlockBtn], function (btn) {
                            $(btn, $taskQueueList).hide();
                        });
                    }
                });
            }).datatable({
                url: urlHelper.route('search', 'taskQueueWebApi', 'tao'),
                paginationStrategyBottom: 'pages',
                filter: true,
                actions: actions,
                model: [
                    {
                        id : 'name',
                        label : __('Name'),
                        sortable : true
                    },
                    {
                        id: 'status',
                        label: __('Status'),
                        sortable: true,
                        transform: function (value) {
                            var icon = value === 'enabled'
                                ? 'result-ok'
                                : 'lock';
                            return '<span class="icon-' + icon + '"></span> ' + value;
                        }
                    }
                ]
            });
        }
    };
});
