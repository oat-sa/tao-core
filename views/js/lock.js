/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2013
 *  *
 */

function Lock (resourceUri){
	this.uri = resourceUri;
	
}

/*
 *
 * @param {type} callback
 * @returns {undefined}
 */
Lock.prototype.release = function (successCallBack, failureCallBack){

    //this.url = _url('tao','lock','release'); //todo _url ?
    this.url = root_url+'/tao/lock/release';
 
    var options = {data: data,
		   type: 'POST',
		   dataType: 'json'};
    $.ajax(this.url, options).done(function(retData, textStatus, jqxhr){
	
	alert('lock removed');
	successCallBack();
    }).fail(function(jqxhr){
	alert(__('A problem occured when releasing the lock'));
    });
}

