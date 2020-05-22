module.exports = function(grunt) {
    'use strict';

    var livereloadPort = grunt.option('livereloadPort');

    //instantiate sass module
    const sass = require('node-sass');

    grunt.config.merge({
        sass : {
            options: {
                includePaths : [ '../scss/', '../js/lib/', '../node_modules/@oat-sa/tao-core-ui/scss' ],
                outputStyle : 'compressed',
                sourceMap : true,
                //set implementation for sass to make 3.x.x branches of grunt-sass work, see https://github.com/nodejs/nan/issues/504#issuecomment-385296082, https://github.com/sourcey/spectacle/issues/156#issuecomment-401731543
                implementation: sass
            },
            tao: {
                files : {
                    '../css/tao-main-style.css' : '../scss/tao-main-style.scss',
                    '../css/tao-3.css' : '../scss/tao-3.scss',
                    '../css/layout.css' : '../scss/layout.scss',
                    '../css/error-page.css': '../scss/error-page.scss',
                    '../js/lib/jsTree/themes/css/style.css' : '../js/lib/jsTree/themes/scss/style.scss'
                }
            },
            component : {
                files : [{
                    expand: true,
                    src: '../js/ui/**/scss/*.scss',
                    rename : function rename(dest, src){
                        return src.replace(/scss/g, 'css');
                    }
                }, {
                    //TODO move them to the correct folder (css,scss)
                    '../js/ui/generis/form/form.css' : '../js/ui/generis/form/form.scss',
                    '../js/ui/generis/widget/widget.css' : '../js/ui/generis/widget/widget.scss',
                    '../js/ui/generis/validator/validator.css' : '../js/ui/generis/validator/validator.scss',
                }]
            }
        },

        watch: {
            options: {
                livereload: livereloadPort
            },
            taosass : {
                files : ['../scss/*.scss', '../scss/**/*.scss', '../js/lib/jsTree/**/*.scss'],
                tasks : ['sass:tao', 'notify:taosass'],
                options : {
                    debounceDelay : 1000
                }
            },
            componentsass : {
                files : ['../js/ui/**/*.scss'],
                tasks : ['sass:component', 'notify:taosass' ],
                options : {
                    debounceDelay : 1000
                }
            }
        },

        notify : {
            taosass : {
                options: {
                    title: 'Grunt SASS',
                    message: 'SASS files compiled to CSS'
                }
            }
        }
    });

    //register an alias for main build
    grunt.registerTask('taosass', ['sass:tao', 'sass:component']);
};
