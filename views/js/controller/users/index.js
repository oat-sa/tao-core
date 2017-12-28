/**
 * @author Jérôme Bogaert <jerome@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['module', 'jquery', 'i18n', 'helpers', 'layout/section', 'ui/feedback', 'ui/dialog/confirm', 'ui/datatable'], function (module, $, __, helpers, section, feedback, dialogConfirm) {
    'use strict';

    var runUserAction = function runUserAction(uri, action, confirmMessage) {
        var tokenName = module.config().xsrfTokenName;
        var data = {};

        data.uri = uri;
        data[tokenName] = $.cookie(tokenName);

        dialogConfirm(confirmMessage, function () {
            $.ajax({
                url : helpers._url(action, 'Users', 'tao'),
                data : data,
                type : 'POST'
            }).done(function(response) {
                if (response.deleted) {
                    feedback().success(response.message);
                } else {
                    feedback().error(response.message);
                }
                $('#user-list').datatable('refresh');
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
            .loadContentBlock(helpers._url('edit', 'Users', 'tao'), {uri : uri})
            .show();
    };

    /**
     * Removes a user
     * @param {String} uri - the user uri
     */
    var removeUser = function removeUser(uri) {
        runUserAction(uri, 'delete', __('Please confirm user deletion'));
	};

    /**
     * Blocks a user
     * @param {String} uri - the user uri
     */
    var blockUser = function blockUser(uri) {
        runUserAction(uri, 'block', __('Please confirm user blocking'));
    };

    /**
     * Reset (unblocks) a user
     * @param {String} uri - the user uri
     */
    var resetUser = function resetUser(uri) {
        runUserAction(uri, 'reset', __('Please confirm user resetting'));
    };

    /**
     * The user index controller
     * @exports controller/users/index
     */
    return {
        start : function(){
            var $userList = $('#user-list');

            section.on('show', function (section) {
                if (section.id === 'list_users') {
                    $userList.datatable('refresh');
                }
            });

            // initialize the user manager component
            $userList.datatable({
                url: helpers._url('data', 'Users', 'tao'),
                filter: true,
                actions: {
                    'edit': editUser,
                    'remove': removeUser,
                    'block': blockUser,
                    'reset': resetUser
                },
                'model': [
                    {
                        id : 'login',
                        label : __('Login'),
                        sortable : true
                    },{
                        id : 'firstname',
                        label : __('First Name'),
                        sortable : true
                    },{
                        id : 'lastname',
                        label : __('Last Name'),
                        sortable : true
                    },{
                        id : 'email',
                        label : __('Email'),
                        sortable : true
                    },{
                        id : 'roles',
                        label : __('Roles'),
                        sortable : false
                    },{
                        id : 'dataLg',
                        label : __('Data Language'),
                        sortable : true
                    },{
                        id: 'guiLg',
                        label : __('Interface Language'),
                        sortable : true
                    }, {
                        id: 'status',
                        label: __('User status'),
                        sortable: true
                    }
                ]
            });
        }
    };
});
