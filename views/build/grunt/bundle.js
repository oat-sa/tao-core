// SPDX-FileCopyrightText: 2014-2026 Open Assessment Technologies S.A.
// Copyright (C) 2014-2026 (original work) Open Assessment Technologies S.A.
//
// SPDX-License-Identifier: GPL-2.0-only

/**
 * Main bundle configuration, as well as for the TAO extension
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
module.exports = function (grunt) {
    'use strict';

    const root = grunt.option('root');
    const workDir = grunt.option('output');
    const amd = require('../config/requirejs.build.json');

    grunt.config.merge({
        bundle: {
            //options that apply for all extensions
            options: {
                rootExtension: 'tao',
                rootPath: root,
                getExtensionPath: extension => `${root}/${extension}/views/js`,
                getExtensionCssPath: extension => `${root}/${extension}/views/css`,
                amd: {
                    ...amd,
                    exclude: [...amd.exclude, 'tao/ckeditor/wproofreaderBootstrap', 'tao/ckeditor/wproofreaderExclude']
                },
                workDir: workDir,
                outputDir: 'loader'
            },
            tao: {
                options: {
                    extension: 'tao',
                    amd,
                    babelPreTransform: { enabled: true },
                    bundles: [
                        {
                            name: 'vendor',
                            vendor: true,
                            include: ['loader/configRetry']
                        },
                        {
                            name: 'vendor.es5',
                            vendor: true,
                            babel: true,
                            include: ['loader/configRetry'],
                            targets: {
                                ie: '11'
                            }
                        },
                        {
                            name: 'login',
                            bootstrap: true,
                            babel: true,
                            entryPoint: 'controller/login',
                            excludeNested: ['tao/lockDownBrowser/lockDownBrowserService'],
                            dependencies: ['tao/loader/lockDownBrowser.min']
                        },
                        {
                            name: 'tao',
                            bootstrap: true,
                            default: true,
                            babel: true,
                            include: [
                                'layout/**/*',
                                'form/**/*',
                                'lock',
                                'report',
                                'users',
                                'serviceApi/**/*',
                                'generis.tree',
                                'generis.tree.select'
                            ]
                        },
                        {
                            name: 'lockDownBrowser',
                            babel: true,
                            include: ['lockDownBrowser/**/*']
                        }
                    ]
                }
            }
        }
    });

    // bundle task alias
    grunt.registerTask('taobundle', ['bundle:tao']);
};
