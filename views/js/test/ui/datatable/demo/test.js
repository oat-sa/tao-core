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
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */

define([
    'jquery',
    'lodash',
    'tpl!test/ui/datatable/demo/feature',
    'tpl!test/ui/datatable/demo/query',
    'tpl!test/ui/datatable/demo/file',
    'json!test/ui/datatable/demo/data.json',
    'ui/dialog',
    'ui/datatable',
    'lib/jquery.mockjax/jquery.mockjax'
], function ($, _, featureTpl, queryTpl, fileTpl, demoData, dialog) {
    "use strict";

    var $container = $('#demo');

    function sort(samples, criteria) {
        var direction = criteria.sortorder === 'desc' || criteria.sortorder < 0 ? -1 : 1;
        var field = criteria.sortby;
        return samples.sort(function (a, b) {
            a = a && a[field];
            b = b && b[field];
            if (a > b) {
                return direction;
            } else if (a < b) {
                return -direction;
            } else {
                return 0;
            }
        });
    }

    function filterToRegExp(filterPattern) {
        if (filterPattern) {
            if (filterPattern.substr(-1) !== '*') {
                filterPattern += '*';
            }

            return new RegExp('^' + filterPattern.replace(/\*/g, '.*?') + '$', 'i');
        }
        return '';
    }

    function filter(samples, model, criteria, options) {
        var filteredColumns = _.map(model, 'id');
        var filterPatterns = {};
        var filterPattern = '';

        if (criteria.filtercolumns) {
            if (_.isArray(criteria.filtercolumns)) {
                _.forEach(criteria.filtercolumns, function (field) {
                    filterPatterns[field] = filterToRegExp(criteria.filterquery);
                });
            } else {
                _.forEach(criteria.filtercolumns, function (pattern, field) {
                    if (field === 'filter') {
                        filterPattern = filterToRegExp(pattern);
                    } else {
                        filterPatterns[field] = filterToRegExp(pattern);
                    }
                });
            }
        } else {
            filterPattern = filterToRegExp(criteria.filterquery);
        }

        if (options.filterRequired && !filterPattern && !_.size(filterPatterns)) {
            return [];
        }

        return _.filter(samples, function (row) {
            return _.every(filterPatterns, function (pattern, field) {
                return pattern.test(row[field]);
            }) && (!filterPattern || _.some(filteredColumns, function (field) {
                return filterPattern.test(row[field]);
            }));
        });
    }

    function query(request, model, options) {
        var queryOptions = options || {};
        var criteria = request && request.data || {};
        var limit = _.isNumber(queryOptions.limit) ? Math.max(0, queryOptions.limit) : demoData.length;
        var filteredData = filter(sort(demoData.slice(0, limit), criteria), model, criteria, queryOptions);
        var rows = request.data.rows || 25;
        var totalPages = Math.ceil(filteredData.length / rows);
        var page = Math.max(1, Math.min(request.data.page || 1, totalPages));
        var start = rows * (page - 1);
        var pageData = filteredData.slice(start, start + rows);

        return {
            data: pageData,
            page: page,
            total: totalPages,
            amount: filteredData.length
        };
    }

    // prevent the AJAX mocks to pollute the logs
    $.mockjaxSettings.logger = null;
    $.mockjaxSettings.responseTime = 1;

    // toggle panels
    $container.on('click', '.feature .title', function (e) {
        var $el = $(e.target);
        var $feature = $el.closest('.feature');
        var action = $el.data('control');
        var $content = $feature.find('.content');
        var contentVisible = $content.is(':visible');
        var toggle = false;
        var $config;

        if (action === 'settings') {
            $config = $feature.find('.config');
            if (contentVisible) {
                $config.toggle();
            } else {
                $config.show();
                toggle = true;
            }
        } else {
            toggle = true;
        }

        if (toggle) {
            if (contentVisible) {
                $content.hide();
            } else {
                $container.find('.feature .content').hide();
                $content.show();
            }
            $feature.find('[data-control=hide]').toggle(!contentVisible);
            $feature.find('[data-control=show]').toggle(contentVisible);
        }
    });


    QUnit.moduleDone(function () {
        $container.find('.feature:last-child .title').click();
    });

    QUnit.module('Datatable Demo');

    QUnit.cases([{
        title: 'Default',
        config: {
            url: '/demo-data/default',
            rows: 10,
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Sortable',
        config: {
            url: '/demo-data/sortable',
            rows: 10,
            model: [{
                id: 'login',
                label: 'Login',
                sortable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true
            }, {
                id: 'firstname',
                label: 'First Name',
                sortable: true
            }, {
                id: 'lastname',
                label: 'Last Name',
                sortable: true
            }, {
                id: 'phone',
                label: 'Phone',
                sortable: true
            }, {
                id: 'country',
                label: 'country',
                sortable: true
            }, {
                id: 'state',
                label: 'state',
                sortable: true
            }, {
                id: 'city',
                label: 'City',
                sortable: true
            }, {
                id: 'street',
                label: 'street',
                sortable: true
            }, {
                id: 'zipcode',
                label: 'Zip Code',
                sortable: true
            }]
        }
    }, {
        title: 'Selectable',
        config: {
            url: '/demo-data/selectable',
            rows: 10,
            selectable: true,
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Row selection',
        limit: 10,
        config: {
            url: '/demo-data/row-selection',
            rows: 5,
            rowSelection: true,
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Status',
        config: {
            url: '/demo-data/status',
            rows: 10,
            status: true,
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Status empty',
        limit: 0,
        config: {
            url: '/demo-data/status-empty',
            rows: 10,
            status: {
                available: 'Users found',
                empty: 'No user found!'
            },
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Status available',
        config: {
            url: '/demo-data/status-available',
            rows: 10,
            status: {
                available: 'Users found',
                empty: 'No user found!'
            },
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Pagination',
        config: {
            url: '/demo-data/pagination',
            rows: 10,
            paginationStrategyTop: 'simple',
            paginationStrategyBottom: 'pages',
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Filter',
        config: {
            url: '/demo-data/filter',
            rows: 10,
            filter: true,
            status: {
                available: 'Users found',
                empty: 'No user found!'
            },
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }, {
                id: 'state',
                label: 'state'
            }, {
                id: 'city',
                label: 'City'
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Filter single column',
        config: {
            url: '/demo-data/filter-single-column',
            rows: 10,
            filter: true,
            status: {
                available: 'Users found',
                empty: 'No user found!'
            },
            model: [{
                id: 'login',
                label: 'Login',
                filterable: true
            }, {
                id: 'email',
                label: 'Email',
                filterable: true
            }, {
                id: 'firstname',
                label: 'First Name',
                filterable: true
            }, {
                id: 'lastname',
                label: 'Last Name',
                filterable: true
            }, {
                id: 'phone',
                label: 'Phone',
                filterable: true
            }, {
                id: 'country',
                label: 'country',
                filterable: true
            }, {
                id: 'state',
                label: 'state',
                filterable: true
            }, {
                id: 'city',
                label: 'City',
                filterable: true
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Filter multiple columns',
        config: {
            url: '/demo-data/filter-multiple-columns',
            rows: 10,
            filter: true,
            filterStrategy: 'multiple',
            status: {
                available: 'Users found',
                empty: 'No user found!'
            },
            model: [{
                id: 'login',
                label: 'Login',
                filterable: true
            }, {
                id: 'email',
                label: 'Email',
                filterable: true
            }, {
                id: 'firstname',
                label: 'First Name',
                filterable: true
            }, {
                id: 'lastname',
                label: 'Last Name',
                filterable: true
            }, {
                id: 'phone',
                label: 'Phone',
                filterable: true
            }, {
                id: 'country',
                label: 'country',
                filterable: true
            }, {
                id: 'state',
                label: 'state',
                filterable: true
            }, {
                id: 'city',
                label: 'City',
                filterable: true
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Filter required',
        filterRequired: true,
        config: {
            url: '/demo-data/filter-required',
            rows: 10,
            filter: true,
            filterStrategy: 'multiple',
            status: {
                available: 'Users found',
                empty: 'No user found! Please precise your search'
            },
            model: [{
                id: 'login',
                label: 'Login',
                filterable: true
            }, {
                id: 'email',
                label: 'Email',
                filterable: true
            }, {
                id: 'firstname',
                label: 'First Name',
                filterable: true
            }, {
                id: 'lastname',
                label: 'Last Name',
                filterable: true
            }, {
                id: 'phone',
                label: 'Phone',
                filterable: true
            }, {
                id: 'country',
                label: 'country',
                filterable: true
            }, {
                id: 'state',
                label: 'state',
                filterable: true
            }, {
                id: 'city',
                label: 'City',
                filterable: true
            }, {
                id: 'street',
                label: 'street'
            }, {
                id: 'zipcode',
                label: 'Zip Code'
            }]
        }
    }, {
        title: 'Actions',
        config: {
            url: '/demo-data/actions',
            rows: 10,
            actions: [{
                id: 'file',
                label: 'File',
                icon: 'item',
                action: function (id, row) {
                    dialog({
                        message: "User's file #" + id,
                        content: fileTpl(row),
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }, {
                id: 'remove',
                label: 'Remove',
                icon: 'bin',
                action: function (id) {
                    dialog({
                        message: 'Deletion is not supported yet!',
                        content: 'That would affect user id #' + id,
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }],
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }]
        }
    }, {
        title: 'Tools',
        config: {
            url: '/demo-data/tools',
            rows: 10,
            selectable: true,
            tools: [{
                id: 'tool',
                label: 'Tool',
                icon: 'settings',
                action: function () {
                    dialog({
                        message: 'It is a wonderful tool!',
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }, {
                id: 'massAction',
                label: 'Mass Action',
                icon: 'play',
                massAction: true,
                action: function (selection) {
                    dialog({
                        message: 'This action will affect users [' + selection.join(', ') + ']',
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }],
            actions: [{
                id: 'file',
                label: 'File',
                icon: 'item',
                action: function (id, row) {
                    dialog({
                        message: "User's file #" + id,
                        content: fileTpl(row),
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }, {
                id: 'remove',
                label: 'Remove',
                icon: 'bin',
                action: function (id) {
                    dialog({
                        message: 'Deletion is not supported yet!',
                        content: 'That would affect user id #' + id,
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }],
            model: [{
                id: 'login',
                label: 'Login'
            }, {
                id: 'email',
                label: 'Email'
            }, {
                id: 'firstname',
                label: 'First Name'
            }, {
                id: 'lastname',
                label: 'Last Name'
            }, {
                id: 'phone',
                label: 'Phone'
            }, {
                id: 'country',
                label: 'country'
            }]
        }
    }, {
        title: 'All',
        config: {
            url: '/demo-data/all',
            rows: 10,
            filter: true,
            filterStrategy: 'multiple',
            rowSelection: true,
            selectable: true,
            paginationStrategyTop: 'simple',
            paginationStrategyBottom: 'pages',
            status: {
                available: 'Users found',
                empty: 'No user found!'
            },
            tools: [{
                id: 'tool',
                label: 'Tool',
                icon: 'settings',
                action: function () {
                    dialog({
                        message: 'It is a wonderful tool!',
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }, {
                id: 'massAction',
                label: 'Mass Action',
                icon: 'play',
                massAction: true,
                action: function (selection) {
                    dialog({
                        message: 'This action will affect users [' + selection.join(', ') + ']',
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }],
            actions: [{
                id: 'file',
                label: 'File',
                icon: 'item',
                action: function (id, row) {
                    dialog({
                        message: "User's file #" + id,
                        content: fileTpl(row),
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }, {
                id: 'remove',
                label: 'Remove',
                icon: 'bin',
                action: function (id) {
                    dialog({
                        message: 'Deletion is not supported yet!',
                        content: 'That would affect user id #' + id,
                        buttons: 'ok',
                        autoRender: true,
                        autoDestroy: true
                    });
                }
            }],
            model: [{
                id: 'login',
                label: 'Login',
                sortable: true,
                filterable: true
            }, {
                id: 'email',
                label: 'Email',
                sortable: true,
                filterable: true
            }, {
                id: 'firstname',
                label: 'First Name',
                sortable: true,
                filterable: true
            }, {
                id: 'lastname',
                label: 'Last Name',
                sortable: true,
                filterable: true
            }, {
                id: 'phone',
                label: 'Phone',
                sortable: true,
                filterable: true
            }, {
                id: 'country',
                label: 'country',
                sortable: true,
                filterable: true
            }]
        }
    }]).asyncTest('configured with ', function (testCase, assert) {
        var $feature = $(featureTpl({
            title: testCase.title,
            config: JSON.stringify(testCase.config, null, 2)
        })).appendTo($container);

        QUnit.expect(1);

        $.mockjax({
            url: testCase.config.url,
            dataType: 'json',
            response: function (request) {
                this.responseText = query(request, testCase.config.model, {
                    limit: testCase.limit,
                    filterRequired: testCase.filterRequired
                });
            }
        });

        $feature.find('.widget')
            .on('create.datatable', function () {
                assert.ok(true, 'Datatable is created');
                QUnit.start();
            })
            .on('query.datatable', function (e, ajaxConfig) {
                var queryData = {
                    method: ajaxConfig.type,
                    url: ajaxConfig.url
                };
                if (ajaxConfig.type === 'GET') {
                    queryData.url = queryData.url + (queryData.url.indexOf('?') > -1 ? '&' : '?') + $.param(ajaxConfig.data);
                } else {
                    queryData.params = JSON.stringify(ajaxConfig.data);
                }
                $feature.find('.query').html(queryTpl(queryData));
            })
            .datatable(testCase.config);
    });
});
