module.exports = function(grunt) {
    'use strict';

    var requirejs = require('requirejs');
    var fs = require('fs');
    var _ = require('lodash');

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var out         = 'output';


    grunt.config.merge({
        compilepci : {
            options: {
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
                extension : 'qtiItemPci',
                excludeShallow : ['mathJax'],
                exclude : ['qtiCustomInteractionContext'],
                paths : {
                    'taoQtiItem':                  root + '/taoQtiItem/views/js',
                    'taoQtiItemCss':               root + '/taoQtiItem/views/css',
                    'qtiCustomInteractionContext': root + '/taoQtiItem/views/js/runtime/qtiCustomInteractionContext'
                }
            }
        }
    });

    function getHookFileName(pciRuntimeData, prefix){
        var runtimeHook;
        if(Array.isArray(pciRuntimeData.src) && pciRuntimeData.src.length > 0){
            //by convention the first module is the hook file
            runtimeHook = pciRuntimeData.src[0];
            runtimeHook = runtimeHook.replace(/\.js$/i, '');
            runtimeHook = runtimeHook.replace(/^\.\//, prefix + '/');
            return runtimeHook;
        }
    }

    function getMinHookFileName(pciRuntimeData){
        if(pciRuntimeData.hook){
            return pciRuntimeData.hook;
        }
        if(Array.isArray(pciRuntimeData.libraries) && pciRuntimeData.libraries.length > 0){
            //by convention the first module is the min file
            return pciRuntimeData.libraries[0];
        }
    }

    grunt.registerTask('compilepci', 'Compile PCIs', function(){

        var done = this.async();//async mode because requirejs optimization is an async process
        var extension = 'qtiItemPci';
        var manifests = grunt.file.expand(root + '/' + extension + '/views/js/pciCreator/**/pciCreator.json');
        var self = this;

        grunt.log.writeln('Compiling PCIs...');

        console.log(root);
        console.log(manifests);

        _.forEach(manifests, function (file) {

            file = root + '/qtiItemPci/views/js/pciCreator/dev/likertScaleInteraction/pciCreator.json';
            var dir = file.replace('/pciCreator.json', '');

            grunt.log.writeln('Compiling PCI from manifest "' + file + '" ...');

            var manifest = grunt.file.readJSON(file);
            var id = manifest.typeIdentifier;
            var runtimeHook = getHookFileName(manifest.runtime, id);
            var config = self.options({
                name: runtimeHook,
                out: dir + '/' + getMinHookFileName(manifest.runtime),
                wrap : {
                    start : '',
                    end : "define(['" + runtimeHook +"'],function(pci){return pci;});"
                },
            });
            config.paths[id] = dir;

            console.log(config);
            console.log(dir + '/' + getMinHookFileName(manifest.runtime) + '.js');

            requirejs.optimize(config, function (buildResponse) {
                ////buildResponse is just a text output of the modules
                ////included. Load the built file for the contents.
                ////Use config.out to get the optimized file contents.
                //var contents = fs.readFileSync(config.out, 'utf8');
                //console.log(contents);
                grunt.log.write('PCI "' + id + '" compiled');
                console.log(buildResponse);

                //grunt.file.copy(srcpath, destpath [, options])
                done();
            }, function(err) {
                //optimization err callback
                console.log('failssss');
                console.log(err);
                done();
            });

            return false;

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


        return;

        var runtimeHook = 'likertScaleInteraction/runtime/likertScaleInteraction';
        var id = 'likertScaleInteraction';
        var config = {
            paths : paths,
            name: runtimeHook,
            out: out  + '/' + id + '/' + 'likert' + '.js',
            wrap : {
                start : '',
                end : "define(['" + runtimeHook +"'],function(pci){return pci;});"
            },
        };

        config = this.options(config);

        config.paths[id] = root + '/qtiItemPci/views/js/pciCreator/dev/likertScaleInteraction';

        console.log(config);


        requirejs.optimize(config, function (buildResponse) {
            //buildResponse is just a text output of the modules
            //included. Load the built file for the contents.
            //Use config.out to get the optimized file contents.
            var contents = fs.readFileSync(config.out, 'utf8');
            console.log(buildResponse);
            done();
        }, function(err) {
            //optimization err callback
            console.log('failssss');
            console.log(err);
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
