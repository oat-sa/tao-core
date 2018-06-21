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
    'tpl!ui/pagination/providers/tpl/pages',
    'tpl!ui/pagination/providers/tpl/pages/page'
], function ($, _, __, tpl, pageTpl) {
    'use strict';

    return {
        init : function () {
            var $paginationTpl;

            var generatePage = function generatePage(page) {
                return $(pageTpl({page: page}));
            };

            var separator = function separator() {
                var $page = generatePage('...');
                $page.addClass('separator');
                return $page;
            };

            var generatePart = function generatePart(from, to, activePage) {
                var i, pages = [], $page;

                for (i = from; i <= to; i++) {
                    $page = generatePage(i);
                    if (i === activePage) {
                        $page.addClass('active');
                    }
                    pages.push($page);
                }

                return pages;
            };

            var generatePages = function generatePages(page, total) {
                var pages = [];

                if (total <= 7) {
                    pages = pages.concat(generatePart(1, total, page));
                } else {
                    if (page < 5) {
                        pages = pages.concat(generatePart(1, 5, page));
                        pages = pages.concat(separator());
                        pages = pages.concat(generatePart(total, total, page));
                    } else {
                        if ( page >= total-3 ) {
                            pages = pages.concat(generatePart(1, 1, page));
                            pages = pages.concat(separator());
                            pages = pages.concat(generatePart(total-3, total, page));
                        } else {
                            pages = pages.concat(generatePart(1, 1, page));
                            pages = pages.concat(separator());
                            pages = pages.concat(generatePart(page-1, page+1, page));
                            pages = pages.concat(separator());
                            pages = pages.concat(generatePart(total, total, page));
                        }
                    }
                }

                return pages;
            };

            var dropPages = function dropPages() {
                $('.page', $paginationTpl).remove();
            };

            var getForwardBtn = function () {
                return $('.next', $paginationTpl);
            };

            var bindPages = function bindPages(list) {
                var $point = getForwardBtn();
                _.each(list, function($page) {
                    $page.insertBefore($point);
                });
            };

            var pagination = {
                render: function render($container) {
                    $paginationTpl = $(tpl());
                    $container.append($paginationTpl);
                },
                forwardButton: function forwardButton() {
                    return getForwardBtn();
                },
                backwardButton: function backwardButton() {
                    return $('.previous', $paginationTpl);
                },
                pageButtons: function pageButton() {
                    return $('.page', $paginationTpl);
                },
                firstPageButton: function lastPageButton() {
                    return $('.first-page', $paginationTpl);
                },
                lastPageButton: function lastPageButton() {
                    return $('.last-page', $paginationTpl);
                },
                setPages: function setPages(page, total) {
                    var pages = generatePages(page, total);

                    dropPages();
                    bindPages(pages);
                },
                disableButton: function disableButton($btn) {
                    if (!$btn.hasClass('disabled')) {
                        $btn.addClass('disabled');
                    }
                },
                enableButton: function enableButton($btn) {
                    if ($btn.hasClass('disabled')) {
                        $btn.removeClass('disabled');
                    }
                },
                destroy: function destroy() {
                    $paginationTpl.remove();
                },
                disable: function disable() {
                    var self = this;
                    this.disableButton(this.backwardButton());
                    this.disableButton(this.firstPageButton());

                    $('.page', $paginationTpl).each(function(){
                        self.disableButton( $(this) );
                    });

                    this.disableButton(this.lastPageButton());
                    this.disableButton(this.forwardButton());
                },
                enable: function enable() {
                    var self = this;
                    // restore buttons
                    $('.page', $paginationTpl).each(function(){
                        self.enableButton( $(this) );
                    });
                }
            };

            return pagination;
        }
    };
});
