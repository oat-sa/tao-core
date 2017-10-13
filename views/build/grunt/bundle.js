module.exports = function (grunt) {
    'use strict';

    var requirejs = grunt.config('requirejs') || {};
    var clean     = grunt.config('clean') || {};
    var copy      = grunt.config('copy') || {};
    var root      = grunt.option('root');
    var libs      = grunt.option('mainlibs');
    var ext       = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out       = 'output';

    /**
     * Remove bundled and bundling files
     */
    clean.bundle = [out];

    /**
     * Compile into a bundle
     */
    requirejs.taoloaderloginbundle = {
        options: {
            exclude: ['json!i18ntr/messages.json'],
            include: ['controller/login', 'lib/require', 'loader/bootstrap'],
            out: out + '/tao/loader/login.min.js'
        }
    };

    requirejs.taoloaderbackofficebundle = {
        options: {
            exclude: ['json!i18ntr/messages.json', 'mathJax', 'ckeditor'],
            include: ['controller/backoffice', 'lib/require', 'loader/bootstrap'].concat(libs),
            out: out + '/tao/loader/backoffice.min.js'
        }
    };

    requirejs.taoloaderappbundle = {
        options: {
            exclude: ['json!i18ntr/messages.json'],
            include: ['controller/app', 'lib/require', 'loader/bootstrap'].concat(libs),
            out: out + '/tao/loader/app.min.js'
        }
    };

    requirejs.taocontrollersbundle = {
        options: {
            exclude: ['mathJax', 'controller/login', 'controller/backoffice'].concat(libs),
            include: ['controller/routes'].concat(ext.getExtensionsControllers(['tao'])),
            out: out + '/tao/controllers.min.js'
        }
    };

     /**
      * Copy to /dist
      */
    copy.taoloaderloginbundle = {
        files: [
            { src: [out + '/tao/loader/login.min.js'],     dest: root + '/tao/views/dist/loader/login.min.js' },
            { src: [out + '/tao/loader/login.min.js.map'], dest: root + '/tao/views/dist/loader/login.min.js.map' },
        ],
    };
    copy.taoloaderbackofficebundle = {
        files: [
            { src: [out + '/tao/loader/backoffice.min.js'],     dest: root + '/tao/views/dist/loader/backoffice.min.js' },
            { src: [out + '/tao/loader/backoffice.min.js.map'], dest: root + '/tao/views/dist/loader/backoffice.min.js.map' },
        ],
    };
    copy.taoloaderappbundle = {
        files: [
            { src: [out + '/tao/loader/app.min.js'],     dest: root + '/tao/views/dist/loader/app.min.js' },
            { src: [out + '/tao/loader/app.min.js.map'], dest: root + '/tao/views/dist/loader/app.min.js.map' },
        ],
    };
    copy.taocontrollersbundle = {
        files: [
            { src: [out + '/tao/controllers.min.js'],     dest: root + '/tao/views/dist/controllers.min.js' },
            { src: [out + '/tao/controllers.min.js.map'], dest: root + '/tao/views/dist/controllers.min.js.map' }
        ],
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    grunt.registerTask('taoloaderloginbundle', [ 'clean:bundle', 'requirejs:taoloaderloginbundle', 'copy:taoloaderloginbundle' ]);
    grunt.registerTask('taoloaderbackofficebundle', [ 'clean:bundle', 'requirejs:taoloaderbackofficebundle', 'copy:taoloaderbackofficebundle' ]);
    grunt.registerTask('taoloaderappbundle', [ 'clean:bundle', 'requirejs:taoloaderappbundle', 'copy:taoloaderappbundle' ]);
    grunt.registerTask('taocontrollersbundle', [ 'clean:bundle', 'requirejs:taocontrollersbundle', 'copy:taocontrollersbundle' ]);

    // bundle task
    grunt.registerTask('taobundle', [ 'taoloaderloginbundle', 'taoloaderbackofficebundle', 'taoloaderappbundle', 'taocontrollersbundle' ]);
};
