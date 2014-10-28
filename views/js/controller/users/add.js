/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define(['jquery', 'module', 'helpers', 'users'], function($, module, helpers, users) {
    'use strict';
       
    
    /**
     * The user add controller
     * @exports controller/users/add
     */    
    return {
        start : function(){
            var conf = module.config();
            var url  = helpers._url('checkLogin', 'Users', 'tao');
            users.checkLogin(conf.loginId, url);
        }
    };
});
