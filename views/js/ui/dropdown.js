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
    'lodash',
    'jquery',
    'ui/component',
    'tpl!ui/dropdown/tpl/dropdown'
], function (_, $, component, dropdownTpl) {
    'use strict';

    /**
     * Some default config
     * @type {Object}
     */
    var defaults = {
        isOpen: false,
        headerItem: {text: 'Menu'},    // li component or just html
        innerItems: [], // array of li components or html items
        activatedBy: 'hover'    // can be hover or click
        //vDirection: 'down', // not implemented
        //hAlign: 'right',    // not implemented
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
         */
        open: function open() {
            if (!this.config.isOpen) {
                $(this.getElement()).find('.dropdown').addClass('open');
                this.config.isOpen = true;
            }
        },

        /**
         * Closes the dropdown
         */
        close: function close() {
            if (this.config.isOpen) {
                $(this.getElement()).find('.dropdown').removeClass('open');
                this.config.isOpen = false;
            }
        },

        /**
         * Toggles the dropdown open/closed
         */
        toggle: function toggle() {
            if (this.config.isOpen) {
                this.close();
            }
            else {
                this.open();
            }
        },

        /**
         * Sets the header item above the dropdown list
         *
         * @param {Object} data
         * @param {String} data.text - the text content of the header
         * @param {string} [data.icon] - name of an icon to be displayed
         * @param {String} [data.link] - a link target
         * @param {String} [data.cls] - a class name applied to the header
         */
        setHeader: function(data) {
            if (data && data.text && data.text.length > 0) {
                this.config.headerItem = data;
                //this.render();
            }
        },

        /**
         * Sets the header item above the dropdown list (if it comes as HTML)
         *
         * @param {String} html - the html of the header
         */
        setHtmlHeader: function(html) {
            if (typeof html === 'string') {
                this.config.headerItem = {
                    html: html
                };
                //this.render();
            }
        },

        /**
         * Adds a list item to the dropdown list
         *
         * @param {Object} data
         * @param {String} data.text - the text content of the item
         * @param {string} [data.icon] - name of an icon to be displayed
         * @param {String} [data.link] - a link target
         * @param {String} [data.cls] - a class name applied to the list item
         */
        addItem: function(data) {
            if (data && data.text && data.text.length > 0) {
                this.config.innerItems.push(data);
                //this.render();
            }
        },

        /**
         * Inserts a list item to the dropdown list
         *
         * @param {Object} data - as above
         * @param {Number} index - list index to insert before
         */
        insertItem: function(data, index) {
            if (data && data.text && data.text.length > 0) {
                if (index >= 0 && index < this.config.innerItems.length) {
                    this.config.innerItems.splice(index, 1, data);
                    //this.render();
                }
            }
        },

        /**
         * Adds a HTML list item to the end of the dropdown list
         *
         * @param {String} html - HTML content to insert inside new list item
         */
        addHtmlItem: function(html) {
            if (typeof html === 'string') {
                this.config.innerItems.push({
                    html: html
                });
                //this.render();
            }
        },

        /**
         * Removes a list item from the dropdown list
         *
         * @param {Number} index - list index to remove
         */
        removeItem: function(index) {
            if (index >= 0 && index < this.config.innerItems.length) {
                this.config.innerItems.splice(index, 1);
                //this.render();
            }
        },

        /**
         * Empties the dropdown list (but not its header!)
         */
        clearItems: function() {
            this.config.innerItems = [];
            //this.render();
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
     * @param {String} [config.activatedBy] - hover or click
     * @param {String} [config.vDirection] - not implemented
     * @param {String} [config.hAlign] - not implemented
     * @returns {dropdown}
     */
    function dropdownFactory(config) {
        return component(dropdownSpecs, defaults)
        .setTemplate(dropdownTpl)
        // dropdown-specific init:
        .on('init', function() {
            // where is focus?
            // if (!window.debugLoop) {
            //     window.debugLoop = setInterval(function() {
            //         console.info('activeElement:', document.activeElement);
            //     }, 3000);
            // }
        })
        // renders the component
        .on('render', function () {
            var self = this;
            var $component = this.getElement();
            var $toggler = $component.find('.toggler');

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

            $toggler
            .on('click', self.toggle)
            .on('focus', self.open);
        })
        .init(config);
    }

    return dropdownFactory;
});
