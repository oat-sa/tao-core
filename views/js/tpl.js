/**
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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
/**
 * ORGINAL VERSION:
 * https://github.com/epeli/requirejs-hbs
 * Copyright 2013 Esa-Matti Suuronen
 * MIT License : https://github.com/epeli/requirejs-hbs/blob/master/LICENSE
 * 
 * MODIFIED VERSION:
 * @author Bertrand Chevrier <bertrand@taotesting.com> for OAT SA
 * - Minor code refactoring
 * - Add the i18n helper
 */
define(['handlebars', 'i18n', 'lodash'], function(Handlebars, __, _){
    var buildMap = {};
    var extension = '.tpl';

    //register a i18n helper
    Handlebars.registerHelper('__', function(key){
        return __(key);
    });

    //register join helper
    Handlebars.registerHelper('join', function(attr, glue, delimiter, wrapper){
        var ret = '', value = '';
        
        //set default arguments with the format: name1="value1" name2="value2"
        glue = typeof(glue) === 'string' ? glue : '=';
        delimiter = typeof(delimiter) === 'string' ? delimiter : ' ';
        wrapper = typeof(wrapper) === 'string' ? wrapper : '"';

        if(typeof(attr) === 'object'){
            for(var name in attr){
                value = attr[name];
                if(value !== null || value !== undefined){
                    if(typeof(value) === 'boolean'){
                        value = value ? 'true' : 'false';
                    }else if(typeof(value) === 'object'){
                        value = _.values(value).join(' ');
                    }
                }else{
                    value = '';
                }
                ret += name + glue + wrapper + value + wrapper + delimiter;
            }
             ret.substring(0, ret.length - 1);
        }

        return ret;
    });

    return {
        load : function(name, req, onload, config){
            extension = extension || config.extension;

            if(config.isBuild){
                //optimization, r.js node.js version
                buildMap[name] = fs.readFileSync(req.toUrl(name + extension)).toString();
                onload();

            }else{
                req(["text!" + name + extension], function(raw){
                    // Just return the compiled template
                    onload(Handlebars.compile(raw));
                });
            }
        },
        write : function(pluginName, moduleName, write){
            if(moduleName in buildMap){
                var compiled = Handlebars.precompile(buildMap[moduleName]);
                // Write out precompiled version of the template function as AMD definition.
                write(
                    "define('tpl!" + moduleName + "', ['handlebars'], function(Handlebars){ \n" +
                    "return Handlebars.template(" + compiled.toString() + ");\n" +
                    "});\n"
                    );
            }
        }
    };
});