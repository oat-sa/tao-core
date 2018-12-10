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
 * Copyright (c) 2015-2018 (original work) Open Assessment Technologies SA ;
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
    var mouseenter = document.createEvent( 'Events' );
    var mouseleave = document.createEvent( 'Events' );
    var themes = ['default','dark', 'info', 'warning', 'error', 'success', 'danger','when theme not exist'];
    var defaultTheme = '<div class="tooltip qtip-rounded qtip-plain" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner qtip-content"></div></div>';

    QUnit.module('tooltip');

    mouseenter.initEvent( 'mouseenter', true, false );
    mouseleave.initEvent( 'mouseleave', true, false );


    QUnit.test('Tooltip: component initialization', function (assert) {
        var $single;
        QUnit.expect(2);
        tooltip($('#' + containerName));
        $single = $( '[data-tooltip]', '#' + containerName).first();
        assert.ok($single.data('$tooltip'), 'tooltip got built from given container');
        assert.throws(function(){tooltip();}, 'tooltip component throws an exception when called without attributes');
    });
    QUnit.test('Tooltip: component as Tooltip instance', function (assert) {
        var $ref = $( '[data-tooltip]', '#' + containerName).first();
        var instance;
        QUnit.expect(1);
        instance = tooltip($ref, {title:'default text'});
        assert.ok(instance, 'tooltip got built from given container');
    });
    QUnit.test('Tooltip: component API', function (assert) {
        var $single;
        var instance;
        var wrapperInstance;
        QUnit.expect(14);
        tooltip($('#' + containerName));
        $single = $( '[data-tooltip]', '#' + containerName).first();
        wrapperInstance = $single.data('$tooltip');
        instance = tooltip($single, { title:'default text'});
        assert.equal(typeof wrapperInstance.show, 'function', 'tooltipAPI: show() defined');
        assert.equal(typeof wrapperInstance.hide, 'function', 'tooltipAPI: hide() defined');
        assert.equal(typeof wrapperInstance.dispose, 'function', 'tooltipAPI: dispose() defined');
        assert.equal(typeof wrapperInstance.toggle, 'function', 'tooltipAPI: toggle() defined');
        assert.equal(typeof wrapperInstance.updateTitleContent, 'function', 'tooltipAPI: updateTitleContent() defined');
        assert.equal(typeof wrapperInstance.options, 'object', 'tooltipAPI: .options defined');
        assert.equal(typeof wrapperInstance._isOpen, 'boolean', 'tooltipAPI: ._isOpen defined');
        assert.equal(typeof instance.show, 'function', 'tooltipAPI: show() defined');
        assert.equal(typeof instance.hide, 'function', 'tooltipAPI: hide() defined');
        assert.equal(typeof instance.dispose, 'function', 'tooltipAPI: dispose() defined');
        assert.equal(typeof instance.toggle, 'function', 'tooltipAPI: toggle() defined');
        assert.equal(typeof instance.updateTitleContent, 'function', 'tooltipAPI: updateTitleContent() defined');
        assert.equal(typeof instance.options, 'object', 'tooltipAPI: .options defined');
        assert.equal(typeof instance._isOpen, 'boolean', 'tooltipAPI: ._isOpen defined');
    });
    QUnit.cases(themes)
        .test('Tooltip: all themes applied', function (data, assert) {
            var $reference = $('#tooltipstered');
            var instance;
            var $single;
            var instance;
            QUnit.expect(2);
            $reference.attr('data-tooltip-theme', data);
            tooltip($('#' + containerName));
            $single = $( '[data-tooltip]', '#' + containerName).first();
            instance = $single.data('$tooltip');

            assert.notEqual(instance.options.template.trim(), '', 'template is not empty string');
            if(data === 'default' || data === 'when theme not exist'){
                assert.equal(instance.options.template, defaultTheme, data +'  default template applied');
            }else{
                assert.notEqual(instance.options.template.trim(), '', data +' template applied');

            }

        });
    QUnit.test('Tooltip: create several tooltips on same page', function (assert) {
        var $reference = $('#tooltipstered');
        var amount = 10;
        var resultAmount = 0;
        var i =1;
        var $container = $('#' + containerName);
        QUnit.expect(1);
        for(i; i < amount;i++){
            $reference.clone().attr('id', 'clonned_'+i).appendTo($container);
        }
        tooltip($container);
        $('[data-tooltip]', $container).each(function(key, item){
            if($(item).data('$tooltip')){
                resultAmount++;
            }
        });

        assert.equal(resultAmount, amount, 'possibility to have several tooltips on page');
    });
    // QUnit.test('JQuery wrapper: tooltip initialization', function (assert) {
    //     var $el = $('<div id="tip-toggle"/>')
    //         .appendTo('#'+containerName)
    //         .qtip(defaultOpts);
    //     QUnit.expect(3);
    //     assert.equal(typeof $.fn.qtip, 'function', "The tooltip wrapper plugin is registered");
    //     assert.equal(typeof $.qtip, 'function', "The tooltip public method is registered");
    //     assert.notEqual(typeof $el.attr('data-hasqtip'), 'undefined', "hasQtip indicator");
    // });
    // QUnit.asyncTest('JQuery wrapper: physical user interactions (mouseEnter, MouseLeave)', function (assert) {
    //     var $el = $('<div id="tip-toggle"/>')
    //         .appendTo('#'+containerName)
    //         .qtip(defaultOpts);
    //     var tooltipApi = $el.qtip('api');
    //     QUnit.expect(4);
    //     $el[0].dispatchEvent(mouseenter);
    //     setTimeout(function() {
    //         var $tooltip = $('.tooltip');
    //         QUnit.start();
    //         assert.ok(tooltipApi._isOpen, 'api visibility parameter is changed to "true"');
    //         assert.equal($tooltip.css('visibility'), 'visible', 'The tooltip become visible on when mouse cursor is in area of reference element (button)');
    //
    //         $el[0].dispatchEvent( mouseleave );
    //         setTimeout(function () {
    //             QUnit.start();
    //             assert.ok(!tooltipApi._isOpen, 'api visibility parameter is changed to "false"');
    //             assert.equal($tooltip.css('visibility'), 'hidden', 'The tooltip become hidden when mouse cursor leave the reference item (button)');
    //         }, 50);
    //         QUnit.stop();
    //     },100);
    // });
    //
    // QUnit.asyncTest('Backward compatibility: Jquery.qtip("show") and Jquery.qtip("hide")', function (assert) {
    //     var $el = $('<div id="tip-show"/>')
    //         .appendTo('#'+containerName)
    //         .qtip(defaultOpts);
    //     var tooltipApi = $el.qtip('api');
    //     QUnit.expect(4);
    //     $el.qtip('show');
    //     setTimeout(function () {
    //         QUnit.start();
    //         assert.ok(tooltipApi._isOpen, 'new component\'s api updated');
    //         assert.equal($('.tooltip').css('visibility'), 'visible', 'The tooltip become visible on .qtip(\'show\') call ');
    //         $el.qtip('hide');
    //         setTimeout(function () {
    //             QUnit.start();
    //             assert.ok(!tooltipApi._isOpen, 'new component\'s api updated');
    //             assert.equal($('.tooltip').css('visibility'), 'hidden', 'The tooltip become hidden on .qtip(\'hide\') call ');
    //         });
    //         QUnit.stop();
    //
    //     });
    // });
    //
    // QUnit.asyncTest('Backward compatibility: Jquery.qtip("toggle")', function (assert) {
    //     var $el =  $('<div id="tip-toggle"/>')
    //         .appendTo('#'+containerName)
    //         .qtip(defaultOpts);
    //     var tooltipApi = $el.qtip('api');
    //     QUnit.expect(4);
    //     $el.qtip('toggle');
    //     setTimeout(function () {
    //         QUnit.start();
    //         assert.ok(tooltipApi._isOpen, 'API ._isOpen changed');
    //         assert.equal($('.tooltip').css('visibility'), 'visible', 'The tooltip become visible on first .qtip(\'toggle\') call ');
    //         $el.qtip('toggle');
    //         setTimeout(function () {
    //             QUnit.start();
    //             assert.ok(!tooltipApi._isOpen, 'API ._isOpen changed');
    //             assert.equal($('.tooltip').css('visibility'), 'hidden', 'The tooltip become hidden on second .qtip(\'toggle\') call ');
    //         });
    //         QUnit.stop();
    //     });
    // });
    //
    // QUnit.asyncTest('Backward compatibility: Jquery.qtip("update", "tooltip text")', function (assert) {
    //     var $elToUpdate = $('<div id="tip-toUpdate"/>')
    //             .appendTo('#'+containerName)
    //             .qtip(defaultOpts);
    //     var $elToUpdateApi = $elToUpdate.qtip('api');
    //     var updateMessage = 'update message';
    //     QUnit.expect(2);
    //     $elToUpdate.qtip('update', updateMessage);
    //     $elToUpdate.qtip('show');
    //     setTimeout(function () {
    //         QUnit.start();
    //         assert.equal($elToUpdateApi.options.title, updateMessage, 'The tooltip message have changed in api');
    //         assert.equal($('.tooltip-inner', '#'+containerName).text(), updateMessage, 'The tooltip message have changed on page ');
    //     });
    // });
    //
    // QUnit.asyncTest('Backward compatibility: Jquery.qtip("set", "content.text", "tooltip text")', function (assert) {
    //     var $elToSet = $('<div id="tip-toSet"/>')
    //             .appendTo('#'+containerName)
    //             .qtip(defaultOpts);
    //     var $elToSetApi = $elToSet.qtip('api');
    //     var setMessage = 'new set message';
    //     QUnit.expect(2);
    //     $elToSet.qtip('set', 'content.text', setMessage);
    //     $elToSet.qtip('show');
    //     setTimeout(function () {
    //         QUnit.start();
    //         assert.equal($elToSetApi.options.title, setMessage, 'The tooltip message have changed in api');
    //         assert.equal($('.tooltip-inner', '#'+containerName).text(), setMessage, 'The tooltip message have changed inside html representation ');
    //     });
    // });
    //
    // QUnit.asyncTest('Backward compatibility: Jquery.qtip("destroy")', function (assert) {
    //     var $elToDestroy = $('<div id="tip-toDestroy"/>')
    //             .appendTo('#'+containerName)
    //             .qtip(defaultOpts);
    //     var $elToDestroyApi = $elToDestroy.qtip('api');
    //     QUnit.expect(2);
    //     $elToDestroy.qtip('show');
    //     setTimeout(function () {
    //         QUnit.start();
    //         $elToDestroy.qtip('destroy');
    //         setTimeout(function () {
    //             QUnit.start();
    //             assert.ok(!$elToDestroyApi._isOpen, 'api is nulled after destroy calling');
    //             assert.equal($('.tooltip-inner','#'+containerName).length, 0, 'The tooltip is removed from DOM ');
    //         });
    //         QUnit.stop();
    //     });
    // });

    QUnit.module('Visual');

    QUnit.test('playground', function(assert) {
        var $container = $('#visible-fixture');
        var $elm = $('#visible-tooltip', $container);
        var $themeSelect = $('#theme-select', $container);
        QUnit.expect(1);
        tooltip($container);
        assert.ok($elm.data("$tooltip"), 'tooltip instance is defined');
        themes.forEach(function (value) {
            $themeSelect.append('<option value="'+value+'">'+value+'</option>');
        });

        $('#change-theme', $container).click(function ($e) {
            var $ref = $('#visible-tooltip', $container);
            var instance;

            instance = new tooltip($ref,{
                theme: $themeSelect.val(),
                title: $ref.data('$tooltip').options.title
            });
            instance.show();
            $e.preventDefault();
            $e.stopPropagation();
            return false;
        });
        $('#toggle-tooltip', $container).click(function ($e) {
            $('#visible-tooltip', $container).data('$tooltip').toggle();
            $e.preventDefault();
            $e.stopPropagation();
            return false;
        });


    });

});
