/**
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
 * Copyright (c) 2016-2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * Bootstrap the app, start the entry controller
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
(function(){
    'use strict';

    var loaderScript = document.getElementById('amd-loader');
    var configUrl = loaderScript.getAttribute('data-config');
    var bundle  = loaderScript.getAttribute('data-bundle');

    var loadController = function loadController(){
        var started = false;
        var controllerOptions = {};
        var controllerPath = loaderScript.getAttribute('data-controller');
        var params = loaderScript.getAttribute('data-params');
        try{
            controllerOptions = JSON.parse(params);
        } catch(err){
            controllerOptions = {};
        }
        require([controllerPath], function(controller) {
            var startController = function startController(){
                if(!started){
                    started = true;
                    controller.start(controllerOptions);
                }
            };
            document.addEventListener('readystatechange', startController, false);
            if (document.readyState === 'complete') {
                startController();
            }
        });
    };
    require([configUrl], function() {
        if(bundle){
            require([bundle], loadController);
        } else {
            loadController();
        }
    });
})();
