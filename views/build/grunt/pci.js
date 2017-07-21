module.exports = function(grunt) {
    'use strict';

    var _ = require('lodash');
    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var out         = 'output';


    grunt.config.merge({
        compilepci : {
            options: {
                options: {
                    ext : 'qtiItemPci'
                },
                src : ['../scss/*.scss', '../scss/**/*.scss', '../js/lib/jsTree/**/*.scss']
            }
        }
    });

    grunt.registerTask('compilepci', 'Compile PCIs', function(){

        grunt.log.writeln('Compiling PCIs...');

        var extension = 'qtiItemPci';

        var options = this.options({
            punctuation: '.',
            separator: ', '
        });

        var manifests = grunt.file.expand(root + '/' + extension + '/views/js/pciCreator/**/pciCreator.json');

        console.log(root);
        console.log(options);
        console.log(manifests);

        manifests.forEach(function (file) {
            console.log(file);

            grunt.log.writeln('Compiling PCI from manifest "' + file);
            return;
            // Concat specified files.
            var src = file.src.filter(function (filepath) {
                // Warn on and remove invalid source files (if nonull was set).
                if (!grunt.file.exists(filepath)) {
                    grunt.log.warn('Source file "' + filepath + '" not found.');
                    return false;
                } else {
                    return true;
                }
            }).map(function (filepath) {
                // Read file source.
                return grunt.file.read(filepath);
            }).join(grunt.util.normalizelf(options.separator));

            // Handle options.
            src += options.punctuation;

            // Write the destination file.
            grunt.file.write(file.dest, src);

            // Print a success message.
            grunt.log.writeln('File "' + file.dest + '" created.');
        });


        var requirejs = require('requirejs');
        var fs = require('fs');

        var paths = {
            'likertScaleInteraction':      root + '/qtiItemPci/views/js/pciCreator/dev/likertScaleInteraction',
            'liquidsInteraction':      root + '/qtiItemPci/views/js/pciCreator/dev/liquidsInteraction',
            'taoQtiItem':                  root + '/taoQtiItem/views/js',
            'taoQtiItemCss':               root + '/taoQtiItem/views/css',
            'qtiCustomInteractionContext': root + '/taoQtiItem/views/js/runtime/qtiCustomInteractionContext'
        };
        var runtimeHook = 'likertScaleInteraction/runtime/likertScaleInteraction';
        var id = 'likertScaleInteraction';
        var out         = 'output';
        var config = {
            optimize: 'uglify2',
            uglify2: {
                mangle : false,
                output: {
                    'max_line_len': 666
                }
            },
            preserveLicenseComments: false,
            optimizeAllPluginResources: true,
            findNestedDependencies : true,
            skipDirOptimize: true,
            optimizeCss : 'none',
            buildCss : false,
            inlineText: true,
            skipPragmas : true,
            generateSourceMaps : true,
            removeCombined : true,
            baseUrl : '../js',
            mainConfigFile : './config/requirejs.build.js',

            paths : paths,
            name: runtimeHook,
            excludeShallow : ['mathJax'],
            exclude : ['qtiCustomInteractionContext'],
            wrap : {
                start : '',
                end : "define(['" + runtimeHook +"'],function(pci){return pci;});"
            },
            out: out  + '/' + id + '/' + 'likert' + '.js'
        };

        console.log(config);

        //grunt.tasks(['mytask', 'jshint'], {}, function() {
        //    grunt.log.ok('Done running tasks.');
        //});
        //return;

        var done = this.async();
        requirejs.optimize(config, function (buildResponse) {
            //buildResponse is just a text output of the modules
            //included. Load the built file for the contents.
            //Use config.out to get the optimized file contents.
            var contents = fs.readFileSync(config.out, 'utf8');
            console.log(buildResponse);
            done();
        }, function(err) {
            //optimization err callback
            console.error('failssss');
            done();
        });



        return;

        var LOG_LEVEL_TRACE = 0, LOG_LEVEL_WARN = 2;

        // TODO: extend this to send build log to grunt.log.ok / grunt.log.error
        // by overriding the r.js logger (or submit issue to r.js to expand logging support)
        //requirejs.define('node/print', [], function() {
        //    return function print(msg) {
        //        if (msg.substring(0, 5) === 'Error') {
        //            grunt.log.errorlns(msg);
        //            grunt.fail.warn('RequireJS failed.');
        //        } else {
        //            grunt.log.oklns(msg);
        //        }
        //    };
        //});

        //var done = this.async();
        var options = this.options({
            logLevel: grunt.option('verbose') ? LOG_LEVEL_TRACE : LOG_LEVEL_WARN,
            error: false,
            done: function(done){
                done();
            }
        });

        options = _.defaults(config, options);

        // The following catches errors in the user-defined `done` function and outputs them.
        var tryCatchDone = function(fn, done, output) {
            try {
                fn(done, output);
            } catch(e) {
                grunt.fail.warn('There was an error while processing your done function: "' + e + '"');
            }
        };

        // The following catches errors in the user-defined `error` function and passes them.
        // if the error function options is not set, this value should be undefined
        var tryCatchError = function(fn, done, err) {
            try {
                fn(done, err);
            } catch(e) {
                grunt.fail.fatal('There was an error while processing your error function: "' + e + '"');
            }
        };

        requirejs.optimize(
            options,
            tryCatchDone.bind(null, options.done, done ),
            options.error ? tryCatchError.bind(null, options.error, done ):undefined
        );

    });

    return;
    grunt.config.merge({
        requirejs : {
            //general options for all requirejs tasks
            options : {
                optimize: 'uglify2',
                uglify2: {
                    mangle : false,
                    output: {
                        'max_line_len': 666
                    }
                },
                //optimize : 'none',
                preserveLicenseComments: false,
                optimizeAllPluginResources: true,
                findNestedDependencies : true,
                skipDirOptimize: true,
                optimizeCss : 'none',
                buildCss : false,
                inlineText: true,
                skipPragmas : true,
                generateSourceMaps : true,
                removeCombined : true,
                baseUrl : '../js',
                mainConfigFile : './config/requirejs.build.js',
            },

            taobundle : {
                options: {
                    dir : out,
                    modules : [{
                        name: 'controller/login',
                        include: ['lib/require', 'loader/bootstrap'],
                        exclude : ['json!i18ntr/messages.json']
                    }, {
                        name: 'controller/backoffice',
                        include: ['lib/require', 'loader/bootstrap'].concat(libs),
                        exclude: ['json!i18ntr/messages.json',  'mathJax', 'ckeditor']
                    }, {
                        name: 'controller/app',
                        include: ['lib/require', 'loader/bootstrap'].concat(libs),
                        exclude : ['json!i18ntr/messages.json']
                    }, {
                        name: 'controller/routes',
                        include : ext.getExtensionsControllers(['tao']),
                        exclude : ['mathJax', 'controller/login', 'controller/backoffice'].concat(libs)
                    }]
                }
            }
        },

        clean: {
            options : {
                force : true
            },
            bundle : [out]
        },

        copy : {
            options : {
                process: function (content, srcpath) {
                    //because we change the bundle names during copy
                    if(/routes\.js$/.test(srcpath)){
                        return content.replace('routes.js.map', 'controllers.min.js.map');
                    }
                    return content;
                }
            },

            taobundle : {
                files: [
                    { src: [out + '/controller/login.js'],            dest: '../js/loader/login.min.js' },
                    { src: [out + '/controller/login.js.map'],        dest: '../js/loader/login.min.js.map' },
                    { src: [out + '/controller/backoffice.js'],       dest: '../js/loader/backoffice.min.js' },
                    { src: [out + '/controller/backoffice.js.map'],   dest: '../js/loader/backoffice.min.js.map' },
                    { src: [out + '/controller/app.js'],              dest: '../js/loader/app.min.js' },
                    { src: [out + '/controller/app.js.map'],          dest: '../js/loader/app.min.js.map' },
                    { src: [out + '/controller/routes.js'],           dest: '../js/controllers.min.js' },
                    { src: [out + '/controller/routes.js.map'],       dest: '../js/controllers.min.js.map' }
                ],
                options : {
                    process: function (content, srcpath) {
                        //because we change the bundle names during copy
                        if(/login\.js$/.test(srcpath)){
                            return content.replace('login.js.map', 'login.min.js.map');
                        }
                        if(/backoffice\.js$/.test(srcpath)){
                            return content.replace('backoffice.js.map', 'backoffice.min.js.map');
                        }
                        if(/routes\.js$/.test(srcpath)){
                            return content.replace('routes.js.map', 'controllers.min.js.map');
                        }

                        return content;
                    }
                }
            }
        }
    });

    // bundle task
    grunt.registerTask('taobundle', ['clean:bundle', 'requirejs:taobundle', 'copy:taobundle']);
};
