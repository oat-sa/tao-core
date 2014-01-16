module.exports = function(grunt) {
    'use strict';
    
    grunt.initConfig({
        
        requirejs: {
            backend: {
                options: {
                    baseUrl: '../js',
                    out: '../js/main.min.js',
                    mainConfigFile : 'backend.build.js',
                    preserveLicenseComments: false,
                    findNestedDependencies : true,
                    optimizeCss : false,
                    removeCombined: true,
                    name: 'main',
                    include: ['lib/require.js']
                }
            }
        },

        sass : {
            options: {
                includePaths : ['../scss/']
            },
            compile : {
                files : {
                    '../css/tao-main-style.css' : '../scss/tao-main-style.scss'
                }
            }
        },

        watch : {
            sass : {
                files : ['../scss/*.scss', '../scss/**/*.scss'],
                tasks : ['sass:compile'],
                options : {
                    debouncDelay : 500
                }
            }
        }

    });
    
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-sass');   
    grunt.loadNpmTasks('grunt-contrib-watch');
};
