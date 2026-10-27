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
(() => {
    'use strict';

    const loaderScript = document.getElementById('amd-loader');
    const configUrl = loaderScript.getAttribute('data-config');
    const loaderUrl = loaderScript.getAttribute('data-main') || loaderScript.getAttribute('src');
    const loaderDirectoryIndex = loaderUrl.lastIndexOf('loader/');
    const jsBaseUrl = loaderDirectoryIndex === -1 ? '' : loaderUrl.substring(0, loaderDirectoryIndex);
    const locale = document.documentElement.getAttribute('lang') || 'en-US';
    const bundle = loaderScript.getAttribute('data-bundle');
    let configErrorDialog;

    //The client config normally provides these paths. Define the minimum here
    //so the translated config error can also be displayed when that request fails.
    window.require.config({
        baseUrl: jsBaseUrl,
        paths: {
            text: 'lib/text/text',
            json: 'lib/text/json',
            css: 'lib/require-css/css',
            lodash: '../node_modules/lodash/lodash',
            core: '../node_modules/@oat-sa/tao-core-sdk/dist/core',
            i18ntr: `../locales/${locale}`
        }
    });

    const loadController = () => {
        let controllerOptions = {};
        const controllerPath = loaderScript.getAttribute('data-controller');
        const params = loaderScript.getAttribute('data-params');

        try {
            controllerOptions = JSON.parse(params);
        } catch (err) {
            controllerOptions = {};
        }

        window.require([controllerPath], controller => {
            const startController = () => {
                if (!window.started) {
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

    /**
     * Continue bootstrapping after the client config has loaded.
     */
    const onConfigLoaded = () => {
        if (configErrorDialog) {
            configErrorDialog.destroy();
        }

        //define the global loading mechanism
        if (!window.loadBundles) {
            //keep track of loaded bundles, even if require does it,
            //this prevents some unnecessary cycles
            window.loaded = {};

            /**
             * Loading entry point for inter bundle dependency,
             * always take the bundles from the params and window.bundles
             * @param {String[]} [bundles] - an optional list of bundle to load
             */
            window.loadBundles = (bundles = []) => {
                const uniqueBundles = bundles.concat(window.bundles).filter((item, index, bundleList) =>
                    item && bundleList.indexOf(item) === index && window.loaded[item] !== true
                );

                window.require(uniqueBundles, () => {
                    uniqueBundles.forEach(item => {
                        window.loaded[item] = true;
                    });
                    loadController();
                });
            };
        }

        if (bundle || (window.bundles && window.bundles.length)) {
            window.loadBundles([bundle]);
        } else {
            loadController();
        }
    };

    //Load the translated failure dialog before requesting the client config.
    window.require(['loader/configRetry'], configRetryDialog => {
        configErrorDialog = configRetryDialog();

        window.require([configUrl], onConfigLoaded, () => {
            configErrorDialog.show();
        });
    });
})();
