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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Sam <sam@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/dynamicComponent'
], function ($, _, dynamicComponent){
    'use strict';

    QUnit.module('component');


    QUnit.test('module', 3, function (assert){
        assert.equal(typeof dynamicComponent, 'function', "The component module exposes a function");
        assert.equal(typeof dynamicComponent(), 'object', "The component factory produces an object");
        assert.notStrictEqual(dynamicComponent(), dynamicComponent(), "The component factory provides a different object on each call");
    });


    QUnit
        .cases([
            {title : 'init'},
            {title : 'destroy'},
            {title : 'render'},
            {title : 'show'},
            {title : 'hide'},
            {title : 'enable'},
            {title : 'disable'},
            {title : 'is'},
            {title : 'setState'},
            {title : 'getContainer'},
            {title : 'getElement'},
            {title : 'getTemplate'},
            {title : 'setTemplate'},
            {title : 'reset'},
            {title : 'resetPosition'},
            {title : 'resetSize'},
            {title : 'resetSize'}
        ])
        .test('instance API ', function (data, assert){
            var instance = dynamicComponent();
            assert.equal(typeof instance[data.title], 'function', 'The component instance exposes a "' + data.title + '" function');
        });


    QUnit.test('init', function (assert){
        var specs = {
            value : 10,
            method : function (){

            }
        };
        var defaults = {
            label : 'a label'
        };
        var config = {
            nothing : undefined,
            dummy : null,
            title : 'My Title'
        };
        var instance = dynamicComponent(specs, defaults).init(config);

        assert.notEqual(instance, specs, 'The component instance must not be the same obect as the list of specs');
        assert.notEqual(instance.config, config, 'The component instance must duplicate the config set');
        assert.equal(instance.hasOwnProperty('nothing'), false, 'The component instance must not accept undefined config properties');
        assert.equal(instance.hasOwnProperty('dummy'), false, 'The component instance must not accept null config properties');
        assert.equal(instance.hasOwnProperty('value'), false, 'The component instance must not accept properties from the list of specs');
        assert.equal(instance.config.title, config.title, 'The component instance must catch the title config');
        assert.equal(instance.config.label, defaults.label, 'The component instance must set the label config');
        assert.equal(instance.is('rendered'), false, 'The component instance must not be rendered');
        assert.equal(typeof instance.method, 'function', 'The component instance must have the functions provided in the list of specs');
        assert.notEqual(instance.method, specs.method, 'The component instance must have created a delegate of the functions provided in the list of specs');

        instance.destroy();
    });

    QUnit.asyncTest('render', function (assert){
        var $dummy1 = $('<div class="dummy" />');
        var $content1 = $('<div class="my-custom-content">BBB</div>');
        var $container1 = $('#fixture-1').append($dummy1);

        QUnit.expect(19);

        // auto render at init
        assert.equal($container1.children().length, 1, 'The container1 already contains an element');
        assert.equal($container1.children().get(0), $dummy1.get(0), 'The container1 contains the dummy element');
        assert.equal($container1.find('.dummy').length, 1, 'The container1 contains an element of the class dummy');

        dynamicComponent({}, {
            title : 'AAA'
        }).on('rendercontent', function ($content){
            //init the calculator
            $content.append($content1);

            assert.equal($container1.find('.dummy').length, 0, 'The container1 does not contain an element of the class dummy');
            assert.equal(this.is('rendered'), true, 'The component instance must be rendered');
            assert.equal(typeof this.getElement(), 'object', 'The component instance returns the rendered content as an object');
            assert.equal(this.getElement().length, 1, 'The component instance returns the rendered content');
            assert.equal(this.getElement().parent().get(0), $container1.get(0), 'The component instance is rendered inside the right container');

            assert.equal($container1.find('.dynamic-component-container').length, 1, 'Dynamic component container ok');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar').length, 1, 'Dynamic component title ok');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar h3').text(), 'AAA', 'Dynamic component title is empty');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar .closer').length, 1, 'Dynamic component title has closer');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content').length, 1, 'Dynamic component has content');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').length, 1, 'Dynamic component has content');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').text(), 'BBB', 'content ok');

            //check configured size
            assert.equal($container1.find('.dynamic-component-container').outerWidth(), 300, 'width ok');
            assert.equal($container1.find('.dynamic-component-container').outerHeight(), 200, 'height ok');

            //check configured position
            assert.equal($container1.find('.dynamic-component-container').offset().top, 10, 'position top ok');
            assert.equal($container1.find('.dynamic-component-container').offset().left, 10, 'position top left');

            //manual reset
            $('#fixture-1').empty().attr('style', '');

            QUnit.start();
        }).init({
            renderTo : $container1,
            replace : true,
            top : 10,
            left : 10,
            height : 200,
            width : 300
        });
    });

    QUnit.asyncTest('reset', function (assert){

        var $content1 = $('<div class="my-custom-content">BBB</div>');
        var $container1 = $('#fixture-1');
        var instance = dynamicComponent({}, {
            title : 'AAA'
        }).on('rendercontent', function ($content){
            //init the calculator
            $content.append($content1);
        }).init({
            renderTo : $container1,
            replace : true,
            top : 10,
            left : 10,
            height : 200,
            width : 300
        });

        assert.equal($container1.find('.dynamic-component-container').length, 1, 'Dynamic component container ok');
        assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar').length, 1, 'Dynamic component title ok');
        assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar h3').text(), 'AAA', 'Dynamic component title is empty');
        assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar .closer').length, 1, 'Dynamic component title has closer');
        assert.equal($container1.find('.dynamic-component-container .dynamic-component-content').length, 1, 'Dynamic component has content');
        assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').length, 1, 'Dynamic component has content');
        assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').text(), 'BBB', 'Dynamic component has content');

        //check configured size
        assert.equal($container1.find('.dynamic-component-container').outerWidth(), 300, 'width ok');
        assert.equal($container1.find('.dynamic-component-container').outerHeight(), 200, 'height ok');

        //check configured position
        assert.equal($container1.find('.dynamic-component-container').offset().top, 10, 'position top ok');
        assert.equal($container1.find('.dynamic-component-container').offset().left, 10, 'position top left');

        instance.on('reset', function (){
            assert.ok(true, 'The component instance can handle reset events');

            this.getElement().find('.my-custom-content').empty();

            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').length, 1, 'Dynamic component has content');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').text(), '', 'Dynamic component has emptied content');

            //check reset size
            assert.equal($container1.find('.dynamic-component-container').outerWidth(), 300, 'width ok');
            assert.equal($container1.find('.dynamic-component-container').outerHeight(), 200, 'height ok');

            //check reset position
            assert.equal($container1.find('.dynamic-component-container').offset().top, 10, 'position top ok');
            assert.equal($container1.find('.dynamic-component-container').offset().left, 10, 'position top left');
            QUnit.start();

        });

        $container1.find('.dynamic-component-container').css({top : 100, left : 100});
        $container1.find('.dynamic-component-container .dynamic-component-content').width(500).height(400);

        instance.reset();

        //manual reset
        $('#fixture-1').empty().attr('style', '');
    });

    QUnit.asyncTest('content size', function (assert){

        var $content1 = $('<div class="my-custom-content">BBB</div>');
        var $container1 = $('#fixture-1');

        dynamicComponent({}, {
            title : 'AAA'
        }).after('rendercontent', function ($content){
            var $element = this.getElement();
            var diffWidth = $element.outerWidth() - $element.width();
            var diffHeight = $element.outerHeight() - $element.height() + $element.find('.dynamic-component-title-bar').outerHeight();

            //init the calculator
            $content.append($content1.width(500).height(400));

            assert.equal($container1.find('.dynamic-component-container').length, 1, 'Dynamic component container ok');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar').length, 1, 'Dynamic component title ok');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar h3').text(), 'AAA', 'Dynamic component title is empty');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar .closer').length, 1, 'Dynamic component title has closer');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content').length, 1, 'Dynamic component has content');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').length, 1, 'Dynamic component has content');
            assert.equal($container1.find('.dynamic-component-container .dynamic-component-content .my-custom-content').text(), 'BBB', 'Dynamic component has content');

            assert.equal($element.find('.my-custom-content').outerWidth(), 500, 'initial content width ok');
            assert.equal($element.find('.my-custom-content').outerHeight(), 400, 'initial content height ok');

            //check configured size
            assert.equal($element.outerWidth(), 300, 'initial width ok');
            assert.equal($element.outerHeight(), 200, 'initial height ok');

            //check configured position
            assert.equal($element.offset().top, 10, 'initial position top ok');
            assert.equal($element.offset().left, 10, 'initial position top left');

            $element.css({top : 100, left : 100});
            assert.equal($element.offset().top, 100, 'updated position top ok');
            assert.equal($element.offset().left, 100, 'updated position top left');

            this
                .on('resize.setContentSize', function (){
                    this.off('resize.setContentSize');
                    assert.ok(true, 'The component instance can handle content resize events');

                    $element.find('.my-custom-content').empty();

                    assert.equal($element.find('.dynamic-component-content .my-custom-content').length, 1, 'Dynamic component has content');
                    assert.equal($element.find('.dynamic-component-content .my-custom-content').text(), '', 'Dynamic component has emptied content');

                    //check reset size
                    assert.equal($element.outerWidth(), 500 + diffWidth, 'final width ok');
                    assert.equal($element.outerHeight(), 400 + diffHeight, 'final height ok');

                    //check reset position
                    assert.equal($element.offset().top, 100, 'final position top ok');
                    assert.equal($element.offset().left, 100, 'final position top left');

                    //manual reset
                    $('#fixture-1').empty().attr('style', '');
                    QUnit.start();
                })
                .setContentSize(500, 400);
        }).init({
            renderTo : $container1,
            replace : true,
            top : 10,
            left : 10,
            height : 200,
            width : 300
        });
    });


    QUnit.asyncTest('close', function (assert){

        var $container1 = $('#fixture-0');
        dynamicComponent().init({
            renderTo : $container1
        }).on('hide', function(){
            assert.ok('dynamic component hidden with the closer');
            QUnit.start();
        });

        assert.equal($container1.find('.dynamic-component-container .dynamic-component-title-bar .closer').length, 1, 'Dynamic component title has closer');

        $container1.find('.dynamic-component-container .dynamic-component-title-bar .closer').click();
    });

    QUnit.asyncTest('reset control', function (assert){
        var $container1 = $('#fixture-0');

        QUnit.expect(2);

        dynamicComponent()
        .on('rendercontent', function(){
            var self = this;
            var $resetControl = $('.dynamic-component-container .dynamic-component-title-bar .reset', $container1);
            assert.equal($resetControl.length, 1, 'Dynamic component title has a reset control');

            //ensure initial resize aren't taken into account
            _.delay(function(){
                self.on('resize', function(){
                    assert.ok('dynamic component reset using the control');
                    QUnit.start();
                });

                $resetControl.trigger('click');
            }, 100);
        })
        .init({
            renderTo : $container1,
            resizable : true
        });
    });

    QUnit.asyncTest('reset control config', function (assert){
        var $container1 = $('#fixture-0');

        QUnit.expect(1);

        dynamicComponent()
        .on('rendercontent', function(){
            var $resetControl = $('.dynamic-component-container .dynamic-component-title-bar .reset', $container1);
            assert.equal($resetControl.length, 0, 'The reset control is there only on resizable components');

            QUnit.start();
        })
        .init({
            renderTo : $container1,
            resizable : false
        });
    });
});
