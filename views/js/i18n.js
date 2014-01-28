define(['lodash', 'i18n_tr', 'context'], function(_, tr, context){
    
    var translations = tr.i18n_tr || {};
    var extensionLocales = _.map(context.extensionsLocales, function(extension){
      return extension + '_i18n';  
    });
    
    //done at load time
    require(extensionLocales, function(){
       _.forEach(arguments, function(extensionLocale){
           if(extensionLocale && extensionLocale.i18n_tr){
               translations = _.merge(translations, extensionLocale.i18n_tr);
           }
       });
    });
    
    
    /**
     * Common translation method.
     * @see /locales/#lang#/messages_po.js
     * 
     * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations  
     * @returns {String} translated message 
     */
    var __ = function __(message){
        return !translations[message] ? message :  translations[message];
    };


    //expose the translation function
    return __ ;
    
});
