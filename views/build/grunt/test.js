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
 * Copyright (c) 2014-2018 (original work) Open Assessment Technlogies SA
 *
 */

/**
 * Main test configuration, as well as for the TAO extension
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
module.exports = function(grunt) {
    'use strict';

    const root           = grunt.option('root');
    const testPort       = grunt.option('testPort');
    const testUrl        = grunt.option('testUrl');
    const livereloadPort = grunt.option('livereloadPort');
    const reportOutput   = grunt.option('reports');
    const ext            = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    const fs             = require('fs');
    const path           = require('path');
    const im             = require('istanbul-lib-instrument');
    const baseUrl        = 'http://' + testUrl + ':' + testPort;
    const testRunners    = root + '/tao/views/js/test/**/test.html';


    //extract unit tests  from FS to URL
    var extractTests = function extractTests(){
        return grunt.file.expand([testRunners]).map(function(testPath){
            return testPath.replace(root, baseUrl);
        });
    };

    grunt.config.merge({

        qunit : {

            //global options
            options : {
                inject: './config/chrome-bridge.js',
                timeout: 30000,
                force: true,
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

                fileNamer : function(url){
                    return url
                    .replace(testUrl + '/', '')
                    .replace('/test.html', '')
                    .replace(/\//g, '.');
                },

                classNamer : function (moduleName, url) {
                    return url
                    .replace(testUrl + '/', '')
                    .replace('views/js/test/', '')
                    .replace('/test.html', '')
                    .replace(/\//g, '.');
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

                    var bodyParser = require('body-parser');
                    var rjsConfig = require('../config/requirejs.build.json');
                    rjsConfig.baseUrl = baseUrl + '/tao/views/js';
                    ext.getExtensions().forEach(function(extension){
                        rjsConfig.paths[extension] = '../../../' + extension + '/views/js';
                        rjsConfig.paths[extension + 'Css'] = '../../../' + extension + '/views/css';
                    });


                    // inject a mock for the requirejs config
                    middlewares.unshift(function(req, res, next) {
                        if (/\/tao\/ClientConfig\/config/.test(req.url)){
                            res.writeHead(200, { 'Content-Type' : 'application/javascript'});
                            return res.end('require.config(' + JSON.stringify(rjsConfig) + ')');
                        }
                        return next();
                    });

                    //allow post requests
                    middlewares.unshift(function(req, res, next) {
                        var filepath;
                        if (req.method.toLowerCase() === 'post') {
                            filepath = path.join(options.base[0], req.url);
                            if (fs.existsSync(filepath)) {
                                fs.createReadStream(filepath).pipe(res);
                                return;
                            }
                        }
                        return next();
                    });

                    middlewares.unshift( (req, res, next) => {
                        if(/\.js$/.test(req.url) && !/js\/lib\//.test(req.url) &&  !/js\/test\//.test(req.url) ){

                            const instrumenter = im.createInstrumenter();
                            const filepath = path.join(options.base[0], req.url);
                            if (fs.existsSync(filepath)) {
                                fs.readFile(filepath, 'utf-8', (err, content) => {
                                    console.log(` ${req.url} => ${filepath} : instrumented`);
                                    res.end(instrumenter.instrumentSync(content, filepath));
                                });
                                return;
                            }
                        }
                        return next();
                    });

                    middlewares.unshift( (req, res, next) => {

                        if (req.method.toLowerCase() === 'post' && /__coverage__/.test(req.url)) {
                            console.log('received coverage ' + JSON.stringify(req.body));
                            fs.writeFile(`${reportOutput}/.cov/__coverage__`,JSON.stringify(req.body), 'utf8', function(err){

                                if(err){
                                    return next(err);
                                }
                                res.end('{ "success" : true}');
                            });
                            return;
                        }

                        return next();
                    });
 middlewares.unshift(
                    bodyParser.json()
                );

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
