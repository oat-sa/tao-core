/**
 * grunt eslint:file --file=/path/to/file/to/lint
 * grunt eslint:extension --extension=taoQtiTest
 */

module.exports = function(grunt) {

    const root             = grunt.option('root');
    const currentExtension = grunt.option('currentExtension') || 'tao';
    const reportOutput     = grunt.option('reports') || 'reports';
    const reportFormat     = grunt.option('format') || 'checkstyle';
    const extensionRoot    = `${root}/${currentExtension}`;

    var extensionSrc = [
        extensionRoot + '/views/js/**/*.js',
        `!${extensionRoot}/views/js/**/*.min.js`,
        `!${extensionRoot}/views/js/**/*.src.js`,
        `!${extensionRoot}/views/js/test/**/*.js`,
        `!${extensionRoot}/views/js/lib/**/*.js`,
        `!${extensionRoot}/views/js/legacyPortableSharedLib/**/*.js`,
        `!${extensionRoot}/views/js/portableLib/**/*.js`,
        `!${extensionRoot}/views/js/pciCreator/dev/**/*.js`,
        `!${extensionRoot}/views/js/picCreator/dev/**/*.js`,
        `!${extensionRoot}/views/js/**/jquery.*.js`
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
                    format: reportFormat,
                    outputFile:  `${reportOutput}/${reportFormat.toUpperCase()}-${currentExtension}.xml`
                },
                src : extensionSrc
            }
        }
    });
};
