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
 * Copyright (c) 2016-2018 (original work) Open Assessment Technologies SA ;
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
        var controllerOptions = {};
        var controllerPath = loaderScript.getAttribute('data-controller');
        var params = loaderScript.getAttribute('data-params');
        try{
            controllerOptions = JSON.parse(params);
        } catch(err){
            controllerOptions = {};
        }
        window.require([controllerPath], function(controller) {
            var startController = function startController(){
                if(!window.started){
                    window.started = true;
                    controller.start(controllerOptions);
                }
            };
            document.addEventListener('readystatechange', startController, false);
            if (document.readyState === 'complete') {
                startController();
            }
        });
    };

    //always start to load the config
    window.require([configUrl], function() {

        //define the global loading mechanism
        if(!window.loadBundles){
            //keep tracl of loaded bundles, even if require does it,
            //this prevent some unecessary cycles
            window.loaded = {};

            /**
             * Loading entry point for inter bundle dependency,
             * always take the bundles from the params and window.bundles
             * @param {String[]} [bundles] - an optional list of bundle to load
             */
            window.loadBundles = function loadBundles(bundles){
                bundles = bundles || [];
                bundles = bundles.concat(window.bundles)
                bundles = bundles.filter( function(item, index){
                    return item && bundles.indexOf(item) === index && window.loaded[item] !== true;
                });
                require(bundles, function(){
                    bundles.forEach( function( item ) {
                        window.loaded[item] = true;
                    });
                    loadController();
                });
            }
        }

        if(bundle || (window.bundles && window.bundles.length)) {
            window.loadBundles([bundle]);
        } else {
            loadController();
        }
    });
})();
