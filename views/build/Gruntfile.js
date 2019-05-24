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
const getPort = require('get-port-sync');
const path    = require('path');

module.exports = function(grunt) {
    'use strict';

    /*
     * IMPORTANT : This file is just the launcher, each task is defined in extension/views/build/grunt/
     * for example the SASS task is defined in tao/views/build/grunt/sass.js for the main behavior and
     * in taoQtiItem/views/build/grunt/sass.js for extension specific configuration.
     */

    //set up contextual config
    const root             = path.resolve('../../../').replace(/\\/g, '/');      //tao dist root
    const extensionHelper  = require('./tasks/helpers/extensions')(grunt, root); //extension helper
    const currentExtension = grunt.option('extension') || 'tao';                 //target extension, add "--extension name" to CLI if needed
    const reportOutput     = grunt.option('reports') || 'reports';               //where reports are saved
    const buildOutput      = grunt.option('output')   || 'output';
    const testUrl          = grunt.option('testUrl') || '127.0.0.1';             //the port to run test web server, override with "--testPort value" to CLI if needed
    const testPort         = parseInt(grunt.option('testPort'), 10) || getPort();//the port to run test web server, override with "--testPort value" to CLI if needed
    const livereloadPort   = parseInt(grunt.option('livereloadPort'), 10) || true;//the livereload port, override with "--livereloadPort 35729" to CLI if needed

    const sassTasks   = [];
    const bundleTasks = [];
    const testTasks   = [];

    grunt.option('root', root);
    grunt.option('currentExtension', currentExtension);
    grunt.option('testPort', testPort);
    grunt.option('testUrl', testUrl);
    grunt.option('reports', reportOutput);
    grunt.option('buildOutput', buildOutput);
    grunt.option('livereloadPort', livereloadPort);
    grunt.option('requirejsModule', require('requirejs'));

    //track build time
    require('time-grunt')(grunt);

    // load all grunt tasks matching the `grunt-*` pattern
    require('load-grunt-tasks')(grunt);

    grunt.loadNpmTasks('@oat-sa/grunt-tao-bundle');

    /*
     * Load separated configs into each extension
     */

    extensionHelper.getExtensions().forEach(function(extension){

        const extensionKey = extension.toLowerCase();
        const gruntDir = path.join(root, extension, '/views/build/grunt');
        if(grunt.file.exists(gruntDir)){
            grunt.verbose.write('Load tasks from gruntDir ' + gruntDir);
            grunt.loadTasks(gruntDir);
        }

        //register all bundle tasks under a bigger one
        if(grunt.task.exists(`${extensionKey}bundle`)){
            bundleTasks.push(`${extensionKey}bundle`);
        }

        //register all sass tasks under a bigger one
        if(grunt.task.exists(`${extensionKey}sass`)){
            sassTasks.push(`${extensionKey}sass`);
        }

        //register all test tasks under a bigger one
        if(grunt.task.exists(`${extensionKey}test`)){
            testTasks.push(`${extensionKey}test`);
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
    grunt.registerTask('testall', "Run all tests", ['connect:test', 'qunit_junit', ...testTasks]);
    grunt.registerTask('build', "The full build sequence", ['concurrent:build']);
};
