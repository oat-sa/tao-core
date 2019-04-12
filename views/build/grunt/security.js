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
    const extensionHelper = require('../tasks/helpers/extensions')(grunt, root);

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

    grunt.registerTask(`retireall`, `Retire check for all extension`, function() {
      grunt.config.merge({
          retire: {
              js: `${root}/**/*.js`, /** Which js-files to scan. **/
              options: {
                  outputFile: `./reports/all-reports.json`,
              }
          }
      });

      grunt.task.run(['retire']);
  });
};
