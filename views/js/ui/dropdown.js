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
    'ui/component',
    'tpl!ui/dropdown/tpl/dropdown',
    'tpl!ui/dropdown/tpl/list-item'
], function ($, component, dropdownTpl, itemTpl) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     */
    var defaults = {
        isOpen: false,
        activatedBy: 'hover'    // can be hover or click
    };

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
                this.config.data.headerItem = html;
                this.controls.$headerItem.html(html);
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
                this.config.data.innerItems.push(item);
                this.controls.$listContainer.append(itemTpl(item));
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
            if (index >= 0 && index < this.config.data.innerItems.length) {
                this.config.data.innerItems.splice(index, 1);
                this.controls.$listContainer.children().get(index).remove();
            }
            return this;
        },

        /**
         * Empties the dropdown list (but not its header!)
         *
         * @returns {dropdown} this
         */
        clearItems: function clearItems() {
            this.config.data.innerItems = [];
            this.controls.$listContainer.empty();
            return this;
        }
    };

    /**
     * Builds a simple dropdown component
     *
     * @param {Object} config
     * @param {Object} config.headerItem - The header item
     * @param {Array}  [config.innerItems] - The list of inner items
     * @param {String} [config.id] - The id of the dropdown element
     * @param {String} [config.cls] - An additional CSS class name
     * @param {Boolean} [config.isOpen] - Does the dropdown start open?
     * @param {String} [config.activatedBy] - hover or click
     * @returns {dropdown}
     */
    function dropdownFactory(config) {
        return component(dropdownSpecs, defaults)
        .setTemplate(dropdownTpl)
        // dropdown-specific init:
        .on('init', function() {
            this.setState('open', this.config.isOpen);
            if (config) {
                this.config.data = {
                    headerItem: config.headerItem || '',
                    innerItems: config.innerItems || []
                };
            }
        })
        // renders the component
        .on('render', function () {
            var $component = this.getElement();
            this.controls = {
                $dropdown: $component.find('.dropdown'),
                $toggler: $component.find('.toggler'),
                $headerItem: $component.find('.dropdown-header'),
                $listContainer: $component.find('.dropdown-submenu')
            };
            this.trigger('wireup');
        })
        .on('wireup', function() {
            var self = this;
            var $component = this.getElement();
            // wire up main behaviour:
            if (self.config.activatedBy === 'hover') {
                $component
                .on('mouseenter', self.open)
                .on('mouseleave', self.close);
            }
            else if (self.config.activatedBy === 'click') {
                $component.on('click', self.toggle);
            }
            $component
            .on('focus', self.open)
            .on('blur', self.close);

            this.controls.$toggler
            .on('click', self.toggle)
            .on('focus', self.open);

            // list item events
            this.controls.$listContainer.on('click', 'li', function() {
                var id = $(this).attr('id');

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
        .init(config);
    }

    return dropdownFactory;
});
