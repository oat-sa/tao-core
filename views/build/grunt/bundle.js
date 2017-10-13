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
    requirejs.taobundle_login = {
        options: {
            exclude: ['json!i18ntr/messages.json'],
            include: ['controller/login', 'lib/require', 'loader/bootstrap'],
            out: out + '/tao/loader/login.min.js'
        }
    };

    requirejs.taobundle_backoffice = {
        options: {
            exclude: ['json!i18ntr/messages.json', 'mathJax', 'ckeditor'],
            include: ['controller/backoffice', 'lib/require', 'loader/bootstrap'].concat(libs),
            out: out + '/tao/loader/backoffice.min.js'
        }
    };

    requirejs.taobundle_app = {
        options: {
            exclude: ['json!i18ntr/messages.json'],
            include: ['controller/app', 'lib/require', 'loader/bootstrap'].concat(libs),
            out: out + '/tao/loader/app.min.js'
        }
    };

    requirejs.taobundle = {
        options: {
            exclude: ['mathJax', 'controller/login', 'controller/backoffice', 'controller/app'].concat(libs),
            include: ext.getExtensionsControllers(['tao']),
            out: out + '/tao/controllers.min.js'
        }
    };

     /**
      * Copy to /dist
      */
    copy.taobundle = {
        files: [
            { src: [out + '/tao/loader/login.min.js'],          dest: root + '/tao/views/dist/loader/login.min.js' },
            { src: [out + '/tao/loader/login.min.js.map'],      dest: root + '/tao/views/dist/loader/login.min.js.map' },
            { src: [out + '/tao/loader/backoffice.min.js'],     dest: root + '/tao/views/dist/loader/backoffice.min.js' },
            { src: [out + '/tao/loader/backoffice.min.js.map'], dest: root + '/tao/views/dist/loader/backoffice.min.js.map' },
            { src: [out + '/tao/loader/app.min.js'],            dest: root + '/tao/views/dist/loader/app.min.js' },
            { src: [out + '/tao/loader/app.min.js.map'],        dest: root + '/tao/views/dist/loader/app.min.js.map' },
            { src: [out + '/tao/controllers.min.js'],           dest: root + '/tao/views/dist/controllers.min.js' },
            { src: [out + '/tao/controllers.min.js.map'],       dest: root + '/tao/views/dist/controllers.min.js.map' }
        ],
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taobundle', [
        'clean:bundle',
        'requirejs:taobundle_login',
        'requirejs:taobundle_backoffice',
        'requirejs:taobundle_app',
        'requirejs:taobundle',
        'copy:taobundle'
    ]);
};
