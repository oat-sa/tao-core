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
/**
 * @author Martin Nicholson <martin@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/component',
    'tpl!ui/dropdown/tpl/dropdown',
    'tpl!ui/dropdown/tpl/list-item'
], function ($, _, component, dropdownTpl, itemTpl) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     */
    var defaults = {
        isOpen: false,
        activatedBy: 'hover'    // can be hover or click
    };

    /**
     * Builds a simple dropdown component
     *
     * @param {Object} config
     * @param {String} [config.id] - The id of the dropdown element
     * @param {String} [config.cls] - An additional CSS class name
     * @param {Boolean} [config.isOpen] - Does the dropdown start open?
     * @param {String} [config.activatedBy] - hover or click
     * @param {Object} [data] - the data to initialise the component with
     * @param {String} [data.header]
     * @param {Object} [data.items]
     * @returns {dropdown}
     */
    function dropdownFactory(config, data) {
        var dropdownSpecs = {
            /**
             * Gets the identifier of the dropdown
             * @returns {String}
             */
            getId: function getId() {
                return this.config.id;
            },

            /**
             * Opens the dropdown
             *
             * @returns {dropdown} this
             */
            open: function open() {
                if (!this.is('open')) {
                    this.controls.$dropdown.addClass('open');
                    this.setState('open', true);
                }
                return this;
            },

            /**
             * Closes the dropdown
             *
             * @returns {dropdown} this
             */
            close: function close() {
                if (this.is('open')) {
                    this.controls.$dropdown.removeClass('open');
                    this.setState('open', false);
                }
                return this;
            },

            /**
             * Toggles the dropdown open/closed
             *
             * @returns {dropdown} this
             */
            toggle: function toggle() {
                if (this.is('open')) {
                    this.close();
                }
                else {
                    this.open();
                }
                return this;
            },

            /**
             * Sets the header item above the dropdown list
             *
             * @param {String} html
             * @returns {dropdown} this
             */
            setHeader: function setHeader(html) {
                if (typeof html === 'string') {
                    data.header = html;
                    if (this.is('rendered')) {
                        this.controls.$headerItem.html(html);
                    }
                }
                return this;
            },

            /**
             * Sets all the list items in one go
             * Replaces any existing items
             *
             * @param {Array} items
             * @returns {dropdown} this
             */
            setItems: function setItems(items) {
                var self = this;

                if (Array.isArray(items)) {
                    data.items = items;

                    if (this.is('rendered')) {
                        this.controls.$listContainer.empty();

                        _.forEach(items, function(item) {
                            self.controls.$listContainer.append(itemTpl(item));
                        });
                    }
                }
                return this;
            },

            /**
             * Adds a list item to the dropdown list
             *
             * @param {Object} item
             * @param {String} item.content - the content to insert (should be HTML)
             * @param {String} [item.id] - the id the list item will have
             * @param {String} [item.cls] - any extra classes to put on the list item
             * @param {String} [item.icon] - the name of an icon to precede the content, if desired
             * @returns {dropdown} this
             */
            addItem: function addItem(item) {
                if (item.content && typeof item.content === 'string' && item.content.length) {
                    data.items.push(item);

                    if (this.is('rendered')) {
                        this.controls.$listContainer.append(itemTpl(item));
                    }
                }
                return this;
            },

            /**
             * Removes a list item from the dropdown list
             *
             * @param {Number} index - list index to remove
             * @returns {dropdown} this
             */
            removeItem: function removeItem(index) {
                if (index >= 0 && index < data.items.length) {
                    data.items.splice(index, 1);
                    if (this.is('rendered')) {
                        this.controls.$listContainer.children().get(index).remove();
                    }
                }
                return this;
            },

            /**
             * Empties the dropdown list (but not its header!)
             *
             * @returns {dropdown} this
             */
            clearItems: function clearItems() {
                data.items = [];
                if (this.is('rendered')) {
                    this.controls.$listContainer.empty();
                }
                return this;
            }
        };
        data = _.defaults({}, data, {
            header: '',
            items: []
        });

        return component(dropdownSpecs, defaults)
        .setTemplate(dropdownTpl)
        // dropdown-specific init:
        .on('init', function() {
            this.setState('open', this.config.isOpen);
        })
        // renders the component
        .on('render', function () {
            var $component = this.getElement();
            this.controls = {
                $dropdown: $component.find('.dropdown'),
                $toggler: $component.find('.dropdown-header:after'),
                $headerItem: $component.find('.dropdown-header'),
                $listContainer: $component.find('.dropdown-submenu')
            };
            // insert data into rendered template:
            if (!_.isEmpty(data)) {
                this.setHeader(data.header);
                this.setItems(data.items);
            }
            this.trigger('wireup');
        })
        .on('wireup', function() {
            var self = this;
            var $component = this.getElement();
            // wire up main behaviour:
            if (this.config.activatedBy === 'hover') {
                $component
                .on('mouseenter', self.open)
                .on('mouseleave', self.close);

                this.controls.$toggler
                .on('click', self.toggle)
                .on('focus', self.open);
            }
            else if (this.config.activatedBy === 'click') {
                this.controls.$headerItem.on('click', self.toggle);
            }
            $component
            .on('focus', self.open)
            .on('blur', self.close);

            // list item events
            this.controls.$listContainer.on('click', 'li', function() {
                var id = $(this).closest('li').attr('id');
                /**
                 * @event item-click
                 */
                self.trigger('item-click', id);
                /**
                 * @event item-click-<id>
                 */
                self.trigger('item-click-' + id);
            });
        })
        .on('item-click', function() {
            this.close();
        })
        .init(config);
    }
    return dropdownFactory;
});
