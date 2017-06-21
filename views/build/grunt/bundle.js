module.exports = function(grunt) {
    'use strict';

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out         = 'output';

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
