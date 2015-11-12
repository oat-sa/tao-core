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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'tpl!ui/bulkActionPopup/layout',
    'ui/component',
    'ui/modal'
], function ($, _, __, layoutTpl, component) {
    'use strict';
    
    var _defaults = {};
    
    var bulkActionPopup = {
        
    };
    
    return function bulkActionPopupFactory(config) {
        
        //modify the template
        if(config.allowedResources.length === 1){
            config.single = true;
        }
        
        return component(bulkActionPopup, _defaults)
                .setTemplate(layoutTpl)

                // uninstalls the component
                .on('destroy', function() {
                    console.log('destroy stuff')
                })

                // renders the component
                .on('render', function() {
                    console.log('rendered');
                })
                .init(config);
    };
});