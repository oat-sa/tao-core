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
 * Copyright (c) 2014-2019 (original work) Open Assessment Technlogies SA
 *
 */


const fs      = require('fs');
const path    = require('path');
const { URL } = require('url');

/**
 * Main configuration to run tests
 *
 * grunt connect:test qunit:extension --extension=taoQtiTest
 * grunt connect:test qunit:single --text=/taoQtiTest/views/js/test/runner/qti/test.html
 *
 * @param {Object} grunt - grunt instance
 * @returns {void}
 */
module.exports = function(grunt) {

    const root           = grunt.option('root');
    const testPort       = grunt.option('testPort');
    const testUrl        = grunt.option('testUrl');
    const livereloadPort = grunt.option('livereloadPort');
    const reportOutput   = grunt.option('reports');
    const ext            = require(`${root}/tao/views/build/tasks/helpers/extensions`)(grunt, root);
    const baseUrl        = `http://${testUrl}:${testPort}`;
    const testRunners    = `${root}/tao/views/js/test/**/test.html`;


    //extract unit tests  from FS to URL
    const extractTests = function extractTests(){
        return grunt.file.expand([testRunners]).map(function(testPath){
            return testPath.replace(root, baseUrl);
        });
    };

    /**
     * Creates a namespace from the test url (the reports needs namspaces)
     * For example :
     * From 'http://127.0.0.1:8082/tao/views/js/test/core/eventifier/test.html'
     * To   'tao.core.eventifier'
     * Expecting the following URL pattern '${baseUrl}/${extensionName}/views/js/test/${path}/test.html'
     *
     * @param {String} url - the URl of the test
     * @param {String} [joinWith=.] - the glue character for the namespace, replacing the slashes
     * @param {String} [suffix] - namespace suffix
     * @returns {String} the test namespace
     */
    const testUrlToNamespace = function testUrlToNamespace(url = '', joinWith = '.', suffix) {

        const namespaceChunks = path.dirname(new URL(url).pathname)
                .replace('views/js/test', '')
                .split('/')
                .filter( entry => !!entry );

        if(suffix){
            namespaceChunks.push(suffix);
        }
        return namespaceChunks.join(joinWith);
    };

    grunt.config.merge({

        qunit : {

            //global options
            options : {
                inject: './config/chrome-bridge.js',
                timeout: 30000,
                puppeteer : {
                    ignoreHTTPSErrors: true,
                    timeout: 30000,
                    args: [ '--no-sandbox', '--disable-gpu', '--disable-popup-blocking' , '--autoplay-policy=no-user-gesture-required'],
                    defaultViewport:  {
                        width: 1280,
                        height: 720,
                        deviceScaleFactor: 1
                    }
                }
            },

            //run a single test (requires the options test=${testUrl})
            single : {
                options : {
                    console: true,
                    urls:    [baseUrl + grunt.option('test')]
                }
            },

            //tests for the tao extension
            taotest : {
                options : {
                    console : true,
                    urls : extractTests()
                }
            }
        },

        //convert QUnit report to JUnit reports for Jenkins
        'qunit_junit' : {
            options : {
                dest : reportOutput,
                fileNamer : url => testUrlToNamespace(url, '-'),
                classNamer : (moduleName, url) => {
                    //moduleName is a sentence so make it camelcase
                    const moduleNameSuffix = moduleName
                        .toLowerCase()
                        .replace(/ (.)/g, (fullMatch,firstMatch) => firstMatch.toUpperCase() );

                    return testUrlToNamespace(url, '.', moduleNameSuffix);
                }
            }
        },

        //starts a static web server to serve assets for tests and the requirejs config
        connect : {
            options: {
                protocol : 'http',
                hostname : testUrl,
                port: testPort,
                base: root,
                middleware: function(connect, options, middlewares) {
                    const rjsConfig = require('../config/requirejs.build.json');
                    rjsConfig.baseUrl = `${baseUrl}/tao/views/js`;
                    ext.getExtensions().forEach(function(extension){
                        rjsConfig.paths[extension] = `../../../${extension}/views/js`;
                        rjsConfig.paths[`${extension}Css`] = `../../../${extension}/views/css`;
                    });

                    const extraPaths = ext.getExtensionsExtraPaths();
                    rjsConfig.paths = {...rjsConfig.paths, ...extraPaths};

                    // inject a mock for the requirejs config
                    middlewares.unshift(function(req, res, next) {
                        if (/\/tao\/ClientConfig\/config/.test(req.url)){
                            res.writeHead(200, { 'Content-Type' : 'application/javascript'});
                            return res.end(`require.config(${JSON.stringify(rjsConfig)})`);
                        }
                        return next();
                    });

                    //allow post requests
                    middlewares.unshift(function(req, res, next) {
                        if (req.method.toLowerCase() === 'post') {
                            const filepath = path.join(options.base[0], req.url);
                            if (fs.existsSync(filepath)) {
                                fs.createReadStream(filepath).pipe(res);
                                return;
                            }
                        }
                        return next();
                    });

                    return middlewares;
                }
            },
            test : {
                options : {
                    livereload: false
                }
            },
            dev : {
                options : {
                    livereload: livereloadPort
                }
            }
        }
    });

    // test task
    grunt.registerTask('taotest', ['qunit:taotest']);
};
