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
 * Copyright (c) 2014-2022 (original work) Open Assessment Technlogies SA
 *
 */
const path = require('path');

module.exports = function(grunt, root) {

    return {

        getExtensions(clientSide) {
            const options = {
                cwd: root,
                filter: function(src) {
                    return grunt.file.isDir(src) && grunt.file.exists(`${src}/manifest.php`) &&
                            (!clientSide || grunt.file.exists(`${src}/views/js/controller/routes.js`));
                }
            };
            return grunt.file.expand(options, '*');
        },

        getExtensionPath(extension) {
            extension = extension || 'tao';
            return `${root}/${extension}`;
        },

        getExtensionSources(extension, filePattern, amdify) {
            const extPath = this.getExtensionPath(extension);

            const jsSources = grunt.file.expand({cwd: extPath}, filePattern);
            jsSources.forEach(function(source, index) {
                let path = `${extPath}/${source}`;
                if(amdify && amdify === true){
                    path = path.replace(`${extPath}/views/js`, extension === 'tao' ? '': extension).replace(/\.js$/, '').replace(/^\//, '');
                }
                jsSources[index] = path;

            });
            return jsSources;
        },

        getExtensionsSources(filePattern, amdify){
            const sources = [];
            this.getExtensions(true).forEach(extension => {
                sources = sources.concat(this.getExtensionSources(extension, filePattern, amdify));
            });
            return sources;
        },

        getExtensionsPaths(extensions){
            const paths = { };
            extensions = extensions || this.getExtensions(true);
            extensions.forEach( extension => {
                const jsPath = `${this.getExtensionPath(extension)}/views/js`;
                const cssPath = `${this.getExtensionPath(extension)}/views/css`;
                if(grunt.file.exists(jsPath)){
                    paths[extension] = path.relative('../js', jsPath);
                }
                if(grunt.file.exists(cssPath)){
                    paths[`${extension}Css`] = path.relative('../js', cssPath);
                }
            });
            return paths;
        },

        // parse a 'paths.json' file in each extension, and if it exists,
        // append its contents to a flat object
        getExtensionsExtraPaths(extensions = this.getExtensions(true)) {
            return extensions.reduce((extraPaths, extension) => {
                try {
                    return {...extraPaths, ...require(path.join(this.getExtensionPath(extension), 'views', 'build', 'grunt', 'paths.json'))};
                } catch(e) {
                    return extraPaths;
                }
            }, {});
        },

        /**
         * @deprecated
         */
       getExtensionsControllers(extensions){

           console.log('The function `getExtensionsControllers` is deprecated');

           var self = this;
           var modules = [];
           extensions = extensions || self.getExtensions(true);
           extensions.forEach(function(extension){
                var extPath = self.getExtensionPath(extension);
                modules = modules.concat(self.getExtensionSources(extension, 'views/js/controller/**/*.js').map(function(source){
                    return source.replace(extPath + '/views/js',  extension === 'tao' ? '': extension).replace(/\.js$/, '').replace(/^\//, '');
                }));
            });
            return modules;
       }
   };
};

