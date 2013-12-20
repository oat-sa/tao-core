define(['i18n_tr'], function(tr){
    
    /**
     * Common translation method.
     * @see /locales/#lang#/messages_po.js
     * 
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations  
     * @returns {String} translated message 
     */
    var __ = function __(message){
        return (!tr.i18n_tr || !tr.i18n_tr[message]) ? message :  tr.i18n_tr[message];
    };


    //expose the translation function
    return __ ;
});
