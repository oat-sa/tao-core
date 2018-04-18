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

define(['jquery', 'lodash', 'ui/pagination'], function ($, _, paginationComponent) {
    'use strict';

    QUnit.module('API');

    QUnit.test('factory', function (assert) {
        QUnit.expect(3);

        assert.ok(typeof paginationComponent === 'function', 'the module exposes a function');
        assert.ok(typeof paginationComponent(false, []) === 'object', 'the factory creates an object');
        assert.notEqual(paginationComponent({}), paginationComponent({}), 'the factory creates new objects');
    });

    QUnit.test('component', function (assert) {
        var pagination;

        QUnit.expect(2);

        pagination = paginationComponent({});

        assert.ok(typeof pagination.render === 'function', 'the component has a render method');
        assert.ok(typeof pagination.destroy === 'function', 'the component has a destroy method');
    });

    QUnit.test('eventifier', function (assert) {
        var pagination;

        QUnit.expect(3);

        pagination = paginationComponent({});

        assert.ok(typeof pagination.on === 'function', 'the component has a on method');
        assert.ok(typeof pagination.off === 'function', 'the component has a off method');
        assert.ok(typeof pagination.trigger === 'function', 'the component has a trigger method');
    });

    QUnit.module('Simple mode');

    QUnit.test('render', function (assert) {
        var $container;

        QUnit.expect(7);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        paginationComponent().on('render', function () {
            assert.equal($('.total', $container).length, 1, 'The total element exists');
            assert.equal($('.page', $container).length, 1, 'The page element exists');
            assert.equal($('.icon-backward', $container).length, 1, 'The backward button exists');
            assert.equal($('.icon-forward', $container).length, 1, 'The forward button exists');
            assert.equal($('.total', $container).text(), 1, 'Total element correct');
            assert.equal($('.page', $container).text(), 1, 'Page element correct');
        }).render($container);
    });

    QUnit.asyncTest('destroy', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(3);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent();
        pagination
            .on('render', function () {
                assert.equal($('.total', $container).length, 1, 'The total element exists');
                pagination.destroy();
            })
            .on('destroy', function() {
                assert.equal($('.total', $container).length, 0, 'The total element exists');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('NextPrev', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(8);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({activePage: 3, totalPages: 7});
        pagination
            .on('render', function () {
                assert.equal($('.total', $container).length, 1, 'The total element exists');
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');
                assert.equal($('.page', $container).text(), 3, 'Current page in template is correct');
                pagination.nextPage();
            })
            .on('next', function () {
                assert.equal(pagination.getActivePage(), 4, 'Current page is correct');
                assert.equal($('.page', $container).text(), 4, 'Current page in template is correct');
                pagination.previousPage();
            })
            .on('prev', function () {
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');
                assert.equal($('.page', $container).text(), 3, 'Current page in template is correct');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.test('setPage', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(7);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({activePage: 3, totalPages: 7});
        pagination
            .on('render', function () {
                assert.equal($('.total', $container).length, 1, 'The total element exists');
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');
                pagination.setPage(7);
                assert.equal(pagination.getActivePage(), 7, 'Current page is correct');
                assert.equal($('.page', $container).text(), 7, 'Current page in template is correct');
            })
            .on('change', function () {
                assert.ok('true', 'change worked');
            })
            .render($container);
    });

    QUnit.test('Critical conditions', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(14);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({activePage: 3, totalPages: 7});
        pagination
            .on('render', function () {
                assert.equal($('.total', $container).length, 1, 'The total element exists');
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');

                pagination.setPage(8);
                assert.equal(pagination.getActivePage(), 7, 'Current page still there');
                pagination.setPage(0);
                assert.equal(pagination.getActivePage(), 1, 'Current page still there');
                pagination.setPage(-1);
                assert.equal(pagination.getActivePage(), 1, 'Current page still there');
            })
            .on('error', function() {
                assert.ok(true, 'Too much catched');
            })
            .on('change', function () {
                var renderedPageNum = parseInt($('.page', $container).text(), 10);
                assert.ok(pagination.getActivePage() <= 7 && pagination.getActivePage() >= 1, 'Current page is correct');
                assert.ok(renderedPageNum <= 7 && renderedPageNum >= 1, 'Current page in template is correct');
            })
            .render($container);
    });

    QUnit.test('Check buttons', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(18);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({activePage: 4, totalPages: 4});
        pagination
            .on('render', function () {
                // buttons
                var $prev, $next;

                assert.equal($('.total', $container).length, 1, 'The total element exists');
                assert.equal(pagination.getActivePage(), 4, 'Current page is correct');

                $prev = $('.icon-backward', $container).parents('button');
                $next = $('.icon-forward', $container).parents('button');

                assert.equal($next.attr('disabled'), 'disabled', 'Next button must be disabled');
                assert.ok(_.isUndefined($prev.attr('disabled')), 'Prev button is not disabled');

                $next.click();
                assert.equal(pagination.getActivePage(), 4, 'Current page is 4');
                assert.equal($next.attr('disabled'), 'disabled', 'Next button must be disabled');
                assert.ok(_.isUndefined($prev.attr('disabled')), 'Prev button is not disabled');
                $prev.click();
                assert.equal(pagination.getActivePage(), 3, 'Current page is 3');
                assert.ok(_.isUndefined($next.attr('disabled')), 'Next button is not disabled');
                assert.ok(_.isUndefined($prev.attr('disabled')), 'Prev button is not disabled');
                $prev.click();
                assert.equal(pagination.getActivePage(), 2, 'Current page is 2');
                $prev.click();
                assert.equal(pagination.getActivePage(), 1, 'Current page is 1');
                assert.equal($prev.attr('disabled'), 'disabled', 'Prev button must be disabled');
                assert.ok(_.isUndefined($next.attr('disabled')), 'Next button is not disabled');
                $prev.click();
                assert.equal(pagination.getActivePage(), 1, 'First page');
                $prev.click();
                assert.equal(pagination.getActivePage(), 1, 'First page');
                $next.click();
                assert.equal(pagination.getActivePage(), 2, 'Current page is 2');
            })
            .render($container);
    });

    QUnit.test('Check buttons - disabled both', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(5);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({activePage: 1, totalPages: 1});
        pagination
            .on('render', function () {
                // buttons
                var $prev, $next;

                assert.equal($('.total', $container).length, 1, 'The total element exists');
                assert.equal(pagination.getActivePage(), 1, 'Current page is correct');

                $prev = $('.icon-backward', $container).parents('button');
                $next = $('.icon-forward', $container).parents('button');

                assert.equal($next.attr('disabled'), 'disabled', 'Next button must be disabled');
                assert.equal($prev.attr('disabled'), 'disabled', 'Prev button must be disabled');
            })
            .render($container);
    });

    QUnit.test('Disabled/Enabled mode', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(9);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({activePage: 4, totalPages: 7});
        pagination
            .on('render', function () {
                // buttons
                var $prev, $next;

                assert.equal($('.total', $container).length, 1, 'The total element exists');
                assert.equal(pagination.getActivePage(), 4, 'Current page is correct');

                $prev = $('.icon-backward', $container).parents('button');
                $next = $('.icon-forward', $container).parents('button');

                assert.notEqual($next.attr('disabled'), 'disabled', 'Next button not disabled');
                assert.notEqual($prev.attr('disabled'), 'disabled', 'Prev button not disabled');

                pagination.disable();

                assert.equal($next.attr('disabled'), 'disabled', 'Next button disabled');
                assert.equal($prev.attr('disabled'), 'disabled', 'Prev button disabled');

                pagination.enable();

                assert.notEqual($next.attr('disabled'), 'disabled', 'Next button not disabled');
                assert.notEqual($prev.attr('disabled'), 'disabled', 'Prev button not disabled');
            })
            .render($container);
    });

    QUnit.asyncTest('Refresh', function (assert) {
        var pagination;
        var $container;

        QUnit.expect(9);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({mode: 'simple', activePage: 1, totalPages: 2});
        pagination
            .on('render', function () {
                // buttons
                var $prev, $next;

                assert.equal(pagination.getActivePage(), 1, 'Current page is correct');

                $prev = $('.icon-backward', $container).parents('button');
                $next = $('.icon-forward', $container).parents('button');

                assert.notEqual($next.attr('disabled'), 'disabled', 'Next button must not be disabled');
                assert.equal($prev.attr('disabled'), 'disabled', 'Prev button must be disabled');

                pagination.disable();

                assert.equal($next.attr('disabled'), 'disabled', 'Next button must be disabled');
                assert.equal($prev.attr('disabled'), 'disabled', 'Prev button must be disabled');

                pagination.enable();

                assert.notEqual($next.attr('disabled'), 'disabled', 'Next button must not be disabled');
                assert.equal($prev.attr('disabled'), 'disabled', 'Prev button must be disabled');

                pagination.previousPage();
            })
            .on('prev', function () {
                assert.equal(pagination.getActivePage(), 1, 'Current page is correct');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.module('Pages mode');

    QUnit.test('render', function (assert) {
        var $container;

        QUnit.expect(5);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        paginationComponent({mode: 'pages'}).on('render', function () {
            assert.equal($('.icon-backward', $container).length, 1, 'The backward button exists');
            assert.equal($('.icon-fast-backward', $container).length, 1, 'The fast backward button exists');
            assert.equal($('.icon-forward', $container).length, 1, 'The forward button exists');
            assert.equal($('.icon-fast-forward', $container).length, 1, 'The fast forward button exists');
        }).render($container);
    });

    QUnit.asyncTest('destroy', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(3);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({mode: 'pages'});
        pagination
            .on('render', function () {
                assert.equal($('.pages', $container).length, 1, 'The element exists');
                pagination.destroy();
            })
            .on('destroy', function() {
                assert.equal($('.pages', $container).length, 0, 'The element not exists');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.asyncTest('NextPrev', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(9);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({mode: 'pages', activePage: 3, totalPages: 7});
        pagination
            .on('render', function () {
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');
                assert.equal($('.page', $container).length, 7, 'Current page in template is correct');
                assert.equal($('.page.active', $container).text(), 3, 'Current page in template is correct');
                pagination.nextPage();
            })
            .on('next', function () {
                assert.equal(pagination.getActivePage(), 4, 'Current page is correct');
                assert.equal($('.page', $container).length, 7, 'Current page in template is correct');
                assert.equal($('.page.active', $container).text(), 4, 'Current page in template is correct');
                pagination.previousPage();
            })
            .on('prev', function () {
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');
                assert.equal($('.page.active', $container).text(), 3, 'Current page in template is correct');
                QUnit.start();
            })
            .render($container);
    });

    QUnit.test('setPage', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(7);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({mode: 'pages', activePage: 3, totalPages: 8});
        pagination
            .on('render', function () {
                assert.equal(pagination.getActivePage(), 3, 'Current page is correct');
                assert.equal($('.page', $container).length, 7, 'Current page in template is correct');

                pagination.setPage(8);
                assert.equal(pagination.getActivePage(), 8, 'Current page is correct');
                assert.equal($('.page', $container).length, 6, 'Current page in template is correct');
            })
            .on('change', function () {
                assert.ok(true, 'Change was called');
            })
            .render($container);
    });

    QUnit.test('Critical conditions', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(15);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({mode: 'pages', activePage: 5, totalPages: 10});
        pagination
            .on('render', function () {
                assert.equal(pagination.getActivePage(), 5, 'Current page is correct');
                pagination.setPage(11);
                assert.equal(pagination.getActivePage(), 10, 'Current page still there');
                pagination.setPage(0);
                assert.equal(pagination.getActivePage(), 1, 'Current page still there');
                pagination.setPage(-1);
                assert.equal(pagination.getActivePage(), 1, 'Current page still there');

                pagination.setPage(5);
                assert.equal($('.page', $container).length, 7, 'Current page in template is correct');
                assert.equal($('.separator', $container).length, 2, 'Separators counted correctly');

                pagination.setPage(7);

                assert.equal($('.page', $container).length, 6, 'Current page in template is correct');
                assert.equal($('.separator', $container).length, 1, 'Separators counted correctly');

            })
            .on('error', function() {
                assert.ok(true, 'Error catched');
            })
            .on('change', function () {
                assert.ok(true, 'Change was called');
            })
            .render($container);
    });

    QUnit.test('Disabled/Enabled mode', function (assert) {
        var $container;
        var pagination;

        QUnit.expect(8);

        $container = $('#qunit-fixture');
        assert.equal($container.length, 1, 'The container exists');

        pagination = paginationComponent({mode: 'pages', activePage: 4, totalPages: 7});
        pagination
            .on('render', function () {
                // buttons
                var $prev, $next;

                assert.equal(pagination.getActivePage(), 4, 'Current page is correct');

                $prev = $('.icon-backward', $container).parents('li');
                $next = $('.icon-forward', $container).parents('li');

                assert.ok(!$prev.hasClass('disabled'), 'Next button not disabled');
                assert.ok(!$next.hasClass('disabled'), 'Prev button not disabled');

                pagination.disable();

                assert.ok($prev.hasClass('disabled'), 'Next button disabled');
                assert.ok($next.hasClass('disabled'), 'Prev button disabled');

                pagination.enable();

                assert.ok(!$prev.hasClass('disabled'), 'Next button not disabled');
                assert.ok(!$next.hasClass('disabled'), 'Prev button not disabled');
            })
            .render($container);
    });

    QUnit.module('Multi pages');

    QUnit.asyncTest('simple simple', function (assert) {
        var $container, pagination1, pagination2, $p1Container, $p2Container;

        QUnit.expect(39);

        $container = $('#qunit-fixture');
        $p1Container = $('<div class="p1" />').appendTo($container);
        $p2Container = $('<div class="p2" />').appendTo($container);
        assert.equal($container.length, 1, 'The container exists');

        pagination1 = paginationComponent({activePage: 3, totalPages: 7});
        pagination2 = paginationComponent({activePage: 17, totalPages: 22});

        pagination1
            .on('render', function () {
                assert.equal($('.total', $container).length, 2, 'p1 Count of the pagination elements is correct');
                assert.equal(pagination1.getActivePage(), 3, 'p1 Current page of p1 is correct');
                assert.equal(pagination2.getActivePage(), 17, 'p1 Current page of p2 is correct');
                assert.equal($('.page', $p1Container).text(), 3, 'p1 Current page in template of p1 is correct');
                assert.equal($('.page', $p2Container).text(), 17, 'p1 Current page in template of p2 is correct');
                pagination1.nextPage();
            })
            .on('next', function () {
                assert.equal(pagination1.getActivePage(), 4, 'p1n Current page is correct');
                assert.equal(pagination2.getActivePage(), 17, 'p1n Current page is correct');
                assert.equal($('.page', $p1Container).text(), 4, 'p1n Current page of p1 in template is correct');
                assert.equal($('.page', $p2Container).text(), 17, 'p1n Current page of p2 in template is correct');
                pagination1.previousPage();
            })
            .on('prev', function () {
                assert.equal(pagination1.getActivePage(), 3, 'p1p Current page is correct');
                assert.equal($('.page', $p1Container).text(), 3, 'p1p Current page in template of p1 is correct');
                assert.equal(pagination2.getActivePage(), 17, 'p1p Current page is correct');
                assert.equal($('.page', $p2Container).text(), 17, 'p1p Current page in template of p2 is correct');
                pagination2.nextPage();
            });

        pagination2
            .on('render', function () {
                assert.equal($('.total', $container).length, 1, 'p2 Count of the pagination elements is correct');
                assert.equal(typeof(pagination1.getActivePage()), 'undefined', 'p2 Pagination 1 have not been initialized');
                assert.equal(pagination2.getActivePage(), 17, 'p2 Current page of p2 is correct');
                assert.equal($('.page', $p1Container).text(), '', 'p2 Pagination 1 have not been initialized');
                assert.equal($('.page', $p2Container).text(), 17, 'p2 Current page in template of p2 is correct');
            })
            .on('next', function () {
                assert.equal(pagination1.getActivePage(), 3, 'p2 Current page of p1 is correct');
                assert.equal(pagination2.getActivePage(), 18, 'p2 Current page of p2 is correct');
                assert.equal($('.page', $p1Container).text(), 3, 'p2 Current page in template of p1 is correct');
                assert.equal($('.page', $p2Container).text(), 18, 'p2 Current page in template of p2 is correct');
                pagination2.previousPage();
            })
            .on('prev', function () {
                var $prev1, $next1, $prev2, $next2;

                assert.equal(pagination1.getActivePage(), 3, 'p2 Current page of p1 is correct');
                assert.equal(pagination2.getActivePage(), 17, 'p2 Current page of p2 is correct');
                assert.equal($('.page', $p1Container).text(), 3, 'p2 Current page in template of p1 is correct');
                assert.equal($('.page', $p2Container).text(), 17, 'p2 Current page in template of p2 is correct');

                // checking disable/enable
                $prev1 = $('.icon-backward', $p1Container).parents('button');
                $next1 = $('.icon-forward', $p1Container).parents('button');

                $prev2 = $('.icon-backward', $p2Container).parents('button');
                $next2 = $('.icon-forward', $p2Container).parents('button');

                assert.notEqual($next1.attr('disabled'), 'disabled', 'p1 Next button not disabled');
                assert.notEqual($prev1.attr('disabled'), 'disabled', 'p1 Prev button not disabled');

                assert.notEqual($next2.attr('disabled'), 'disabled', 'p2 Next button not disabled');
                assert.notEqual($prev2.attr('disabled'), 'disabled', 'p2 Prev button not disabled');

                pagination1.disable();

                assert.equal($next1.attr('disabled'), 'disabled', 'p1 Next button disabled');
                assert.equal($prev1.attr('disabled'), 'disabled', 'p1 Prev button disabled');
                assert.notEqual($next2.attr('disabled'), 'disabled', 'p2 Next button not disabled');
                assert.notEqual($prev2.attr('disabled'), 'disabled', 'p2 Prev button not disabled');

                pagination1.enable();

                assert.notEqual($next1.attr('disabled'), 'disabled', 'p1 Next button not disabled');
                assert.notEqual($prev1.attr('disabled'), 'disabled', 'p1 Prev button not disabled');
                assert.notEqual($next2.attr('disabled'), 'disabled', 'p2 Next button not disabled');
                assert.notEqual($prev2.attr('disabled'), 'disabled', 'p2 Prev button not disabled');

                QUnit.start();
            });


        pagination2.render($p2Container);
        pagination1.render($p1Container);
    });
});
