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
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

define([
    'jquery',
    'lodash',
    'i18n',
    'tpl!ui/pagination/providers/tpl/simple'
], function ($, _, __, tpl) {
    'use strict';

    var pagination = {
        init: function init() {
            var $paginationTpl;

            return {
                render: function render($container) {
                    $paginationTpl = $(tpl());
                    $container.append($paginationTpl);
                },
                forwardButton: function forwardButton() {
                    return $('.icon-forward', $paginationTpl).parents('button');
                },
                backwardButton: function backwardButton() {
                    return $('.icon-backward', $paginationTpl).parents('button');
                },
                setPages: function setPages(page, total) {
                    $('.page', $paginationTpl).text(page);
                    $('.total', $paginationTpl).text(total);
                },
                disableButton: function disableButton($btn) {
                    $btn.attr('disabled', 'disabled');
                },
                enableButton: function enableButton($btn) {
                    if ($btn.attr('disabled')){
                        $btn.removeAttr('disabled');
                    }
                },
                pageButtons: function pageButton() {
                    return false;
                },
                firstPageButton: function lastPageButton() {
                    return false;
                },
                lastPageButton: function lastPageButton() {
                    return false;
                },
                destroy: function destroy() {
                    $paginationTpl.remove();
                },
                disable: function disable() {
                    this.disableButton(this.backwardButton());
                    this.disableButton(this.forwardButton());
                },
                enable: function enable() {
                    // for that provider everything will be done in the pagination.js
                }
            };
        }
    };

    return pagination;
});
