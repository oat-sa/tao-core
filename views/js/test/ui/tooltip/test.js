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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define([
    'jquery',
    'lodash',
    'ui/tooltip',
], function($, _, tooltip) {
    'use strict';

    QUnit.module('tooltip');

    QUnit.test('jQuery.fn', function(assert) {
        var $el = $($('#for_tooltip_1')),
            tooltipApi;

        assert.equal(typeof $.qtip, 'function', "The tooltip widget is registered");

        $el.qtip({
            theme : 'warning',
            content: {
                text: 'Tooltip content'
            }
        });

        tooltipApi = $el.qtip('api');

        QUnit.assert.ok(!tooltipApi.rendered);

        $el.trigger('mouseover');

        QUnit.stop();
        //wait showing delay
        setTimeout(function() {
            QUnit.start();
            assert.ok(tooltipApi.rendered);
            assert.ok($(tooltipApi.elements.content).is(':visible'));
        }, 500);
    });

    QUnit.test('Tooltipstered element', function(assert) {

        tooltip($('#qunit-fixture'));

        var tooltipApi = $('#tooltipstered_1').qtip('api');
        QUnit.assert.ok(!tooltipApi.rendered);
        $('#tooltipstered_1').trigger('mouseover');

        QUnit.stop();
        //wait showing delay
        setTimeout(function() {
            QUnit.start();
            assert.ok(tooltipApi.rendered);
        }, 500);
    });
});
