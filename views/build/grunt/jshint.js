module.exports = function(grunt) { 

    var jshint  = grunt.config('jshint') || {};
    var root    = grunt.option('root');
    var currentExtension = grunt.option('currentExtension');
    var extensionRoot = root + '/' + currentExtension + '/';

    jshint.options = {
        jshintrc : '.jshintrc'
    };
        
    /**
     * grunt jshint:file --file /path/to/file/to/lint
     */
    
    jshint.file = {
         src : grunt.option('file')
    };
         
    /**
     * grunt jshint:extension --extension taoQtiTest
     */ 
    jshint.extension = {
        src : [extensionRoot + '/views/js/**/*.js', '!' + extensionRoot + 'views/js/**/*.min.js', '!' + extensionRoot + 'views/js/test/**/*.js']
    };


    grunt.config('jshint', jshint);
};
