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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define(['jquery', 'jquery.sizechange'], function ($) {
    'use strict';

    /**
     * Helps you to resize an iframe from it's content
     * 
     * todo migrate to a jQuery plugin ?
     * 
     * @author Bertrand Chevrier <betrand@taotesting.com>
     * @exports iframeResizer
     */
    var Resizer = {

        /**
         * Set the height of an iframe regarding it's content, on load and if the style changes.
         * 
         * @param {jQueryElement} $frame - the iframe to resize
         * @param {string} [restrict = 'body'] - restrict the elements that can have a style change
         * @returns {jQueryElement} $frame for chaining 
         */
        autoHeight : function ($frame, restrict) {
            var self = this;
            restrict = restrict || 'body';
            $frame.load(function () {
                var $frameContent = $frame.contents();
                var height = $frameContent.height();

                //call resizePop to change only to the last value within a time frame of 10ms
                var sizing = false;
                var resizePop = function resizePop () {
                    if (sizing === false) {
                        sizing = true;
                        setTimeout(function () {
                            self._adaptHeight($frame, height);
                            sizing = false;
                        }, 10);
                    }
                };

                //resize on load
                self._adaptHeight($frame, height);

                try {

                    //then listen for size change
                    $frameContent.find(restrict).sizeChange(function () {
                        var newHeight = $frameContent.height();
                        if (height !== newHeight) {
                            height = newHeight;
                            resizePop();
                        }
                    });
                    
                } catch (e) {
                    
                    //fallback to an interval mgt
                    setInterval(function () {
                        var newHeight = $frameContent.height();
                        if (height !== newHeight) {
                            height = newHeight;
                            resizePop();
                        }
                    }, 50);
                }
            });

            return $frame;
        },

        /**
         * Listen for heightchange event to adapt the height
         * @param {jQueryElement} $frame - the frame to listen for height changes
         */
        eventHeight : function ($frame) {
            var self = this;
            var diff = 20;

            $frame.load(function () {
                diff = parseInt($frame.contents().height(), 10) - parseInt($frame.height(), 10) + 20;
                self._adaptHeight($frame, $frame.contents().height());
            });

            $(document).on('heightchange', function (e, height) {
                self._adaptHeight($frame, height + diff);
            });
        },

        /**
         * Notify the parent document of an height change in case we are in an iframe
         * @private
         * @param {Number} height - the value of the new height
         * @fires heightchange
         */
        _notifyParent : function (height) {
            if (window.parent && window.parent !== window && window.parent.$) {
                
                /**
                 * @event heightchange
                 * @param {Number} height - the value of the new height
                 */
                window.parent.$(window.parent.document).trigger('heightchange', [height]);
            }
        },

        /**
         * Change the height of the targeted iframe
         * @private
         * @param {jQueryElement} $frame  - the frame to resize
         * @param {number} height  - the value of the new height
         * @fires heightchange
         */
        _adaptHeight : function ($frame, height) {
            $frame.height(height);
            this._notifyParent(height);
        }

    };
    return Resizer;
});


