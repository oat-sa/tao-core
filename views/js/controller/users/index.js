/**
 * @author Jérôme Bogaert <jerome@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'i18n', 'helpers', 'layout/section', 'ui/usermgr'], function($, __, helpers, section) {
    'use strict';
       
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

            section.get('edit_user').disable();

            //initialize the user manager component
            $('#user-list').usermgr({
                'url': helpers._url('data', 'Users', 'tao'),
                'edit': editUser,
                'remove': removeUser
            });
        }
    };
});
