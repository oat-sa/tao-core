module.exports = function(grunt) {
    'use strict';

    var livereloadPort = grunt.option('livereloadPort');

    grunt.config.merge({
        sass : {
            options : {
                noCache: true,
                unixNewlines : true,
                loadPath : ['../scss/', '../js/lib/'],
                lineNumbers : false,
                style : 'compressed'
            },
            tao: {
                files : {
                    '../css/tao-main-style.css' : '../scss/tao-main-style.scss',
                    '../css/tao-3.css' : '../scss/tao-3.scss',
                    '../css/layout.css' : '../scss/layout.scss',
                    '../js/lib/jsTree/themes/css/style.css' : '../js/lib/jsTree/themes/scss/style.scss'
                }
            },
            ckeditor : {
                files : {
                    '../js/lib/ckeditor/skins/tao/editor.css' : '../js/lib/ckeditor/skins/tao/scss/editor.scss',
                    '../js/lib/ckeditor/skins/tao/dialog.css' : '../js/lib/ckeditor/skins/tao/scss/dialog.scss',
                }
            },
            mediaplayer : {
                files : {
                    '../js/ui/mediaplayer/css/player.css' : '../js/ui/mediaplayer/scss/player.scss',
                }
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
    grunt.registerTask('taosass', ['sass:tao']);
};
