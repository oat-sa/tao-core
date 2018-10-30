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
 * Copyright (c) 2014-2018 (original work) Open Assessment Technlogies SA
 *
 */

/**
 * Main bundle configuration, as well as for the TAO extension
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
module.exports = function(grunt) {
    'use strict';
    const root        = grunt.option('root');
    const workDir     = grunt.option('output');

    grunt.config.merge({
        bundle : {
            //options that apply for all extensions
            options: {
                rootExtension        : 'tao',
                getExtensionPath     : extension => `${root}/${extension}/views/js`,
                getExtensionCssPath  : extension => `${root}/${extension}/views/css`,
                amd                  : require('../config/requirejs.build.json'),
                workDir              : workDir,
                outputDir            : 'loader'
            },

            tao : {
                options : {
                    extension : 'tao',
                    bundles : [{
                        name   : 'vendor',
                        vendor : true
                    }, {
                        name      : 'login',
                        bootstrap : true,
                        entryPoint: 'controller/login'
                    }, {
                        name      : 'tao',
                        bootstrap : true,
                        default   : true,
                        include   : [
                            'layout/**/*',
                            'form/**/*',
                            'lock',
                            'report',
                            'users',
                            'serviceApi/**/*',
                            'generis.tree',
                            'generis.tree.select'
                        ]
                    }]
                }
            }
        }
    });

    // bundle task alias
    grunt.registerTask('taobundle', ['bundle:tao']);
};
