
/**
 * Common translation method.
 * i18n_tr var is set globally in the messages_po.js file, it contains the JSON translation of the selected language
 * @see /locales/#lang#/messages_po.js
 * 
 * @param {String} message should be the string in the default language (usually english) used as the key in the gettext translations  
 * @return {String} translated message 
 */
var __ = function(message){
	if(!i18n_tr){
		return message;
	}
	if(i18n_tr[message]){
		return i18n_tr[message];
	}
	return message;
}
