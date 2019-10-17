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

/**
 * grunt eslint:file --file=/path/to/file/to/lint
 * grunt eslint:extension --extension=taoQtiTest
 */
module.exports = function(grunt) {
    'use strict';

    const formatterExtensions = {
        stylish : 'txt',
        checkstyle : 'xml',
        json : 'json',
        html : 'html'
    };

    const root             = grunt.option('root');
    const currentExtension = grunt.option('currentExtension') || 'tao';
    const extensionRoot    = `${root}/${currentExtension}`;
    const reportOutput     = grunt.option('reports') || 'reports';
    const format           = grunt.option('format') || 'checkstyle';
    const outputFile       = `${reportOutput}/${format.toUpperCase()}-${currentExtension}.${formatterExtensions[format]}`;


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
        `!${extensionRoot}/views/js/**/jquery.*.js`,
        `!${extensionRoot}/views/js/e2e/**/*.js`
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
                    format,
                    outputFile
                },
                src : extensionSrc
            }
        }
    });
};
