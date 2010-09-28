/**
 * TAO API 
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 * 
 * @require jquery >= 1.4.0 {@link http://www.jquery.com}
 *
 */

/**
 * The TaoStack class
 */
function TaoStack(){
	

/* ENVIRONMENT */
	
	/**
	 * @var {Object} environment data to communicate with the server
	 */
	this.environment = {
		'url' 		: '/tao/',		// the url to the server
		'params': {					// the key/values to send to the server at each communication 
			'processUri' : false,	
			'itemUri'	 : false,
			'subjectUri' : false
		}	
	};
	
	this.pushSettings = {
		'method' 		: 'post',	//HTTP method to push the data (get|post)
		'async'			: true,		//if the request is asynchrone 
		'clearAfter'	: true		//if the variables stacks are cleared once pushed
	};
	
	/**
	 * Initialize the environment
	 * @param {String} url
	 * @param {Object} params
	 */
	this.initEnvironment = function(url, params, settings){
		//test url
		if(/(\/[-a-z\d%_.~+]*)*(\?[;&a-z\d%_.~+=-]*)?(\#[-a-z\d_]*)?$/.test(url)){

			this.environment.url = url;		//set url
			
			if($.isPlainObject(params)){	//set parameters
				for(key in params){
					if(isScalar(params[key])){
						this.environment.params[key] = params[key]+''; 
					}
				}
			}
			if($.isPlainObject(settings)){	//set push settings
				if(settings.method){
					if(/^get|post$/i.test(settings.method)){
						this.pushSettings.method = settings.method;
					}
				}
				if(settings.async === false){
					this.pushSettings.async = false;
				}
				if(settings.clearAfter === false){
					this.pushSettings.clearAfter = false;
				}
			}
		}
	};
	
	/**
	 * push all the data to the server
	 */
	this.push = function(){
		
		var data = this.environment.params
		data['taoVars'] = this.taoVars;
		data['userVars'] = this.userVars;
		
		var instance = this;
		console.log(this.environment.url);
		$.ajax({
			'url'  		: this.environment.url,
			'type' 		: this.pushSettings.method,
			'async'		: this.pushSettings.async,
			'data' 		: this.environment.params,
			'dataType'  	: 'json',
			'success' 	: function(data){
				if(data.saved){
					if(instance.pushSettings.clearAfter){
						instance.taoVars  = new Object();
						instance.userVars = new Object();
					}
				}
			}
		});
	};
	
/* TAO Variables */
	
	/**
	 * @var {Object} contains the tao vars 
	 */
	this.taoVars = new Object();
	
	/**
	 * @param {String} key
	 * @return {String|int|float|boolean} value (false if the key is not found)
	 */
	this.getTaoVar = function(key){
		return (this.taoVars[key]) ? this.taoVars[key] : false;
	};
	
	/**
	 * @param {String} key
	 * @param {String|int|float|boolean} value
	 */
	this.setTaoVar = function(key, value){
		if(isScalar(value)){
			this.taoVars[key] = value;
		}
	};
	
/* Custom Variables */
	
	/**
	 * @var {Object} contains the user custom vars 
	 */
	this.userVars = new Object();
	
	/**
	 * @param {String} key
	 * @return {String|int|float|boolean} value (false if the key is not found)
	 */
	this.getUserVar = function(key){
		return (this.userVars[key]) ? this.userVars[key] : false;
	};
	
	/**
	 * @param {String} key
	 * @param {String|int|float|boolean} value
	 */
	this.setUserVar = function(key, value){
		if(isScalar(value)){
			this.userVars[key] = value;
		}
	};
}


/**
 * Utility function to check if a value is a scalar
 * @param {mixed} value
 * @return {bool} true if it's a scalar
 */
function isScalar(value){
	switch((typeof value).toLowerCase()){
		case 'string':
		case 'number':
		case 'boolean':
			return true;
			
		default: 
			return false;
	}
	return false;
}
