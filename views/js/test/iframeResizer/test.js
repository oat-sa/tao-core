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
define(['jquery', 'iframeResizer'], function($, iframeResizer) {
    'use strict';

    var $fixture = $('#qunit-fixture');
    var fixture = 'qunit-fixture';

    QUnit.test('parser structure', function(assert) {
        assert.ok(typeof iframeResizer === 'object');
        assert.ok(typeof iframeResizer.autoHeight === 'function');
    });

    QUnit.test('resize on load', function(assert) {
        var ready = assert.async();
        var frame = document.getElementById(fixture).querySelector('#iframe1');
        assert.equal($(frame).length, 1);

        iframeResizer.autoHeight($(frame));

        frame.src = 'js/test/iframeResizer/framecontent1.html';
        frame.onload = function() {
            assert.equal(parseInt($(frame).height(), 10), 500);
            ready();
        };
    });

    QUnit.test('resize after load', function(assert) {
        var ready = assert.async();
        var frame = document.getElementById(fixture).querySelector('#iframe2');

        assert.equal($(frame).length, 1);

        iframeResizer.autoHeight($(frame));
        frame.src = 'js/test/iframeResizer/framecontent2.html';
        frame.onload = function() {
            assert.equal(parseInt($(frame).height(), 10), 200);
            setTimeout(function() {
                assert.equal(parseInt($(frame).height(), 10), 600);
                ready();
            }, 2000);
        };

    });

    QUnit.test('nested iframes', function(assert) {
        var ready = assert.async();
        var frame = document.getElementById(fixture).querySelector('#iframe3');

        assert.equal($(frame).length, 1);

        iframeResizer.autoHeight($(frame), 'iframe');
        frame.src = 'js/test/iframeResizer/framecontent3.html';
        frame.onload = function() {
            var $nested = $(frame).contents().find('iframe');

            iframeResizer
                .autoHeight($nested)
                .attr('src', 'framecontent2.html');

            setTimeout(function() {
                assert.ok(parseInt($(frame).height(), 10) >= 600); //The div that contains the iframe has a 604 height!
                ready();
            }, 2500);
        };
    });
});

