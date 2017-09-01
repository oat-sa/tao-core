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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

/**
 * Basic features checks :
 *  - js enabled
 *  - ES5 features
 *  - DOM and browser API
 *
 *
 * js check hides the 'js-check' box if there's JS and remove the 'no-js' class which hide the content.
 * feature check show the 'browser-check' box as soon as one of the check fails.
 *
 * Uses old school JS to ensure it runs on old old browers.
 */
(function () {
    'use strict';

    var reqFeedback = document.getElementById('browser-check');

    var tests = [{
        name : 'ES5 Global JSON',
        test : function (){
            return 'JSON' in window && typeof JSON.parse === 'function' && typeof JSON.stringify === 'function';
        }
    }, {
        name : 'ES5 Extension',
        test : function (){
            return typeof Function.prototype.bind === 'function' && typeof String.prototype.trim === 'function';
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

    document.documentElement.className = document.documentElement.className.replace('no-js', '');

    //if one of the test fail, we show the warning
    if(reqFeedback){
        while(testCounter < tests.length){
            if(typeof tests[testCounter].test === 'function' && !tests[testCounter].test()){
                reqFeedback.style.display = 'block';
                reqFeedback.className = reqFeedback.className.replace('hidden', '');
                document.documentElement.className = document.documentElement.className + ' no-js';
                break;
            }
            testCounter++;
        }
    }
}());

