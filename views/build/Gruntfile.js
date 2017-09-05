module.exports = function(grunt) {
    'use strict';

    /*
     * IMPORTANT : This file is just the launcher, each task is defined in extension/views/build/grunt/
     * for example the SASS task is defined in tao/views/build/grunt/sass.js for the main behavior and
     * in taoQtiItem/views/build/grunt/sass.js for extension specific configuration.
     */

    //set up contextual config
    var root                = require('path').resolve('../../../').replace(/\\/g, '/'); //tao dist root
    var ext                 = require('./tasks/helpers/extensions')(grunt, root);       //extension helper
    var currentExtension    = grunt.option('extension') || 'tao';                       //target extension, add "--extension name" to CLI if needed
    var reportOutput        = grunt.option('reports') || 'reports';                     //where reports are saved
    var testUrl             = grunt.option('testUrl') || '127.0.0.1';                   //the port to run test web server, override with "--testPort value" to CLI if needed
    var testPort            = grunt.option('testPort') || 8082;                         //the port to run test web server, override with "--testPort value" to CLI if needed
    var livereloadPort      = parseInt(grunt.option('livereloadPort'), 10) || true;     //the livereload port, override with "--livereloadPort 35729" to CLI if needed

    var sassTasks   = [];
    var bundleTasks = [];
    var testTasks   = [];


    //Resolve some shared AMD modules
    var libsPattern =  ['views/js/*.js', 'views/js/core/**/*.js', 'views/js/ui/**/*.js', 'views/js/layout/**/*.js', 'views/js/util/**/*.js', '!views/js/main.*', '!views/js/*.min*', '!views/js/test/**/*.js'];
    var libs        = ext.getExtensionSources('tao', libsPattern, true).concat([
        'jquery',
        'jqueryui',
        'select2',
        'lodash',
        'async',
        'moment',
        'handlebars',
        'ckeditor',
        'class',
        'jwysiwyg',
        'jquery.tree',
        'jquery.timePicker',
        'jquery.cookie',
        'jquery.fileDownload',
        'raphael',
        'scale.raphael',
        'html5-history-api']);

    grunt.option('mainlibs', libs);
    grunt.option('root', root);
    grunt.option('currentExtension', currentExtension);
    grunt.option('testPort', testPort);
    grunt.option('testUrl', testUrl);
    grunt.option('reports', reportOutput);
    grunt.option('livereloadPort', livereloadPort);
    grunt.option('requirejsModule', require('requirejs'));

    //track build time
    require('time-grunt')(grunt);

    // load all grunt tasks matching the `grunt-*` pattern
    require('load-grunt-tasks')(grunt);

     // Load local tasks.
    grunt.loadTasks('tasks');

    /*
     * Load separated configs into each extension
     */

    ext.getExtensions().forEach(function(extension){

        var extensionKey = extension.toLowerCase();
        var gruntDir = root + '/' + extension + '/views/build/grunt';
        if(grunt.file.exists(gruntDir)){
            grunt.verbose.write('Load tasks from gruntDir ' + gruntDir);
            grunt.loadTasks(gruntDir);
        }

        //register all bundle tasks under a bigger one
        if(grunt.task.exists(extensionKey + 'bundle')){
            bundleTasks.push(extensionKey + 'bundle');
        }

        //register all sass tasks under a bigger one
        if(grunt.task.exists(extensionKey + 'sass')){
            sassTasks.push(extensionKey + 'sass');
        }

        //register all test tasks under a bigger one
        if(grunt.task.exists(extensionKey + 'test')){
            testTasks.push(extensionKey + 'test');
        }
    });

    /*
     *task to run by extension concurrently
     */
    grunt.config('concurrent', {
        build : ['bundleall', 'sassall']
    });

    /*
     * Create task alias
     */
    grunt.registerTask('sassall', "Compile all sass files", sassTasks);
    grunt.registerTask('bundleall', "Compile all js files", bundleTasks);
    grunt.registerTask('testall', "Run all tests", ['connect:test', 'qunit_junit'].concat(testTasks));
    grunt.registerTask('build', "The full build sequence", ['concurrent:build']);
};
