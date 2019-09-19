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
 * Upload assets to an s3 bucket
 *
 * Please ensure the file `tao/views/build/config/aws.json` contains the
 * correct bucket access configuration
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 *
 * @param {Object} grunt - the grunt instance
 */
module.exports = function(grunt) {
    'use strict';

    const awsConfig  = require('../config/aws.json');

    const compress = grunt.config('compress') || {};
    const awsS3    = grunt.config('aws_s3') || {};
    const clean    = grunt.config('clean') || {};
    const root     = grunt.option('root');
    const concurrency = grunt.option('s3-concurrency') || 20;                  // run the cli with --s3-concurrency=N
    const ext      = require('../tasks/helpers/extensions')(grunt, root);   //extension helper
    const out      = 'output';

    clean.s3 = [out];

    const patterns = [];
    ext.getExtensions().forEach(function(extension){
        patterns.push(`${extension}/views/js/**/*`);
        patterns.push(`${extension}/views/css/**/*`);
        patterns.push(`${extension}/views/img/**/*`);
        patterns.push(`${extension}/views/locales/**/*`);
        patterns.push(`${extension}/views/media/**/*`);
        patterns.push(`${extension}/views/node_modules/**/*`);
        patterns.push(`!${extension}/views/js/tessalutt/**/*`);
    });

    compress.s3 = {
        options: {
            mode: 'gzip',
            pretty: true
        },
        cwd : root,
        expand: true,
        src: patterns,
        dest: out
    };

    awsS3.options = {
        accessKeyId: awsConfig.s3.accessKeyId,
        secretAccessKey: awsConfig.s3.secretKey,
        region: awsConfig.s3.region,
        uploadConcurrency: concurrency,
        bucket: awsConfig.s3.bucket
    };
    awsS3.clean = {
        files: [{
            dest: `${awsConfig.s3.path}**/*`,
            action: 'delete'
        }]
    };
    awsS3.upload = {
        files: [{
            expand: true,
            cwd: out,
            src: patterns,
            dest : awsConfig.s3.path,
            params: {
                ContentEncoding: 'gzip'
            }
        }, {
            expand: true,
            cwd: root,
            src: patterns,
            dest : awsConfig.s3.path
        }]
    };

    grunt.loadNpmTasks('grunt-aws-s3');

    grunt.config('clean', clean);
    grunt.config('aws_s3', awsS3);
    grunt.config('compress', compress);
};
