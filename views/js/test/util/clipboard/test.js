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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */
define(['jquery', 'util/clipboard'], function($, clipboard) {
    'use strict';

    QUnit.module('API');

    QUnit.test('eventifier', function(assert) {
        assert.expect(3);

        assert.ok(typeof clipboard.on === 'function', 'the eventifier has a on method');
        assert.ok(typeof clipboard.off === 'function', 'the eventifier has a off method');
        assert.ok(typeof clipboard.trigger === 'function', 'the eventifier has a trigger method');
    });

    QUnit.module('Methods');

    QUnit.test('copyFromEl', function(assert) {
        var $container;
        var $act;
        var $copyFrom, $copyTo;
        var $btn, $askAction, $result, $pasteState;

        $container = $('#qunit-fixture');
        $act = $('#test');
        assert.equal($container.length, 1, 'The container exists');

        $copyFrom = $('<div>Text to be copied</div>');
        $copyTo = $('<p></p>');
        $askAction = $('<div><b>We need user action:</b><div class="btn"></div><div class="status">Click btn, pls</div> </div>');
        $btn = $('<button>Click it</button>');
        $('.btn', $askAction).append($btn);
        $container.append($copyFrom).append($copyTo);
        $act.append($askAction);
        $result = $('#result');
        $pasteState = $('#paste-state');
        $btn.on('click', function(){
            clipboard
                .on('copied', function(){
                    $('.status', $askAction).text('success');
                })
                .on('copyError', function () {
                    $('.status', $askAction).text('error');
                })
                .copyFromEl($copyFrom);
        });

        $('#paste').on('click', function () {
            clipboard
                .on('paste', function () {
                    $pasteState.text('success');
                })
                .on('pasteError', function (status) {
                    $pasteState.text(status.reason);
                })
                .paste($result);
        });

    });
});

