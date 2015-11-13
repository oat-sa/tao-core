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
    'tpl!ui/bulkActionPopup/select',
    'ui/component',
    'ui/modal',
    'select2'
], function($, _, __, layoutTpl, selectTpl, component){
    'use strict';

    var _defaults = {};

    var bulkActionPopup = {
    };

    function initCascadingComboBox($container, options){

        var $comboBox,
            selectedValues = {};

        function createCombox(level, categories){
            if(options.categoriesDefinitions[level]){
                var categoryDef = options.categoriesDefinitions[level];
                if(categoryDef.id){

                    //format categories
                    var _categories = _.map(categories, function(cat){
                        if(cat.categories){
                            //encode subcategory in json
                            cat.categories = JSON.stringify(cat.categories);
                        }
                        return cat;
                    });

                    //init <select> DOM element
                    var $comboBox = $(selectTpl({
                        comboboxId : categoryDef.id,
                        comboboxLabel : categoryDef.label || '',
                        options : _categories
                    }));

                    //add event handler
                    $comboBox.on('change', function(){

                        var $selected = $comboBox.find(":selected");
                        selectedValues[categoryDef.id] = $selected.attr("id");
                        $comboBox.nextAll('.cascading-combo-box').remove();

                        //trigger event
                        $comboBox.trigger('selected.cascading-combobox', [selectedValues]);

                        var subCategories = $selected.data('categories');
                        if(_.isArray(subCategories) && subCategories.length){
                            //init sub-level select box
                            var $subComboBox = createCombox(level + 1, subCategories);
                            if($subComboBox){
                                $comboBox.after($subComboBox);
                            }
                        }
                    });

                    //init select 2 on $comboBox
                    $comboBox.find('select').select2({
                        width : 'element',
                        placeholder : categoryDef.placeholder || __('select...'),
                        minimumResultsForSearch : -1
                    });

                    return $comboBox;
                }
            }else{
                throw 'missing category definition on level : ' + level;
            }
        }

        $comboBox = createCombox(0, options.categories);
        $container.append($comboBox);
    }

    function initModal($container){
        
        $container.addClass('modal').modal();
        
    }

    return function bulkActionPopupFactory(config){
        
        //modify the template
        if(config.allowedResources.length === 1){
            config.single = true;
        }

        return component(bulkActionPopup, _defaults)
            .setTemplate(layoutTpl)

            // uninstalls the component
            .on('destroy', function(){
                console.log('destroy stuff')
            })

            // renders the component
            .on('render', function(){
                initModal(this.getElement());
                initCascadingComboBox(this.getElement().find('.reason').children('.categories'), config);
            })
            .init(config);
    };
});