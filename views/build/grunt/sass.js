module.exports = function(grunt) {
    'use strict';

    var livereloadPort = grunt.option('livereloadPort');

    grunt.config.merge({
        sass : {
            options: {
                includePaths : [ '../scss/', '../js/lib/' ],
                outputStyle : 'compressed',
                sourceMap : true
            },
            tao: {
                files : {
                    '../css/tao-main-style.css' : '../scss/tao-main-style.scss',
                    '../css/tao-3.css' : '../scss/tao-3.scss',
                    '../css/layout.css' : '../scss/layout.scss'
                }
            },
            ckeditor : {
                files : {
                    '../js/lib/ckeditor/skins/tao/editor.css' : '../js/lib/ckeditor/skins/tao/scss/editor.scss',
                    '../js/lib/ckeditor/skins/tao/dialog.css' : '../js/lib/ckeditor/skins/tao/scss/dialog.scss',
                }
            },
            component : {
                files : {
                    '../js/ui/mediaplayer/css/player.css' : '../js/ui/mediaplayer/scss/player.scss',
                    '../js/ui/class/css/selector.css' : '../js/ui/class/scss/selector.scss',
                    '../js/ui/resource/css/selector.css' : '../js/ui/resource/scss/selector.scss',
                    '../js/ui/generis/form/form.css' : '../js/ui/generis/form/form.scss',
                    '../js/ui/generis/widget/widget.css' : '../js/ui/generis/widget/widget.scss',
                    '../js/ui/generis/validator/validator.css' : '../js/ui/generis/validator/validator.scss'
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
    grunt.registerTask('taosass', ['sass:tao']);
};
