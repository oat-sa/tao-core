module.exports = function(grunt) {
    'use strict';

    var root           = grunt.option('root');
    var testPort       = grunt.option('testPort');
    var testUrl        = grunt.option('testUrl');
    var livereloadPort = grunt.option('livereloadPort');
    var reportOutput   = grunt.option('reports');
    var ext            = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var fs             = require('fs');
    var path           = require('path');
    var baseUrl        = 'http://' + testUrl + ':' + testPort;
    var testRunners    = root + '/tao/views/js/test/**/test.html';


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
                    args: [ "--no-sandbox", "--disable-gpu", "--disable-popup-blocking" ],
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
