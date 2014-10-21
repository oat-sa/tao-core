/**
 * @author Jérôme Bogaert <jerome@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'i18n', 'helpers', 'layout/section', 'ui/itemsmgr'], function($, __, helpers, section) {
    'use strict';

    /**
     * Edit a user (shows the edit section)
     * @param {String} uri - the user uri
     */
    var editUser = function editUser(uri) {
        section
            .get('edit_user')
            .loadContentBlock(helpers._url('edit', 'Users', 'tao'), {uri : uri})
            .show();
    };

    /**
     * Removes a user
     * @param {String} uri - the user uri
     */
	var removeUser = function removeUser(uri){
        //TODO use a confirm component
        //TODO run an ajax request and show a feedback
		if (window.confirm(__('Please confirm user deletion'))) {
			window.location = helpers._url('delete', 'Users', 'tao',  {uri : uri});
		}
	};

    /**
     * The user index controller
     * @exports controller/users/index
     */
    return {
        start : function(){

            //initialize the user manager component
            $('#user-list').itemsmgr({
                'url': helpers._url('data', 'Users', 'tao'),
                'actions' : {
                    'edit': editUser,
                    'remove': removeUser
                },
                'model' : [
                    {
                        id : 'login',
                        label : __('Login'),
                        sortable : true
                    },{
                        id : 'name',
                        label : __('Name'),
                        sortable : true
                    },
                    {
                        id : 'email',
                        label : __('Email'),
                        sortable : true
                    },{
                        id : 'role',
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
                    }
                ]
            });
        }
    };
});
