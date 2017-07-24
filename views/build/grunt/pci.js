module.exports = function (grunt) {
    'use strict';

    var Promise = require('pinkie-promise');
    var requirejs = require('requirejs');
    var root = grunt.option('root');

    grunt.config.merge({
        compilepci: {
            options: {
                optimize: 'uglify2',
                uglify2: {
                    mangle: false,
                    output: {
                        'max_line_len': 666
                    }
                },
                preserveLicenseComments: false,
                optimizeAllPluginResources: true,
                findNestedDependencies: true,
                skipDirOptimize: true,
                optimizeCss: 'none',
                buildCss: false,
                inlineText: true,
                skipPragmas: true,
                generateSourceMaps: true,
                removeCombined: true,
                baseUrl: '../js',
                mainConfigFile: './config/requirejs.build.js',
                excludeShallow: ['mathJax'],
                exclude: ['qtiCustomInteractionContext'],
                paths: {
                    'taoQtiItem': root + '/taoQtiItem/views/js',
                    'qtiCustomInteractionContext': root + '/taoQtiItem/views/js/runtime/qtiCustomInteractionContext'
                }
            }
        }
    });

    function getHookFileName(pciRuntimeData, prefix) {
        if (Array.isArray(pciRuntimeData.src) && pciRuntimeData.src.length > 0) {
            //by convention the first module is the hook file
            return pciRuntimeData.src[0]
                .replace(/\.js$/i, '')
                .replace(/^\.\//, prefix + '/');
        }
    }

    function getMinHookFile(pciRuntimeData) {
        var minHookFile;
        if (pciRuntimeData.hook) {
            minHookFile = pciRuntimeData.hook;
        }
        if (Array.isArray(pciRuntimeData.libraries) && pciRuntimeData.libraries.length > 0) {
            //by convention the first module is the min file
            minHookFile = pciRuntimeData.libraries[0];
        }
        if(minHookFile){
            return minHookFile.replace(/^\.\//, '');
        }
    }

    function printReport(report) {
        if(Array.isArray(report)){
            report.forEach(function (r) {
                printReport(r);
            });
        }else if(typeof report === 'function'){
            report.call();
        }
    }

    grunt.registerTask('compilepci', 'Compile PCIs', function () {

        var done = this.async();//async mode because requirejs optimization is an async process
        var extension = grunt.option('e');
        var selectedId = grunt.option('i');
        var manifests = grunt.file.expand(root + '/' + extension + '/views/js/pciCreator/**/pciCreator.json');
        var self = this;
        var compileTasks;

        if(!extension){
            grunt.log.error('Missing the extension in param, e.g. "grunt compilepci -e=qtiItemPci"');
            return done();
        }

        grunt.log.writeln('Started optimizing PCIs in extension "' + extension + '"');

        if(selectedId){
            grunt.log.writeln('Looking for PCI "' + selectedId + '" only');
        }

        compileTasks = manifests.map(function(file){
            return new Promise(function (resolve, reject) {
                var dir = file.replace('/pciCreator.json', '');
                var report = [];
                var manifest = grunt.file.readJSON(file);
                var id = manifest.typeIdentifier;
                var config;
                var runtimeHook = getHookFileName(manifest.runtime, id);
                var minRuntimeFile = getMinHookFile(manifest.runtime);

                if(selectedId && selectedId !== id){
                    //not the targeted one
                    return resolve(report);
                }

                report.push(grunt.log.subhead.bind(null, 'PCI "' + id + '" found in manifest "' + file + '" ...'));

                if (!runtimeHook) {
                    report.push(grunt.log.ok.bind(null, 'No source file for PCI "' + id + '"'));
                    return resolve(report);
                }

                config = self.options({
                    name: runtimeHook,
                    out: dir + '/' + minRuntimeFile,
                    wrap: {
                        start: '',
                        end: "define(['" + runtimeHook + "'],function(pci){return pci;});"
                    },
                });
                config.paths[id] = dir;

                requirejs.optimize(config, function (buildResponse) {
                    report.push(grunt.log.ok.bind(null, 'PCI "' + id + '" compiled'));
                    report.push(grunt.log.writeln.bind(null, buildResponse));
                    resolve(report);
                }, function (err) {
                    report.push(grunt.log.error.bind(null, 'PCI "' + id + '" cannot be compiled'));
                    report.push(grunt.log.error.bind(null, err));
                    reject(report);
                });
            });
        });

        if (compileTasks.length) {
            Promise.all(compileTasks).then(function (report) {
                printReport(report);
                done();
            }).catch(function (report) {
                printReport(report);
                done();
            });
        } else {
            grunt.log.writeln('no PCI to be compiled in extension "'+extension+'"');
            done();
        }
    });
};
