/**
 * TAO API interface.
 * Provides methods to manage items.
 * 
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 * @package tao
 *
 */

var taoStack = new TaoStack();

/////////////////////
// TAO  variables //
///////////////////

/**
 * @return {bool}
 */
function getEndorsement(){
	return taoStack.getTaoVar(URI.ENDORSMENT);
}

/**
 * @param {bool} endorsement
 */
function setEndorsement(endorsement){
	taoStack.setTaoVar(URI.ENDORSMENT, (endorsement == true));
}

/**
 * @return {Object} subject
 */
function getSubject(){
	//return taoStack.getTaoVar(URI.SUBJECT);
}


/////////////////////
// user variables //
///////////////////

/**
 * @param {String} key
 * @return {String|int|float|bool}
 */
function getUserVar(key){
	return taoStack.getUserVar(key);
}

/**
 * @param {String} key
 * @param {String|int|float|bool} value
 */
function setUserVar(key, value){
	taoStack.setUserVar(key, value);
}


////////////////////////////
// EVENTS to be defined  //
//////////////////////////

/**
 * @param {Event} e
 */
function setEvent(e){
}


/////////////////////////////
// GENERIS to be defined  //
///////////////////////////


/**
 * 
 */
function createVar(){
}


/////////////////////////////
// GENERIS to be defined  //
///////////////////////////

/**
 * @return {bool}
 */
function push(){
	taoStack.push();
}

/**
 * @param {String} url
 * @param {Object} parameters
 */
function initServer(url, params, settings){
	taoStack.initEnvironment(url, params, settings);
}


