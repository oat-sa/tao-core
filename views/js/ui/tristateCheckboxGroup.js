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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'ui/component',
    'tpl!ui/tristateCheckboxGroup/list',
    'tpl!ui/tristateCheckboxGroup/li',
    'ui/tooltip'
], function ($, _, __, component, layoutTpl, elementTpl, tooltip){
    'use strict';

    /**
     * Defines tristate checkboxes methods
     * @type {Object}
     */
    var tristateCheckboxGroup = {
        /**
         * Get the value of tristateCheckboxGroup
         *
         * @returns {Object}
         *          {array} values.checked - checkbox in checked state
         *          {array} values.indeterminate - checkbox in intermediate state
         */
        getValues : function getValues(){

            var values = {checked : [], indeterminate : []};
            var $list = this.getElement();

            $list.find('input:checked').each(function (){
                values.checked.push($(this).val());
            });
            $list.find('input:indeterminate').each(function (){
                values.indeterminate.push($(this).val());
            });

            return values;
        },
        /**
         * Set the checked/indeterminate state of the tristateCheckboxGroup
         *
         * @param {Object} values
         * @param {array} [values.checked] - checkbox in checked state
         * @param {array} [values.indeterminate] - checkbox in intermediate state
         * @returns {tristateCheckboxGroup}
         */
        setValues : function setValues(values){

            var $list = this.getElement();

            $list.find('input')
                .prop('checked', false)
                .prop('indeterminate', false);

            //priority to checked values
            if(_.isArray(values.checked)){
                _.each(values.checked, function (v){
                    $list.find('input[value="' + v + '"]').prop('checked', true);
                });
            }

            if(_.isArray(values.indeterminate)){
                _.each(values.indeterminate, function (v){
                    $list.find('input[value="' + v + '"]:not(:checked)').prop('indeterminate', true);
                });
            }

            return this;
        },
        /**
         * Set checkbox elements
         * The given checkbox element "value" is used as a key.
         * If the key already exists, the existing checkbox element will updated.
         * If not, a new checkbox element will be created and appended to the list.
         *
         * @param {Array} elements
         * @returns {tristateCheckboxGroup}
         */
        setElements : function setElements(elements){
            var $list = this.getElement();
            var self = this;
            var $cbox;
            _.each(elements, function (data){
                if(data){

                    //try to find if the value is already set
                    $cbox = $list.find('input[value="' + data.value + '"]');
                    if(!$cbox.length){
                        //does not exist, create one
                        data.serial = self.config.serial;
                        $cbox = $(elementTpl(data)).appendTo($list).find('input');
                    }else{
                        if(data.label){
                            //if already exists, check if label needs to be updated
                            $cbox.siblings('.label').text(data.label);
                        }
                        $cbox.find('input')
                            .prop('checked', false)
                            .prop('indeterminate', false);
                    }

                    //finally, set the checked or indeterminate properties
                    if(data.checked){
                        $cbox.prop('checked', true);
                    }else if(data.indeterminate){
                        $cbox.prop('indeterminate', true);
                    }
                }
            });
            return this;
        },
        /**
         * tooltip instance integrated in checkbox group
         * will be defined with initialization
         */
        tooltip: null
    };

    /**
     * Builds an instance of tristateCheckboxGroup
     *
     * @param {Object} config
     * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
     * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
     * @param {String} [config.serial] - The unique string to uniquely identify the checkbox group
     * @param {Array} [config.list] - Default list of checkbox element to be rendered
     * @param {String} [config.list[].value] - the value (used as key) of the checkbox element
     * @param {String} [config.list[].label] - the label of the checkbox element
     * @param {Boolean} [config.list[].checked] - the checkbox element is initial checked or not
     * @param {Boolean} [config.list[].indeterminate] - the checkbox element is initial indeterminate or not
     * @param {String} [config.serial] - the unique string to uniquely identify the checkbox group
     * @param {String} [config.maxSelection] - the maximum number of selectable checkboxes
     * @param {String} [config.maxMessage] - the message that will be displayed in the tooltip if the maxSelection is reached
     * @returns {listBox}
     */
    return function tristateCheckboxGroupFactory(config){

        config = _.defaults(config || {}, {
            serial : _.uniqueId('tscb'),
            list : [],
            maxSelection : 0,
            maxMessage : __('Maximum selection reached')
        });

        return component(tristateCheckboxGroup)
            .setTemplate(layoutTpl)
            .on('render', function (){

                var self = this;
                var $list = this.getElement();

                $list.on('change', function (e){
                    var $input;
                    var $icon;
                    var maxSelection = self.config.maxSelection;

                    if(maxSelection && $list.find('input:checked,input:indeterminate').length > maxSelection){

                        $input = $(e.target);

                        if($input.is(':checked')){

                            $icon = $input.siblings('.icon')
                                .addClass('cross')
                                .each(function( ) {
                                    self.tooltip = tooltip.warning(this, self.config.maxMessage);
                                    self.tooltip.show();

                                });

                            $icon.parent('label').on('mouseleave', function (){
                                if(self.tooltip){
                                    self.tooltip.dispose();
                                }
                            });

                            //visually highlight the invalid new choice
                            _.delay(function (){
                                $input.prop('checked', false);
                                $icon.removeClass('cross');
                            }, 150);
                        }

                        return;
                    }

                    self.trigger('change', self.getValues());
                });

                this.setElements(this.config.list);
            })
            .init(config);
    };

});
