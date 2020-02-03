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
 * Copyright (c) 2014-2019 (original work) Open Assessment Technlogies SA
 *
 */

/**
 * Main configuration to build css
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
module.exports = function(grunt) {
    'use strict';

    var livereloadPort = grunt.option('livereloadPort');

    //instantiate sass module
    const sass = require('node-sass');

    grunt.config.merge({
        sass : {
            options: {
                includePaths : [ './scss/', './js/lib/', './node_modules/@oat-sa/tao-core-ui/scss' ],
                outputStyle : 'compressed',
                sourceMap : true,
                //set implementation for sass to make 3.x.x branches of grunt-sass work, see https://github.com/nodejs/nan/issues/504#issuecomment-385296082, https://github.com/sourcey/spectacle/issues/156#issuecomment-401731543
                implementation: sass
            },
            tao: {
                files : {
                    './css/tao-main-style.css' : './scss/tao-main-style.scss',
                    './css/tao-3.css' : './scss/tao-3.scss',
                    './css/layout.css' : './scss/layout.scss',
                    './js/lib/jsTree/themes/css/style.css' : './js/lib/jsTree/themes/scss/style.scss'
                }
            },
            ckeditor : {
                files : {
                    './js/lib/ckeditor/skins/tao/css/editor.css' : './js/lib/ckeditor/skins/tao/scss/editor.scss',
                    './js/lib/ckeditor/skins/tao/css/dialog.css' : './js/lib/ckeditor/skins/tao/scss/dialog.scss'
                }
            },
            component : {
                files : [{
                    expand: true,
                    src: './js/ui/**/scss/*.scss',
                    rename : function rename(dest, src){
                        return src.replace(/scss/g, 'css');
                    }
                }, {
                    //TODO move them to the correct folder (css,scss)
                    './js/ui/generis/form/form.css' : './js/ui/generis/form/form.scss',
                    './js/ui/generis/widget/widget.css' : './js/ui/generis/widget/widget.scss',
                    './js/ui/generis/validator/validator.css' : './js/ui/generis/validator/validator.scss',
                }]
            }
        },

        watch: {
            options: {
                livereload: livereloadPort
            },
            taosass : {
                files : ['./scss/*.scss', './scss/**/*.scss', './js/lib/jsTree/**/*.scss'],
                tasks : ['sass:tao', 'notify:taosass'],
                options : {
                    debounceDelay : 1000
                }
            },
            componentsass : {
                files : ['./js/ui/**/*.scss'],
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
