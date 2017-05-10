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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */

/**
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
define([
    'jquery',
    'lodash',
    'layout/loading-bar'
], function($, _, loadingBar) {
    'use strict';

    QUnit.module('layout/loading-bar');

    QUnit.test('module', function(assert) {
        assert.ok(typeof loadingBar === 'object', 'the module expose an object');
    });

    QUnit.asyncTest('Show loading bar with overlay', function(assert) {
        loadingBar.start();
        QUnit.expect(2);

        assert.ok($('.loading-bar').is(':visible'), 'Loading bar has been shown');
        assert.ok($('.loading-bar.loading').hasClass('loadingbar-covered'), 'Loading bar has overlay');

        QUnit.start();
    });

    QUnit.asyncTest('Show loading bar without overlay', function(assert) {
        loadingBar.start(false);
        QUnit.expect(2);

        assert.ok($('.loading-bar').is(':visible'), 'Loading bar has been shown');
        assert.ok(!$('.loading-bar.loading').hasClass('loadingbar-covered'), 'Loading bar does not have overlay');

        QUnit.start();
    });

});
