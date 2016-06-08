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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */
(function () {
    'use strict';

    var jsFeedback = document.getElementById('js-check');
    var reqFeedback = document.getElementById('requirement-check');

    var tests = [{
        name : 'ES5 Global JSON',
        test : function (){
            return 'JSON' in window && typeof JSON.parse === 'function' && typeof JSON.stringify === 'function';
        }
    }, {
        name : 'ES5 Extension',
        test : function (){
            return typeof Function.prototype.bind === 'function' && typeof String.prototype.string === 'function';
        }
    }, {
        name : 'localstorage',
        test : function (){
            return 'localStorage' in window;
        }
    }, {
        name : 'querySelector',
        test : function (){
            return 'querySelector' in window.document && 'querySelectorAll' in window.document;
        }
    }, {
        name : 'file reader',
        test : function (){
            return 'File' in window && 'FileReader' in window;
        }
    }];
    var testCounter = 0;

    //if we do jsi (basically if we are here), we hide the warning
    if(jsFeedback){
        jsFeedback.style.display = 'none';
        document.documentElement.className = document.documentElement.className.replace('no-js', '');
    }

    //if one of the test fail, we show the warning
    while(testCounter < tests.length){
        if(typeof tests[testCounter].test === 'function' && !tests[testCounter].test()){
            reqFeedback.style.display = 'block';
            break;
        }
        testCounter++;
    }
}());

