module.exports = function(grunt) {
    'use strict';

    var root = '../../../';
    var extension = grunt.option('extension') || 'tao';
    var extpath  = root + extension;

    grunt.log.write('Running Grrrrunt!!! on extension ' + extension);
    var jsSources = grunt.file.expand({ cwd: extpath }, ['views/js/**/*.js', '!views/js/lib/**/*.js']);
    jsSources.forEach(function(source, index){
        jsSources[index] = extpath + '/' + source;
    });

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

        jshint : {
            dist : {
                src : jsSources,
                options : {
                    jshintrc : '.jshintrc'
                }
            }
        },

        sass : {
            options: {
                includePaths : ['../scss/']
            },
            tao : {
                files : {
                    '../css/tao-main-style.css' : '../scss/tao-main-style.scss'
                }
            }
        },

        watch : {
            'tao-sass' : {
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
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-jshint');
};
