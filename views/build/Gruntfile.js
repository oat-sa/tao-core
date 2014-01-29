module.exports = function(grunt) {
    'use strict';

    var root = require('path').resolve('../../../');
    var currentExtension = grunt.option('extension') || 'tao';  
    var ext = require('./helpers/extensions')(grunt, root);
    
    // load all grunt tasks matching the `grunt-*` pattern
    require('load-grunt-tasks')(grunt);
    
    
    //build some dynamic values for the config regarding the current extensions 
    var amdBundles = [];
    var copies = [];
    ext.getExtensions(true).forEach(function(extension){
        amdBundles.push({
            name: extension + '/controller/routes',
            include : ext.getExtensionsControllers([extension]),
            exclude : ['main', 'i18n_tr']
       });
       copies.push({
           src: ['output/'+ extension +'/controller/routes.js'],  
           dest: ext.getExtensionPath(extension) + '/views/js/controllers.min.js'
       });
    });
    
    //all the Javascript sources for Linting
    var allJsSources = ext.getExtensionSources(currentExtension, ['views/js/**/*.js', '!views/js/lib/**/*.js', '!views/js/**/*.min.js']);
    
    var taoBaseLibs = ext.getExtensionsLibs(['tao']);
    delete taoBaseLibs['main'];
    
    //get qti runtime AMD modules
    var extPath = ext.getExtensionPath('taoQTI');
    var qtiRuntimeAmd = ext.getExtensionSources('taoQTI', [
        'views/js/qtiItem/core/**/*.js', 
        'views/js/qtiDefaultRenderer/renderers/**/*.js', 
        'views/js/qtiDefaultRenderer/widgets/**/*.js']).map(function(source){
                    return source.replace(extPath + '/views/js', '')
                            .replace(/\.(js)$/, '').replace(/^\//, '')
                            .replace('qtiItem', 'taoQtiItem')
                            .replace('qtiRunner', 'taoQtiRunner')
                            .replace('qtiDefaultRenderer', 'taoQtiDefaultRenderer');
                });
    qtiRuntimeAmd = qtiRuntimeAmd.concat(ext.getExtensionSources('taoQTI', ['views/js/qtiDefaultRenderer/tpl/**/*.tpl']).map(function(source){
                    return source.replace(extPath + '/views/js', '')
                            .replace(/\.(tpl)$/, '').replace(/^\//, 'tpl!')
                            .replace('qtiDefaultRenderer', 'taoQtiDefaultRenderer');
                }));
    
    /**
     * 
     * Set up Grunt config
     * 
     */
    grunt.initConfig({
        
        clean: {
            options:  {
                force : true
            },
            backendBundle : ['output',  '../js/main.min.js', '../../../*/views/js/controllers.min.js'],
            qtiBundle : ['output', '../../../taoQTI/views/js/runtime/qtiLoader.min.js']
        },
        
        copy : {
            backendBundle : {
                files: [
                    { src: ['output/main.js'],  dest: '../js/main.min.js' },
                    { src: ['output/controller/routes.js'],  dest: '../js/controllers.min.js' }
                ].concat(copies)
            },
            qtiBundle : { 
                files : [ { src: ['output/qtiLoader.min.js'],  dest: '../../../taoQTI/views/js/runtime/qtiLoader.min.js' } ]
            }
        },
        
        /**
         * Optimize JavaScript files
         */
        requirejs: {
            
            options : {
                optimize: 'uglify2',
                preserveLicenseComments: false,
                optimizeAllPluginResources: false,
                findNestedDependencies : true,
                optimizeCss : false,
                inlineText: true,
                paths : ext.getExtensionsPaths()
            },
            
            /**
             * compile the javascript files of all TAO backend's extension to one file!
             */
            backendAll: {
                options: {
                    baseUrl : '../js',
                    out: '../js/main.min.js',
                    name: 'main',
                    mainConfigFile : './config/backend.js',
                    include: ['lib/require'].concat(ext.getExtensionsControllers()),
                    exclude : ['i18n_tr']
                }
            },
            
            /**
             * Or bundles
             */
            backendBundle : {
                 options: {
                    baseUrl : '../js',
                    dir : 'output',
                    mainConfigFile : './config/backend.js',
                    modules : [{
                        name: 'main',
                        include: [
                            'lib/require'
                        ],
                        deps : taoBaseLibs,
                        exclude : ['i18n_tr']
                    }].concat(amdBundles)
                }
            },
            
            qtiBundle : {
                options: {
                    baseUrl : '../js',
                    out: 'output/qtiLoader.min.js',
                    name: 'taoQTI/runtime/qtiLoader',
                    optimizeAllPluginResources: true,
                    mainConfigFile : './config/qtiRuntime.js',
                    insertRequire: ['taoQTI/runtime/qtiLoader'],
                    paths: {
                       'taoQTI' : '../../../taoQTI/views/js'
                    },
                    include: ['lib/require'].concat(qtiRuntimeAmd),
                    exclude : ['i18n_tr', 'mathJax']
                }
            }
        },
        

        /**
         * Check your code style by extension
         * grunt jshint --extension=taoItems
         */
        jshint : {
            dist : {
                src : allJsSources,
                options : {
                    jshintrc : '.jshintrc'
                }
            },
            noop: {
                src : []
            }
        },
        
        
        /**
         * Compile SASS to CSS
         * grunt jshint --extension=taoItems
         */
        sass : {
            options : {
                includePaths : ['../scss/']
            },
            compile : {
                files : {
                    '../css/tao-main-style.css' : '../scss/tao-main-style.scss'
                }
            }
        },
        

        /**
         * Runs a task by watching on file changes (used for development purpose)
         */
        watch : {
            
            /**
             * Watch SASS changes and compile on the fly!
             */
            'sass' : {
                files : ['../scss/*.scss', '../scss/**/*.scss'],
                tasks : ['sass:compile'],
                options : {
                    debouncDelay : 500
                }
            }
        }
    });
    
    grunt.registerTask('backendBundle', "Create JavaScript bundles for TAO backend",
                        ['clean:backendBundle', 'requirejs:backendBundle', 'copy:backendBundle']);
    grunt.registerTask('qtiBundle', "Create JavaScript bundles for QTI runtimes",
                        ['clean:qtiBundle', 'requirejs:qtiBundle', 'copy:qtiBundle']);
    grunt.registerTask('jsBundle', "Create JavaScript bundles for the whole TAO plateform",
                        ['backendBundle', 'qtiBundle']);
};

