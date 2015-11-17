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

    var _ns = '.bulk-action-popup';

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
                        var _cat = _.clone(cat);
                        if(_cat.categories){
                            //encode subcategory in json
                            _cat.categories = JSON.stringify(_cat.categories);
                        }
                        return _cat;
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
                        
                        //clean previously created combo boxes
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
                        dropdownAutoWidth : true,
                        placeholder : categoryDef.placeholder || __('select...'),
                        minimumResultsForSearch : -1
                    });

                    return $comboBox;
                }
            }else{
                throw 'missing category definition on level : ' + level;
            }
        }

        if(_.isArray(options.categoriesDefinitions) && _.isArray(options.categories)){
            $comboBox = createCombox(0, options.categories);
            $container.append($comboBox);
        }
    }

    /**
     * Add the form into a popup and display it
     * @param {type} $container
     * @returns {undefined}
     */
    function initModal(instance){

        instance.getElement()
            .addClass('modal')
            .on('closed.modal', function(){
                //on shot only, on close, destroy the widget
                console.log('closed.modal');
                instance.destroy();
            })
            .modal();
    }

    function destroy(instance){
        instance.getElement().removeClass('modal').modal('destroy');
        instance.trigger('destroyed');
    }


    return function bulkActionPopupFactory(config){

        var state = {
            reasons : null,
            comment : ''
        };

        //modify the template
        config.resourceCount = config.allowedResources.length;
        if(config.resourceCount === 1){
            config.single = true;
        }

        return component(bulkActionPopup, _defaults)
            .setTemplate(layoutTpl)

            // uninstalls the component
            .on('destroy', function(){
                console.log('destroyed')
                this.getElement().removeClass('modal').modal('destroy');
            })

            // renders the component
            .on('render', function(){

                var self = this;
                var $element = this.getElement();

                initModal(this);
                initCascadingComboBox($element.find('.reason').children('.categories'), config);
                $element.on('selected.cascading-combobox' + _ns, function(e, reasons){
                    state.reasons = reasons;
                    self.trigger('change', state);
                }).on('change' + _ns, 'textarea', function(){
                    state.comment = $(this).text();
                    self.trigger('change', state);
                }).on('click', '.actions .done', function(e){
                    self.trigger('ok', state);
                    self.destroy();
                }).on('click', '.actions .cancel', function(e){
                    e.preventDefault();
                    self.trigger('cancel');
                    self.destroy();
                });
            })
            .init(config);
    };
});