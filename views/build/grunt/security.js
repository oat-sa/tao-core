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
 * @author Aliaksandr Katovich <aliaksandr@taotesting.com>
 */
module.exports = function(grunt) {
    'use strict';
    const root = grunt.option('root');
    const extensionHelper = grunt.option('extensionHelper'); // return all ext names

    grunt.loadNpmTasks('grunt-retire');

    extensionHelper.getExtensions().forEach(extName => {
        const lowerCaseExtName = extName.toLowerCase();

        grunt.registerTask(`retire${lowerCaseExtName}`, `Retire check for ${extName} extension`, function() {
            grunt.config.merge({
                retire: {
                    js: `${root}/${extName}/**/*.js`, /** Which js-files to scan. **/
                    options: {
                        outputFile: `./reports/${extName}-reports.json`,
                    }
                }
            });

            grunt.task.run(['retire']);
        });
    });
};
