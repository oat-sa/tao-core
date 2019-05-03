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
 * It's grunt module for creating Retire tasks for all extensions
 *
 * You can to know more about Retire.js by link below
 * @see {@link https://github.com/RetireJS}
 *
 * @author Aliaksandr Katovich <aliaksandr@taotesting.com>
 */
module.exports = function(grunt) {
    'use strict';
    const root = grunt.option('root');
    const currentExtension = grunt.option('currentExtension');
    const reportOutput = grunt.option('reports') || 'reports';

    grunt.registerTask(
      'retire:extension',
      'Scanner detecting the use of JavaScript libraries with known vulnerabilities. ' +
      'Use it with flag "--extension=extensionName".', function () {
        const retireCheckPath = `${root}/${currentExtension}/**/*.js`;
        const retireOutputFilePath = `./${reportOutput}/${currentExtension}-retire.json`;

        grunt.config.merge({
            retire: {
                js: retireCheckPath,
                options: {
                    outputFile: retireOutputFilePath,
                    verbose: false,
                },
            }
        });

        grunt.task.run(['retire']);
      }
    );

    grunt.registerTask(
      'retire:all',
      'Scanner detecting the use of JavaScript libraries with known vulnerabilities. ' +
      'Use it for checking all extensions.', function () {
        const retireCheckPath = ``;


        grunt.config.merge({
            retire: {
                js: `${root}/**/*.js`,
                options: {
                    outputFile: `./${reportOutput}/all-retire.json`,
                    verbose: false,
                },
            }
        });

        grunt.task.run(['retire']);
      }
    );
};
