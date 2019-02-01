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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA ;
 */

define([
    'jquery',
    'i18n',
    'ui/feedback'
], function ($, __, feedback) {
    'use strict';

    /**
     * Add a field with URI of an item etc and a button to copy it to the clipboard
     * @param $container
     * @private
     */
    function _idToClipboard($container) {
        // Note: isInstanceForm will not work with jquery|querySelector
        var isInstanceForm = document.getElementById('tao.forms.instance'),
            $idField = $container.find('#id'),
            value = $idField.val(),
            $copyField,
            $label = $('<span>', {class: 'form_desc', text: __('Resource Identifier')}),
            $button = $('<span>', {class: 'icon-clipboard clipboard-command', title: __('Copy to clipboard')})
                .on('click', function () {
                    var success;
                    try {
                        $copyField.select();
                        success = document.execCommand('copy');
                        $copyField.blur();
                        if (success) {
                            feedback().success(__('Resource Identifier has been copied to the clipboard'));
                        } else {
                            feedback().error(__('Resource Identifier could not be copied to the clipboard'));
                        }
                    } catch (err) {
                        feedback().error(__('Your browser does not support copying to the clipboard'));
                    }
                }),
            $fieldBox = $('<span>', {class: 'uri-container'});

        // if the field has already been added
        if ($('.uri-container').length) {
            return;
        }

        if (!isInstanceForm || !$idField.length) {
            return;
        }

        $copyField = $idField.clone()
        // To make MS browsers happy, value needs to be removed and re-added
            .val('')
            .attr({readonly: true, type: 'text'});
        $idField.remove();
        $fieldBox.append([$copyField, $button]);

        $container.find('div')
            .first()
            .after($('<div>')
                .append([$label, $fieldBox]));
        $fieldBox.height($copyField.outerHeight());

        $copyField.addClass('final')
        // re-add value
            .val(value);
    }


    /**
     * Toggle availability of mode switch (advanced/simple)
     *
     * @param newMode
     * @private
     */
    function _toggleModeBtn(newMode) {
        var $modeToggle = $('.property-mode');
        if (newMode === 'disabled') {
            $modeToggle.addClass('disabled');
        } else {
            $modeToggle.removeClass('disabled');
        }
    }

    /**
     * Reposition the radio buttons or checkboxes of a property and make them look nice.
     *
     * @param $container the container in which to search and upgrade buttons
     * @param type string the type of input we want to upgrade 'checkbox' or 'radio' by default we use radio
     * @private
     */
    function _upgradeButtons($container, type) {

        //if the type is not radio or checkbox we put by default radio
        if (type !== 'radio' && type !== 'checkbox') {
            type = 'radio';
        }
        var selector = '.form_checklst';
        var notSelector = '';
        if (type === 'radio') {
            selector = '.form_radlst';
            notSelector = '.form_checklst, ';
        }

        $container.find(selector).not(notSelector + '.property-' + type + '-list').each(function () {
            var $radioList = $(this);
            $radioList.addClass('property-' + type + '-list');
            $radioList.parent().addClass('property-' + type + '-list-box');
            $radioList.each(function () {
                var $block = $(this),
                    $inputs = $block.find('input');

                if ($inputs.length <= 2) {
                    $block.find('br').remove();
                }

                $inputs.each(function () {
                    var $input = $(this),
                        $label = $block.find('label[for="' + this.id + '"]'),
                        $icon = $('<span>', {'class': 'icon-' + type});

                    $label.prepend($icon);
                    $label.prepend($input);
                });
            });
        });
    }


    /**
     * Get reference to property container. If it doesn't' exist create one and add it to the DOM.
     *
     * @returns {*|HTMLElement}
     */
    function getPropertyContainer() {
        var $propertyContainer = $('.content-block .property-container');
        if ($propertyContainer.length) {
            return $propertyContainer;
        }
        $propertyContainer = $('<div>', {'class': 'property-container'});
        $('.content-block .form-group').first().before($propertyContainer);
        return $propertyContainer;
    }


    /**
     * Add properties to the designated container. Also add some CSS classes for easier access.
     *
     * @param $properties
     * @private
     */
    function _wrapPropsInContainer($properties) {
        var $propertyContainer = getPropertyContainer(),
            // the reason why this is not done via a simple counter is that
            // the function could have been called multiple times, e.g. when
            // properties are created dynamically.
            hasAlreadyProperties = !!$propertyContainer.find('.property-block').length;


        $properties.each(function () {
            var $property = $(this);
            if ($property.attr !== undefined) {
                var type = (function () {
                    var $propertyMode = $('.property-mode');

                    switch ($property.attr('id').replace(/_?property_[\d]+/, '')) {
                        case 'ro':
                            return 'readonly-property';
                        case 'parent':
                            return 'parent-property';
                        default:
                            var $editIcon = $property.find('.icon-edit'),
                                $editContainer = $property.children('div:first');

                            var $indexIcon = $property.find('.icon-find');

                            $editContainer.addClass('property-edit-container');


                            _hideProperties($editContainer);
                            _hideIndexes($editContainer);

                            if ($propertyMode.hasClass('property-mode-simple')) {
                                $indexIcon.hide();
                            } else if ($propertyMode.hasClass('property-mode-advanced')) {
                                $indexIcon.show();
                            }

                            //on click on edit icon show property form or hide it
                            $editIcon.on('click', function () {
                                //form is close so open it (hide index, show property)
                                if (!$editContainer.parent().hasClass('property-edit-container-open')) {
                                    //hide index and show properties
                                    _hideIndexes($editContainer);
                                    _showProperties($editContainer);
                                    $editContainer.slideToggle(function () {
                                        $editContainer.parent().toggleClass('property-edit-container-open');
                                    });
                                }
                                //it is open so switch between index and property or close it
                                else {
                                    // close form
                                    if ($($('.property', $editContainer)[0]).is(':visible')) {
                                        $editContainer.slideToggle(function () {
                                            $editContainer.parent().toggleClass('property-edit-container-open');
                                            //hide properties
                                            _hideProperties($editContainer);
                                        });
                                    }
                                    // hide index and show properties
                                    else {
                                        //hide index properties
                                        _hideIndexes($editContainer);
                                        //show properties
                                        _showProperties($editContainer);
                                    }
                                }
                            });

                            //on click on index icon show index form or hide it
                            $indexIcon.on('click', function () {
                                //if form property is simple we can show index form
                                if ($('.property-mode').hasClass('property-mode-advanced')) {
                                    //form is close so open it (hide property, show index)
                                    if (!$editContainer.parent().hasClass('property-edit-container-open')) {
                                        //hide index and show properties
                                        _hideProperties($editContainer);
                                        _showIndexes($editContainer);
                                        $editContainer.slideToggle(function () {
                                            $editContainer.parent().toggleClass('property-edit-container-open');
                                        });
                                    }
                                    //it is open so switch between index and property or close it
                                    else {
                                        // close form
                                        if ($($('.index', $editContainer)[0]).is(':visible')) {
                                            $editContainer.slideToggle(function () {
                                                $editContainer.parent().toggleClass('property-edit-container-open');
                                                //hide indexes
                                                _hideIndexes($editContainer);
                                            });
                                        }
                                        // hide properties and show indexes
                                        else {
                                            _hideProperties($editContainer);
                                            //show properties
                                            _showIndexes($editContainer);
                                        }
                                    }
                                }
                            });
                            return 'regular-property';
                    }
                }());
                $property.addClass(!hasAlreadyProperties ? 'property-block-first property-block ' + type : 'property-block ' + type);
                $propertyContainer.append($property);
                hasAlreadyProperties = true;
            }
        });
    }


    /**
     * Make properties look nice
     *
     * @param $properties (optional)
     */
    function init($properties) {
        var $container = $('.content-block .xhtml_form:first form');
        if (!$container.length) {
            return;
        }

        _idToClipboard($container);

        // case no or empty argument -> find all properties not upgraded yet
        if (!$properties || !$properties.length) {
            $properties = $container.children('div[id*="property_"]').not('.property-block');
        }
        if (!$properties.length) {
            return;
        }
        _wrapPropsInContainer($properties);
        _upgradeButtons($container, 'radio');
        _upgradeButtons($container, 'checkbox');
        _toggleModeBtn('disabled');
        _showErrors($container);
    }

    function _showErrors($container) {
        var $editContainer;
        var $error = $container.find('.error');
        if ($error.length) {
            $editContainer = $error.closest('.property-edit-container');
            if ($editContainer.length) {
                _hideIndexes($editContainer);
                _showProperties($editContainer);
                $editContainer.slideToggle(function () {
                    $editContainer.parent().toggleClass('property-edit-container-open');
                });
            }
        }

    }

    function _hideProperties($container) {
        $('.property', $container).each(function () {
            var $currentTarget = $(this);
            while (!_.isEqual($currentTarget.parent()[0], $container[0])) {
                $currentTarget = $currentTarget.parent();
            }
            $currentTarget.hide();
        });
        _toggleModeBtn('disabled');
    }

    function _showProperties($container) {
        $('.property', $container).each(function () {
            var $currentTarget = $(this);
            while (!_.isEqual($currentTarget.parent()[0], $container[0])) {
                $currentTarget = $currentTarget.parent();
            }
            $currentTarget.show();
        });
        //show or hide the list values select
        var elt = $('[class*="property-type"]', $container).parent("div").next("div");

        var propertiesTypes = ['list', 'tree'];
        var re = new RegExp(propertiesTypes.join('$|').concat('$'));

        if (re.test($('[class*="property-type"]', $container).val())) {
            if (elt.css('display') === 'none') {
                elt.show();
                elt.find('select').removeAttr('disabled');
            }
        } else if (elt.css('display') !== 'none') {
            elt.css('display', 'none');
            elt.find('select').prop('disabled', "disabled");
        }
        _toggleModeBtn('enabled');
    }

    function _hideIndexes($container) {
        $('.index', $container).each(function () {
            var $currentTarget = $(this);
            while (!_.isEqual($currentTarget.parent()[0], $container[0])) {
                $currentTarget = $currentTarget.parent();
            }
            $currentTarget.hide();
        });
        $('.index-remover', $container).each(function () {
            $(this).parent().hide();
        });
    }

    function _showIndexes($container) {
        $('.index', $container).each(function () {
            var $currentTarget = $(this);
            while (!_.isEqual($currentTarget.parent()[0], $container[0])) {
                $currentTarget = $currentTarget.parent();
            }
            $currentTarget.show();
        });
        $('.index-remover', $container).each(function () {
            $(this).parent().show();
        });
    }


    return {
        /**
         * Initialize post renderer, this can be done multiple times
         */
        init: init,
        getPropertyContainer: getPropertyContainer
    };
});


