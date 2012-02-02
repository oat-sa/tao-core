if(typeof(tao) == 'undefined'){
    tao = {};
}

/**
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package wfEngine
 * @subpackage views
 * @namespace wfApi
 * 
 * This file provides an ajax wrapper build on the top of the jquery
 * library.
 * 
 * @author Cédric Alfonsi, <taosupport@tudor.lu>
 * @version 0.1
 */
tao.ajaxWrapper = {};

/**
 * The ajax call
 */
tao.ajaxWrapper.ajax = function(options)
{
    var successCallback = tao.ajaxWrapper.defaultSuccessCallback;
    var errorCallback = tao.ajaxWrapper.defaultErrorCallback;
    if(options){
        if(typeof options.success != 'undefined'){
            successCallback = options.success;
            delete options.success;
        }
        if(typeof options.error != 'undefined'){
            errorCallback = options.error;
            delete options.error;
        }
    }
    //modify options, with jquery > 1.5 use $.ajax(...).success or $.ajax(...).error
    options.success = function(result){
        tao.ajaxWrapper.successCallback(result, successCallback, errorCallback);
    };
    
    return $.ajax(options);
};

/**
 * The tao ajax success callback
 */
tao.ajaxWrapper.successCallback = function(result, successCallback, errorCallback)
{
    //Extract the result server data
    var resultData = typeof result.data != 'undefined' ? result.data : null;
    var resultType = typeof result.type != 'undefined' ? result.type : null;
    var resultMsg = typeof result.message != 'undefined' ? result.message : null;
    
    //The result type has to be defined
    if(resultType==null){
        throw new Error ('tao.ajaxWrapper::successCallback, an error occured, the result server has to contain a "type"');
    }
    
    //If the result server is an exception
    if(resultType=='Exception'){
        errorCallback(result);
    }
    //Else fire the succcess callback
    else{
        successCallback(resultData);
    }
    
};

/**
 * The tao ajax default success callback
 */
tao.ajaxWrapper.defaultSuccessCallback = function (){
    console.log('DEFAULT SUCCESS CALLBACK');
};

/**
 * The tao ajax default error callback
 */
tao.ajaxWrapper.defaultErrorCallback = function (result){
    throw new Error (result.message);
};
