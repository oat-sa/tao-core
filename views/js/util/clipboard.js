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
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

define([
    'jquery',
    'core/eventifier'
], function ($, eventifier){
    'use strict';

    /**
     * System clipboard manager
     *
     * System clipboard can't be changed without real users action (safety restriction)
     *
     * @typedef {Object} clipboard
     */
    return eventifier({
        /**
         * Cleans system clipboard
         * rewrites everything with space symbol (because some browsers don't replace content with empty string)
         */
        clean: function clean () {
            this.copy(' ');
        },
        /**
         * Place text to the system clipboard
         * @param text
         */
        copy: function copy (text) {
            // create new el to copy from
            var textAreaToSelContent;
            textAreaToSelContent = document.createElement('textarea');  // Create a <textarea> element
            textAreaToSelContent.setAttribute("id", "clipboardCleanerPlugin");
            textAreaToSelContent.value = text;                                 // Set its value to the string that you want copied
            textAreaToSelContent.setAttribute('readonly', '');                // Make it readonly to be tamper-proof
            textAreaToSelContent.style.position = 'absolute';
            textAreaToSelContent.style.left = '-9999px';                      // Move outside the screen to make it invisible
            document.body.appendChild(textAreaToSelContent);
            this.copyFromEl(textAreaToSelContent);
            document.body.removeChild(textAreaToSelContent);                  // Remove the <textarea> element
        },
        /**
         * Copy text from the element (js or jquery element)
         * @param elem
         * @fires clipboard#copied - content successfully stored in clipboard
         * @fires clipboard#copyError - content was not stored, returns reason
         */
        copyFromEl: function copyFromEl(elem) {
            var textRange, editable, readOnly, range, sel, successful, el;

            el = elem instanceof $ ? elem.get(0) : elem;

            // Copy textarea, pre, div, etc.
            if (document.body.createTextRange) {
                // IE
                textRange = document.body.createTextRange();
                textRange.moveToElementText(el);
                textRange.select();
                textRange.execCommand('Copy');
                this.trigger('copied', {srcEl: el});
            }
            else if (window.getSelection && document.createRange) {
                // non-IE
                if (el.hasOwnProperty('contentEditable')) {
                    editable = el.contentEditable; // Record contentEditable status of element
                    el.contentEditable = true; // iOS will only select text on non-form elements if contentEditable = true;
                }
                if (el.hasOwnProperty('readOnly')) {
                    readOnly = el.readOnly; // Record readOnly status of element
                    el.readOnly = false; // iOS will not select in a read only form element
                }
                range = document.createRange();
                range.selectNodeContents(el);
                sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range); // Does not work for Firefox if a textarea or input
                if (el.nodeName === 'TEXTAREA' || el.nodeName === 'INPUT') {
                    el.select(); // Firefox will only select a form element with select()
                }
                if (el.setSelectionRange && navigator.userAgent.match(/ipad|ipod|iphone/i)) {
                    el.setSelectionRange(0, 999999); // iOS only selects "form" elements with SelectionRange
                }
                if (el.hasOwnProperty('contentEditable')) {
                    el.contentEditable = editable; // Restore previous contentEditable status
                }
                if (el.hasOwnProperty('readOnly')) {
                    el.readOnly = readOnly; // Restore previous readOnly status
                }
                if (document.queryCommandSupported('copy')) {
                    successful = document.execCommand('copy');
                    if (successful) {
                        this.trigger('copied', { srcEl: elem });
                    } else {
                        this.trigger('copyError', {srcEl: elem, reason: 'Not Success'});
                    }
                } else {
                    if (!navigator.userAgent.match(/ipad|ipod|iphone|android|silk/i)) {
                        this.trigger('copyError', {srcEl: elem, reason: 'Copy command not supported'});
                    }
                }
            }
        },
        /**
         * Paste from clipboard
         * doesn't work for many browsers
         * can be useful article to use it (if required): https://developers.google.com/web/updates/2018/03/clipboardapi
         * @param elem
         * @fires clipboard#pasted - content from clipboard pasted
         * @fires clipboard#pasteError - content wasn't pasted
         */
        paste: function paste(elem) {
            var el, editable, readOnly, range, sel, successful;

            el = elem instanceof $ ? elem.get(0) : elem;

            if (window.clipboardData) {
                // IE
                el.value = window.clipboardData.getData('Text');
                el.innerHTML = window.clipboardData.getData('Text');
            }
            else if (window.getSelection && document.createRange) {
                // non-IE
                if (el.tagName.match(/textarea|input/i) && el.value.length < 1) {
                    el.value = ' '; // iOS needs element not to be empty to select it and pop up 'paste' button
                } else if (el.innerHTML.length < 1) {
                    el.innerHTML = '&nbsp;'; // iOS needs element not to be empty to select it and pop up 'paste' button
                }
                editable = el.contentEditable; // Record contentEditable status of element
                readOnly = el.readOnly; // Record readOnly status of element
                el.contentEditable = true; // iOS will only select text on non-form elements if contentEditable = true;
                el.readOnly = false; // iOS will not select in a read only form element
                range = document.createRange();
                range.selectNodeContents(el);
                sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                if (el.nodeName === 'TEXTAREA' || el.nodeName === 'INPUT') {
                    el.select(); // Firefox will only select a form element with select()
                }
                if (el.setSelectionRange && navigator.userAgent.match(/ipad|ipod|iphone/i)) {
                    el.setSelectionRange(0, 999999); // iOS only selects "form" elements with SelectionRange
                }
                if (document.queryCommandSupported('paste')) {
                    successful = document.execCommand('Paste');
                    if (successful) {
                        this.trigger('pasted', { srcEl: elem });
                    } else {
                        if (navigator.userAgent.match(/android/i) && navigator.userAgent.match(/chrome/i))
                        {
                            this.trigger('pasteError', {
                                srcEl: elem,
                                reason: 'Extra action required' // wrong element selected?
                            });

                            if (el.tagName.match(/textarea|input/i))
                            {
                                el.value = ' ';
                                el.focus();
                                el.setSelectionRange(0, 0);
                            } else {
                                el.innerHTML = "";
                            }
                        } else {
                            this.trigger('pasteError', {
                                srcEl: elem,
                                reason: 'Press CTRL-V to paste'
                            });
                        }
                    }
                }
                else
                {
                    this.trigger('pasteError', {
                        srcEl: elem,
                        reason: 'Command paste not supported'
                    });
                }
                el.contentEditable = editable; // Restore previous contentEditable status
                el.readOnly = readOnly; // Restore previous readOnly status
            }
        }
    });
});
