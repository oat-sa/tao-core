module.exports = function(grunt) {
    'use strict';

    /*
     * IMPORTANT : This file is just the launcher, each task is defined in extension/views/build/grunt/ 
     * for example the SASS task is defined in tao/views/build/grunt/sass.js for the main behavior and in taoQtiItem/views/build/grunt/sass.js for extension specific configuration.
     * 
     */

    // load all grunt tasks matching the `grunt-*` pattern
    require('load-grunt-tasks')(grunt);

    //set up contextual config
    var root = require('path').resolve('../../../');
    var ext  = require('./tasks/helpers/extensions')(grunt, root);
    var currentExtension = grunt.option('extension') || 'tao';  
    grunt.option('root', root);
    grunt.option('currentExtension', currentExtension);

    //Resolve some shared AMD modules 
    var libsPattern =  ['views/js/*.js', 'views/js/core/**/*.js', 'views/js/ui/**/*.js', 'views/js/layout/**/*.js', 'views/js/util/**/*.js', '!views/js/main.*', '!views/js/*.min*', '!views/js/test/**/*.js'];
    var libs        = ext.getExtensionSources('tao', libsPattern, true).concat([
        'jquery',
        'jqueryui',
        'jquerytools',
        'filereader',
        'store',
        'select2',
        'lodash',
        'async',
        'moment',
        'handlebars',
        'ckeditor',
        'class',
        'jwysiwyg',
        'jquery.tree',
        'jqGrid',
        'jquery.timePicker',
        'jquery.cookie',
        'jquery.fileDownload',
        'raphael',
        'scale.raphael',
        'tooltipster',
        'history']);

    grunt.option('mainlibs', libs);
    
    //extract tao version
    var constants = grunt.file.read('../../includes/constants.php');
    var taoVersion = constants.match(/'TAO_VERSION'\,\s?'(.*)'/)[1];
    grunt.log.write('Found tao version ' + taoVersion);  

     // Load local tasks.
    grunt.loadTasks('tasks');


    // load separated configs into each extension
    var sassTasks  = [];
    var bundleTasks = []; 
    ext.getExtensions().forEach(function(extension){
        grunt.log.debug(extension);
        
        var gruntDir = root + '/' + extension + '/views/build/grunt';
        if(grunt.file.exists(gruntDir)){
            grunt.verbose.write('Load tasks from gruntDir ' + gruntDir);
            grunt.loadTasks(gruntDir);
        }

        //register all bundle tasks under a bigger one
        if(grunt.task.exists(extension.toLowerCase() + 'bundle')){
            bundleTasks.push(extension.toLowerCase() + 'bundle');
        }
        //register all sass tasks under a bigger one
        if(grunt.task.exists(extension.toLowerCase() + 'sass')){
            sassTasks.push(extension.toLowerCase() + 'sass');
        }
    });
    if(grunt.task.exists('qtiruntime')){
        bundleTasks.push('qtiruntime');
    }

    /*
     * Create task alias
     */
    grunt.registerTask('sassall', "Compile all sass files", sassTasks);
    grunt.registerTask('bundleall', "Compile all js files", bundleTasks);
    grunt.registerTask('build', "The full build sequence", ['bundleall', 'sassall']);
};
