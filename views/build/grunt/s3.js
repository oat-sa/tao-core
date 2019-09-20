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
 * Configure the bucket:
 * Using either  env variables :
 *  AWS_S3_ACESS_KEY,
 *  AWS_S3_SECRET_KEY,
 *  AWS_S3_REGION,
 *  AWS_S3_BUCKET,
 *  AWS_S3_PATH
 * Or
 *  copy tao/views/build/config/aws.json.sample to tao/views/build/config/aws.json
 *  and fill the configuration
 *
 * How to upload assets on S3 :
 *  cd tao/views/build
 *  npm ci
 *  npx grunt clean:s3 compress:s3 aws_s3:upload
 *
 * The upload concurrency can be changed using
 *  npx grunt clean:s3 compress:s3 aws_s3:upload --s3-concurrency=N
 *
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 *
 * @param {Object} grunt - the grunt instance
 */
module.exports = function(grunt) {
    'use strict';

    const root     = grunt.option('root');

    const ext      = require('../tasks/helpers/extensions')(grunt, root);   //extension helper
    const out      = 'output';

    let awsConfig = { s3 : {} };
    try {
        awsConfig  = require('../config/aws.json');
    } catch(err){
        grunt.log.debug('AWS configuration file not found');
    }

    const s3Config = {
        accessKeyId : process.env.AWS_S3_ACESS_KEY || awsConfig.s3.accessKeyId,
        secretAccessKey :  process.env.AWS_S3_SECRET_KEY || awsConfig.s3.secretKey,
        region :  process.env.AWS_S3_REGION || awsConfig.s3.region,
        bucket : process.env.AWS_S3_BUCKET || awsConfig.s3.bucket,
        path : process.env.AWS_S3_PATH || awsConfig.s3.path
    };
    const uploadConcurrency = grunt.option('s3-concurrency') || 20;

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

    grunt.config.merge({
        clean : {
            s3 : [out]
        },

        compress: {
            s3 : {
                options: {
                    mode: 'gzip',
                    pretty: true
                },
                cwd : root,
                expand: true,
                src: patterns,
                dest: out
            }
        },

        aws_s3 : {
            options : {
                uploadConcurrency,
                ...s3Config
            },
            clean : {
                files: [{
                    dest: `${s3Config.path}**/*`,
                    action: 'delete'
                }]
            },
            upload : {
                files: [{
                    expand: true,
                    cwd: out,
                    src: patterns,
                    dest : s3Config.path,
                    params: {
                        ContentEncoding: 'gzip'
                    }
                }, {
                    expand: true,
                    cwd: root,
                    src: patterns,
                    dest : s3Config.path
                }]
            }
        }
    });

    grunt.loadNpmTasks('grunt-aws-s3');
};
