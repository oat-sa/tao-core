/**
 * grunt eslint:file --file=/path/to/file/to/lint
 * grunt eslint:extension --extension=taoQtiTest
 */
module.exports = function(grunt) {
    'use strict';

    var root    = grunt.option('root');
    var currentExtension = grunt.option('currentExtension');
    var reportOutput = grunt.option('reports') || 'reports';
    var extensionRoot = root + '/' + currentExtension + '/';
    var extensionSrc = [
        extensionRoot + '/views/js/**/*.js',
        '!' + extensionRoot + 'views/js/**/*.min.js',
        '!' + extensionRoot + 'views/js/**/*.src.js',
        '!' + extensionRoot + 'views/js/test/**/*.js',
        '!' + extensionRoot + 'views/js/lib/**/*.js',
        '!' + extensionRoot + 'views/js/legacyPortableSharedLib/**/*.js',
        '!' + extensionRoot + 'views/js/portableLib/**/*.js',
        '!' + extensionRoot + 'views/js/pciCreator/dev/**/*.js',
        '!' + extensionRoot + 'views/js/picCreator/dev/**/*.js',
        '!' + extensionRoot + 'views/js/**/jquery.*.js'
    ];

    grunt.config.merge({
        eslint : {
            options : {
                configFile: '.eslintrc.json',
            },
            file : {
                src : grunt.option('file')
            },
            extension : {
                src : extensionSrc
            },
            extensionreport : {
                options : {
                    format: 'checkstyle',
                    outputFile:  reportOutput + '/' + currentExtension + '-checkstyle.xml'
                },
                src : extensionSrc
            }
        }
    });
};
