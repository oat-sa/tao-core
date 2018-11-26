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
    'ui/tooltip'
], function($, _, tooltip) {
    'use strict';
    var containerName = 'qunit-fixture';
    // invoking native mouse events
    var mouseenter = document.createEvent( 'Events' );
    var mouseleave = document.createEvent( 'Events' );
    var defaultOpts = {
        theme : 'warning',
        content: {
            text: 'Tooltip content'
        }
    };
    var themes = ['default','dark', 'info', 'warning', 'error', 'success', 'danger','when theme not exist'];
    var defaultTheme = '<div class="tooltip qtip-rounded qtip-plain" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>';

    QUnit.module('tooltip');

    // mouse events imitation
    mouseenter.initEvent( 'mouseenter', true, false );
    mouseleave.initEvent( 'mouseleave', true, false );


    QUnit.test('Tooltip: component initialization', function (assert) {
        var $single;
        QUnit.expect(2);
        tooltip($('#' + containerName));
        $single = $( '[data-tooltip]', '#' + containerName).first();
        assert.ok($single.data('$popper'), 'tooltip got built from given container');
        assert.throws(tooltip(), 'tooltip component throws an exception when called without attributes');
    });
    QUnit.test('Tooltip: component API', function (assert) {
        var $single, $popper;
        QUnit.expect(8);
        tooltip($('#' + containerName));
        $single = $( '[data-tooltip]', '#' + containerName).first();
        $popper = $single.data('$popper');
        assert.ok($popper, 'tooltip got built from given container');
        assert.equal(typeof $popper.show, 'function', 'tooltipAPI: show() defined');
        assert.equal(typeof $popper.hide, 'function', 'tooltipAPI: hide() defined');
        assert.equal(typeof $popper.dispose, 'function', 'tooltipAPI: dispose() defined');
        assert.equal(typeof $popper.toggle, 'function', 'tooltipAPI: toggle() defined');
        assert.equal(typeof $popper.updateTitleContent, 'function', 'tooltipAPI: updateTitleContent() defined');
        assert.equal(typeof $popper.options, 'object', 'tooltipAPI: .options defined');
        assert.equal(typeof $popper._isOpen, 'boolean', 'tooltipAPI: ._isOpen defined');
    });
    QUnit.cases(themes)
        .test('Tooltip: all themes applied', function (data, assert) {
            var $reference = $('#tooltipstered'), $single, $popper;
            QUnit.expect(2);
            $reference.attr('data-tooltip-theme', data);
            tooltip($('#' + containerName));
            $single = $( '[data-tooltip]', '#' + containerName).first();
            $popper = $single.data('$popper');


            assert.notEqual($popper.options.template.trim(), '', 'template is not empty string');
            if(data === 'default' || data === 'when theme not exist'){
                assert.equal($popper.options.template, defaultTheme, data +'  default template applied');
            }else{
                assert.notEqual($popper.options.template.trim(), '', data +' template applied');

            }

        });
    QUnit.test('Tooltip: several tooltips on same page', function (assert) {
        var $reference = $('#tooltipstered'), amount =10, resultAmount=0, instances = [];
        var i =1;
        var $container = ('#' + containerName);
        QUnit.expect(1);
        for(i; i < amount;i++){
            $reference.clone().attr('id', 'clonned_'+i).appendTo($container);
        }
        tooltip($container);
        $('[data-tooltip]', $container).each(function(key, item){
            //check for Popper instance attached to DOM element
            if($(item).data('$popper')){
                resultAmount++;
            }
        });

        resultAmount = $('[data-tooltip]', $container).length;
        assert.equal(resultAmount, amount, 'possibility to have several tooltips on page');
    });
    QUnit.test('JQuery wrapper: tooltip initialization', function (assert) {
        var $el = $('<div id="tip-toggle"/>')
            .appendTo('#'+containerName)
            .qtip(defaultOpts);
        QUnit.expect(3);
        assert.equal(typeof $.fn.qtip, 'function', "The tooltip wrapper plugin is registered");
        assert.equal(typeof $.qtip, 'function', "The tooltip public method is registered");
        assert.notEqual(typeof $el.attr('data-hasqtip'), 'undefined', "hasQtip indicator");
    });
    //basic physical user interactions (mouseEnter, mouseLeave)
    QUnit.asyncTest('JQuery wrapper: physical user interactions (mouseEnter, MouseLeave)', function (assert) {
        var $el = $('<div id="tip-toggle"/>')
            .appendTo('#'+containerName)
            .qtip(defaultOpts);
        QUnit.expect(4);
        var tooltipApi = $el.qtip('api');
        $el[0].dispatchEvent(mouseenter);
        setTimeout(function() {
            var $tooltip = $('.tooltip');
            QUnit.start();
            // invoking native mouse events
            assert.ok(tooltipApi._isOpen, 'api visibility parameter is changed to "true"');
            assert.equal($tooltip.css('visibility'), 'visible', 'The tooltip become visible on when mouse cursor is in area of reference element (button)');

            $el[0].dispatchEvent( mouseleave );
            setTimeout(function () {
                QUnit.start();
                assert.ok(!tooltipApi._isOpen, 'api visibility parameter is changed to "false"');
                assert.equal($tooltip.css('visibility'), 'hidden', 'The tooltip become hidden when mouse cursor leave the reference item (button)');
            }, 50);
            QUnit.stop();
        },100);
    });

    // former tooltip plugin compatibility API (jQuery.qtip plugin)
    QUnit.asyncTest('Backward compatibility: Jquery.qtip("show") and Jquery.qtip("hide")', function (assert) {
        var $el = $('<div id="tip-show"/>')
            .appendTo('#'+containerName)
            .qtip(defaultOpts);
        var tooltipApi = $el.qtip('api');
        QUnit.expect(4);
        $el.qtip('show');
        setTimeout(function () {
            QUnit.start();
            assert.ok(tooltipApi._isOpen, 'new component\'s api updated');
            assert.equal($('.tooltip').css('visibility'), 'visible', 'The tooltip become visible on .qtip(\'show\') call ');
            $el.qtip('hide');
            setTimeout(function () {
                QUnit.start();
                assert.ok(!tooltipApi._isOpen, 'new component\'s api updated');
                assert.equal($('.tooltip').css('visibility'), 'hidden', 'The tooltip become hidden on .qtip(\'hide\') call ');
            });
            QUnit.stop();

        });
    });

    QUnit.asyncTest('Backward compatibility: Jquery.qtip("toggle")', function (assert) {
        var $el =  $('<div id="tip-toggle"/>')
            .appendTo('#'+containerName)
            .qtip(defaultOpts);
        var tooltipApi = $el.qtip('api');
        QUnit.expect(4);
        $el.qtip('toggle');
        setTimeout(function () {
            QUnit.start();
            assert.ok(tooltipApi._isOpen, 'API ._isOpen changed');
            assert.equal($('.tooltip').css('visibility'), 'visible', 'The tooltip become visible on first .qtip(\'toggle\') call ');
            $el.qtip('toggle');
            setTimeout(function () {
                QUnit.start();
                assert.ok(!tooltipApi._isOpen, 'API ._isOpen changed');
                assert.equal($('.tooltip').css('visibility'), 'hidden', 'The tooltip become hidden on second .qtip(\'toggle\') call ');
            });
            QUnit.stop();
        });
    });

    QUnit.asyncTest('Backward compatibility: Jquery.qtip("update", "tooltip text")', function (assert) {
        var $elToUpdate = $('<div id="tip-toUpdate"/>')
                .appendTo('#'+containerName)
                .qtip(defaultOpts);
        var $elToUpdateApi = $elToUpdate.qtip('api');
        var updateMessage = 'update message';
        QUnit.expect(2);
        $elToUpdate.qtip('update', updateMessage);
        $elToUpdate.qtip('show');
        setTimeout(function () {
            QUnit.start();
            assert.equal($elToUpdateApi.options.title, updateMessage, 'The tooltip message have changed in api');
            assert.equal($('.tooltip-inner', '#'+containerName).text(), updateMessage, 'The tooltip message have changed on page ');
        });
    });

    QUnit.asyncTest('Backward compatibility: Jquery.qtip("set", "content.text", "tooltip text")', function (assert) {
        var $elToSet = $('<div id="tip-toSet"/>')
                .appendTo('#'+containerName)
                .qtip(defaultOpts);
        var $elToSetApi = $elToSet.qtip('api');
        var setMessage = 'new set message';
        QUnit.expect(2);
        $elToSet.qtip('set', 'content.text', setMessage);
        $elToSet.qtip('show');
        setTimeout(function () {
            QUnit.start();
            assert.equal($elToSetApi.options.title, setMessage, 'The tooltip message have changed in api');
            assert.equal($('.tooltip-inner', '#'+containerName).text(), setMessage, 'The tooltip message have changed inside html representation ');
        });
    });

    QUnit.asyncTest('Backward compatibility: Jquery.qtip("destroy")', function (assert) {
        // $el.qtip('destroy');
        var $elToDestroy = $('<div id="tip-toDestroy"/>')
                .appendTo('#'+containerName)
                .qtip(defaultOpts);
        var $elToDestroyApi = $elToDestroy.qtip('api');
        QUnit.expect(2);
        $elToDestroy.qtip('show');
        setTimeout(function () {
            QUnit.start();
            $elToDestroy.qtip('destroy');
            setTimeout(function () {
                QUnit.start();
                assert.ok(!$elToDestroyApi._isOpen, 'api is nulled after destroy calling');
                assert.equal($('.tooltip-inner','#'+containerName).length, 0, 'The tooltip is removed from DOM ');
            });
            QUnit.stop();
        });
    });

    QUnit.module('Visual');

    QUnit.test('playground', function(assert) {
        var $container = $('#visible-fixture');
        var $elm = $('#visible-tooltip', $container);
        var $themeSelect = $('#theme-select', $container);
        QUnit.expect(1);
        tooltip($container);
        assert.ok($elm.data("$popper"), 'popper instance is defined');
        themes.forEach(function (value) {
            $themeSelect.append('<option value="'+value+'">'+value+'</option>');
        });

        $('#change-theme', $container).click(function ($e) {
            //$themeSelect.val()
            var $tootip = $('#visible-tooltip', $container);
            $tootip.qtip({
                theme: $themeSelect.val(),
                show:true,
                content:{
                    text: $tootip.data('$popper').options.title
                }
            } );
            $tootip.qtip('show');
            $e.preventDefault();
            $e.stopPropagation();
            return false;
        });
        $('#toggle-tooltip', $container).click(function ($e) {
            $('#visible-tooltip', $container).data('$popper').toggle();
            $e.preventDefault();
            $e.stopPropagation();
            return false;
        });


    });

});
