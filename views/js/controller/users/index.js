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
    var runUserAction = function runUserAction(uri, action, confirmMessage) {
        var data = {
            uri: uri
        };

        dialogConfirm(confirmMessage, function () {
            request({
                url: urlHelper.route(action, 'Users', 'tao'),
                data: data,
                method: 'POST'
            })
                .then(function (response) {
                    if (response.success) {
                        feedback().success(response.message);
                    }
                    $('#user-list').datatable('refresh');
                })
                .catch(function (err) {
                    feedback().error(err);
                });
        });
    };

    /**
     * Edit a user (shows the edit section)
     * @param {String} uri - the user uri
     */
    var editUser = function editUser(uri) {
        section
            .get('edit_user')
            .enable()
            .loadContentBlock(urlHelper.route('edit', 'Users', 'tao'), { uri: uri })
            .show();
    };

    /**
     * Removes a user
     * @param {String} uri - the user uri
     * @param row
     */
    var removeUser = function removeUser(uri, row) {
        runUserAction(uri, 'delete', __('Please confirm deletion of user %s', row.login));
    };

    /**
     * Locks a user
     * @param {String} uri - the user uri
     * @param row
     */
    var lockUser = function lockUser(uri, row) {
        runUserAction(uri, 'lock', __('Please confirm locking of account %s', row.login));
    };

    /**
     * Unlocks blocked user
     * @param {String} uri - the user uri
     * @param row
     */
    var unlockUser = function unlockUser(uri, row) {
        runUserAction(uri, 'unlock', __('Please confirm unlocking of account %s', row.login));
    };

    /**
     * The user index controller
     * @exports controller/users/index
     */
    return {
        start: function () {
            var $userList = $('#user-list');

            section.on('show', function (section) {
                if (section.id === 'list_users') {
                    $userList.datatable('refresh');
                }
            });

            var actions = [
                { id: 'edit', label: __('edit'), icon: 'edit', action: editUser },
                { id: 'remove', label: __('remove'), icon: 'remove', action: removeUser },
                { id: 'lock', label: __('lock'), icon: 'lock', action: lockUser },
                { id: 'unlock', label: __('unlock'), icon: 'unlock', action: unlockUser }
            ];

            // initialize the user manager component
            $userList
                .on('load.datatable', function (e, dataset) {
                    _.forEach(dataset.data, function (row) {
                        var lockBtn = '[data-item-identifier="' + row.id + '"] button.lock';
                        var unlockBtn = '[data-item-identifier="' + row.id + '"] button.unlock';
                        if (row.lockable) {
                            $(row.locked ? lockBtn : unlockBtn, $userList).hide();
                        } else {
                            _.forEach([lockBtn, unlockBtn], function (btn) {
                                $(btn, $userList).hide();
                            });
                        }
                    });
                })
                .datatable({
                    url: urlHelper.route('data', 'Users', 'tao'),
                    paginationStrategyBottom: 'pages',
                    filter: true,
                    actions: actions,
                    model: [
                        {
                            id: 'login',
                            label: __('Login'),
                            sortable: true
                        },
                        {
                            id: 'firstname',
                            label: __('First Name'),
                            sortable: true
                        },
                        {
                            id: 'lastname',
                            label: __('Last Name'),
                            sortable: true
                        },
                        {
                            id: 'email',
                            label: __('Email'),
                            sortable: true
                        },
                        {
                            id: 'roles',
                            label: __('Roles'),
                            sortable: false
                        },
                        {
                            id: 'dataLg',
                            label: __('Data Language'),
                            sortable: true,
                            visible: $userList.data('user-data-lang-enabled')
                        },
                        {
                            id: 'guiLg',
                            label: __('Interface Language'),
                            sortable: true
                        },
                        {
                            id: 'status',
                            label: __('Account status'),
                            sortable: true,
                            transform: function (value) {
                                var icon = value === 'enabled' ? 'result-ok' : 'lock';
                                return '<span class="icon-' + icon + '"></span> ' + value;
                            }
                        }
                    ]
                });
        }
    };
});
