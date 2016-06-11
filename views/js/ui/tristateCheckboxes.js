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
    'tpl!ui/tristateCheckboxes/list',
    'tpl!ui/tristateCheckboxes/li',
    'ui/tooltip'
], function ($, _, __, component, layoutTpl, elementTpl){
    'use strict';

    /**
     * Defines tristate checkboxes methods
     * @type {Object}
     */
    var tristateCheckboxes = {
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
        setValues : function setValues(values){
            var $list = this.getElement();
            var i = 0;
            var max = this.config.max;

            $list.find('input')
                .prop('checked', false)
                .removeAttr('checked')
                .prop('indeterminate', false);

            //priority to checked values
            if(_.isArray(values.checked)){
                _.each(values.checked, function (v){
                    var $input = $list.find('input[value="' + v + '"]');
                    if(max && i >= max){
                        return false;
                    }
                    if($input.length){
                        $input.prop('checked', true).attr('checked', 'checked');
                        i++;
                    }
                });
            }

            if(_.isArray(values.indeterminate)){
                _.each(values.indeterminate, function (v){
                    var $input = $list.find('input[value="' + v + '"]:not(:checked)');
                    if(max && i >= max){
                        return false;
                    }
                    if($input.length){
                        $input.prop('indeterminate', true);
                        i++;
                    }
                });
            }
        },
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
                            .removeAttr('checked')
                            .prop('indeterminate', false);
                    }

                    //finally, set the checked or indeterminate properties
                    if(data.checked){
                        $cbox.prop('checked', true).attr('checked', 'checked');
                    }else if(data.indeterminate){
                        $cbox.prop('indeterminate', true).addClass('indeterminate');
                    }
                }
            });
        }
    };

    /**
     * Builds an instance of the listBox manager
     * @param {Object} config
     * @param {jQuery|HTMLElement|String} [config.renderTo] - An optional container in which renders the component
     * @param {Boolean} [config.replace] - When the component is appended to its container, clears the place before
     * @param {Function} [config.countRenderer] - An optional callback applied on the list count before display
     * @returns {listBox}
     */
    return function tristateCheckboxesFactory(config){

        config = _.defaults(config || {}, {
            serial : _.uniqueId('tscb'),
            list : [],
            max : 0,
            maxMessage : __('Maximum selection reached')
        });

        return component(tristateCheckboxes)
            .setTemplate(layoutTpl)
            .on('render', function (){
                var self = this;
                var $list = this.getElement();
                $list.on('change', '.indeterminate', function (){
                    $(this).removeClass('indeterminate');
                }).on('change', function (e){
                    var $input;
                    var $icon;
                    if(self.config.max && $list.find('input:checked,input:indeterminate').length > self.config.max){

                        $input = $(e.target);
                        $icon = $input.siblings('.icon')
                            .addClass('cross')
                            .qtip({
                                theme : 'warning',
                                content : {
                                    text : self.config.maxMessage
                                }
                            }).qtip('show');

                        $icon.parent('label').on('mouseleave', function (){
                            $icon.qtip('destroy');
                        });

                        _.delay(function (){
                            $input.prop('checked', false).removeAttr('checked');
                            $icon.removeClass('cross');
                        }, 150);

                        return;
                    }
                    self.trigger('change', self.getValues());
                });
                this.setElements(this.config.list);
            })
            .init(config);
    };

});
