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

define(['jquery', 'lodash', 'i18n', 'ui/component', 'ui/pagination/paginationStrategy'], function ($, _, __, component, paginationStrategy) {
    'use strict';

    /**
     * Default values
     *
     * @type {{mode: string, activePage: number, totalPages: number}}
     * @private
     */
    var _defaults = {
        mode: 'simple',
        activePage: 1,
        totalPages: 1,
        delay: 300
    };

    /**
     * Checking that variable has valid totalPages value
     *
     * @param totalPages
     * @returns {*|number}
     */
    function validTotalPages(totalPages) {
        totalPages = totalPages || 1;
        if (totalPages < 1) {
            return false;
        }
        return totalPages;
    }

    /**
     * Calculate active page value
     *
     * @param page
     * @param pages
     * @returns {Number|*}
     */
    function calculateActivePage(page, pages) {
        page = parseInt(page);

        if (page < 1) {
            page = 1;
        }

        if (page > pages) {
            page = pages;
        }

        return page;
    }

    /**
     * Creates pagination
     *
     * @param {Object} config
     * @param {String} [config.mode] - 'pages' | 'simple' -- 'simple' by default (next/prev), 'pages' show pages and extended control for pagination
     * @param {String} [config.activePage] - The initial active page (default: 1)
     * @param {Integer} [config.totalPages] - Count of the pages
     * @param {Integer} [config.delay] - Waiting time for debouncing pagination buttons
     * @fires "render" after the pagination component rendering
     * @fires "destroy" after the pagination component destroying
     *
     * @returns {component|*}
     */
    return function paginationFactory(config) {

        var paginationComponent;
        var pagination;
        var provider;
        var totalPages, activePage;

        config = _.defaults(config || {}, _defaults);

        pagination = {
            setPage: function setPage(page) {
                page = calculateActivePage(page, this.getTotal());
                if (page === false) {
                    this.trigger('error', __('Undefined amount of the pages for pagination'));
                } else {
                    activePage = page;
                    provider.setPages(this.getActivePage(), this.getTotal());
                    this.trigger('change');
                }
            },
            nextPage: function nextPage() {
                this.setPage(this.getActivePage()+1);
                this.trigger('next');
            },
            previousPage: function previousPage() {
                this.setPage(this.getActivePage()-1);
                this.trigger('prev');
            },
            getActivePage: function getActivePage() {
                return activePage;
            },
            getTotal: function getTotal() {
                return totalPages;
            },
            actualizeButtons: function initButtons() {
                if (this.getActivePage() === this.getTotal()) {
                    provider.disableButton(provider.forwardButton());
                    if (provider.lastPageButton() !== false) {
                        provider.disableButton(provider.lastPageButton());
                    }
                } else {
                    provider.enableButton(provider.forwardButton());
                    if (provider.lastPageButton() !== false) {
                        provider.enableButton(provider.lastPageButton());
                    }
                }

                if (this.getActivePage() === 1) {
                    provider.disableButton(provider.backwardButton());
                    if (provider.firstPageButton() !== false) {
                        provider.disableButton(provider.firstPageButton());
                    }
                } else {
                    provider.enableButton(provider.backwardButton());
                    if (provider.firstPageButton() !== false) {
                        provider.enableButton(provider.firstPageButton());
                    }
                }
            }
        };

        return component(pagination)
            .on('render', function () {
                var self = this;

                if (_.isUndefined(config.totalPages)) {
                    this.trigger('error', __('Undefined amount of the totalPages for pagination'));
                }

                totalPages = validTotalPages(config.totalPages);
                if (totalPages === false) {
                    this.trigger('error', __('Undefined amount of the pages for pagination'));
                }

                activePage = calculateActivePage(config.activePage || 1, totalPages);

                provider = paginationStrategy(config.mode).init();

                provider.render(this.getContainer());

                provider.setPages(this.getActivePage(), this.getTotal());
                pagination.actualizeButtons();

                provider.forwardButton().off('click').on('click', _.debounce(function() {
                    if (self.getActivePage() >= self.getTotal()) {
                        return;
                    }

                    self.nextPage();
                }, config.delay));

                provider.backwardButton().off('click').on('click', _.debounce(function() {
                    if (self.getActivePage() === 1) {
                        return;
                    }

                    self.previousPage();
                }, config.delay));

                if (provider.pageButtons() !== false) {
                    provider.pageButtons().off('click').on('click', _.debounce(function () {
                        var page = parseInt($(this).text());
                        if (page) {
                            self.setPage(page);
                        }
                    }, config.delay));
                }

                if (provider.firstPageButton() !== false) {
                    provider.firstPageButton().off('click').on('click', _.debounce(function () {
                        self.setPage(1);
                    }, config.delay));
                }

                if (provider.lastPageButton() !== false) {
                    provider.lastPageButton().off('click').on('click', _.debounce(function () {
                        self.setPage(self.getTotal());
                    }, config.delay));
                }
            })
            .on('disable', function() {
                // all buttons will be disabled
                provider.disable();
            })
            .on('enable', function() {
                // all buttons will be enabled
                provider.enable();
            })
            .on('destroy', function () {
                provider.destroy();
            })
            .init(config);
    };
});
