module.exports = function(grunt) { 

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);

    /**
     * General options
     */
    requirejs.options = {
        optimize: 'uglify2',
        uglify2: {
            mangle : false,
            output: {
                max_line_len: 666
            }
        },
        //optimize : 'none',
        preserveLicenseComments: false,
        optimizeAllPluginResources: true,
        findNestedDependencies : true,
        skipDirOptimize: true,
        optimizeCss : 'none',
        buildCss : false,
        inlineText: true,
        skipPragmas : true,
        generateSourceMaps : true,
        removeCombined : true
   };

    clean.options =  {
        force : true
    };
    
    grunt.log.verbose.writeln('libs');
    grunt.log.verbose.writeln(libs);

    /**
     * Remove bundled and bundling files
     */
    clean.taobundle = ['output',  '../js/main.min.js', '../js/main.min.js.map', '../js/controllers.min.js'];
    
    /**
     * Compile tao files into a bundle 
     */
    requirejs.taobundle = {
        options: {
            baseUrl : '../js',
            dir : 'output',
            mainConfigFile : './config/requirejs.build.js',
            paths : { 'tao' : '.' },
            modules : [{
                name: 'main',
                include: ['lib/require'],
                deps : libs,
                exclude : ['json!i18ntr/messages.json',  'mathJax', 'mediaElement'],
            }, {
                name: 'controller/routes',
                include : ext.getExtensionsControllers(['tao']),
                exclude : ['mathJax', 'mediaElement'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taobundle = {
        files: [
            { src: ['output/main.js'],  dest: '../js/main.min.js' },
            { src: ['output/main.js.map'],  dest: '../js/main.min.js.map' },
            { src: ['output/controller/routes.js'],  dest: '../js/controllers.min.js' },
            { src: ['output/controller/routes.js.map'],  dest: '../js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taobundle', ['clean:taobundle', 'requirejs:taobundle', 'copy:taobundle']);
};
