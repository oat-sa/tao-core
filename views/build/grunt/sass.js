module.exports = function(grunt) {
    'use strict';

    var livereloadPort = grunt.option('livereloadPort');

    //instantiate sass module
    const sass = require('node-sass');

    grunt.config.merge({
        sass : {
            options: {
                includePaths : [ '../scss/', '../js/lib/' ],
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
                    '../js/lib/jsTree/themes/css/style.css' : '../js/lib/jsTree/themes/scss/style.scss'
                }
            },
            ckeditor : {
                files : {
                    '../js/lib/ckeditor/skins/tao/editor.css' : '../js/lib/ckeditor/skins/tao/scss/editor.scss',
                    '../js/lib/ckeditor/skins/tao/dialog.css' : '../js/lib/ckeditor/skins/tao/scss/dialog.scss'
                }
            },
            component : {
                files : {
                    '../js/ui/mediaplayer/css/player.css' : '../js/ui/mediaplayer/scss/player.scss',
                    '../js/ui/class/css/selector.css' : '../js/ui/class/scss/selector.scss',
                    '../js/ui/resource/css/selector.css' : '../js/ui/resource/scss/selector.scss',
                    '../js/ui/generis/form/form.css' : '../js/ui/generis/form/form.scss',
                    '../js/ui/generis/widget/widget.css' : '../js/ui/generis/widget/widget.scss',
                    '../js/ui/generis/validator/validator.css' : '../js/ui/generis/validator/validator.scss',
                    '../js/ui/switch/css/switch.css' : '../js/ui/switch/scss/switch.scss',
                    '../js/ui/animable/absorbable/css/absorb.css' : '../js/ui/animable/absorbable/scss/absorb.scss',
                    '../js/ui/animable/pulsable/css/pulse.css' : '../js/ui/animable/pulsable/scss/pulse.scss',
                    '../js/ui/badge/css/badge.css' : '../js/ui/badge/scss/badge.scss',
                    '../js/ui/loadingButton/css/button.css' : '../js/ui/loadingButton/scss/button.scss',
                    '../js/ui/destination/css/selector.css' : '../js/ui/destination/scss/selector.scss',
                    '../js/ui/taskQueueButton/css/taskable.css' : '../js/ui/taskQueueButton/scss/taskable.scss',
                    '../js/ui/taskQueueButton/css/treeButton.css' : '../js/ui/taskQueueButton/scss/treeButton.scss',
                    '../js/ui/waitingDialog/css/waitingDialog.css' : '../js/ui/waitingDialog/scss/waitingDialog.scss',
                    '../js/ui/maths/calculator/css/calculator.css' : '../js/ui/maths/calculator/scss/calculator.scss'
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
    grunt.registerTask('taosass', ['sass:tao', 'sass:component']);
};
