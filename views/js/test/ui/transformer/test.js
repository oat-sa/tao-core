define([
    'jquery',
    'ui/transformer'
], function ($, transformer) {

    'use strict';

    function resetContainer(transformation) {
        var $fixture = $('#qunit-fixture').empty();
        var $container = $('<div id="container"/>');
        if (transformation) {
            $container.addClass('pre-' + transformation);
        }
        $fixture.append($container);
        return $container;
    }

    function getRect($container) {
        var rect = $container[0].getBoundingClientRect(),
            key,
            retVal = {};

        // rounding because especially on rotation and skewing values can differ slightly
        for (key in rect) {
            retVal[key] = Math.round(rect[key]);
        }
        return retVal;
    }

    QUnit.module('Transformer');

    QUnit.test('Module', function (assert) {
        QUnit.expect(1);
        assert.ok(typeof transformer !== 'undefined', 'The module exports something');
    });

    QUnit.test('API', function (assert) {
        QUnit.expect(9);

        assert.ok(typeof transformer === 'object', 'Transformer returns an object');
        assert.ok(typeof transformer.translate === 'function', 'Exposes method translate()');
        assert.ok(typeof transformer.translateX === 'function', 'Exposes method translateX()');
        assert.ok(typeof transformer.translateY === 'function', 'Exposes method translateY()');
        assert.ok(typeof transformer.rotate === 'function', 'Exposes method rotate()');
        assert.ok(typeof transformer.skew === 'function', 'Exposes method skew()');
        assert.ok(typeof transformer.scale === 'function', 'Exposes method scale()');
        assert.ok(typeof transformer.scaleX === 'function', 'Exposes method scaleX()');
        assert.ok(typeof transformer.scaleY === 'function', 'Exposes method scaleY()');
    });

    QUnit.test('Basics', function (assert) {
        var $container = $('#container');

        QUnit.expect(2);

        assert.equal($container.length, 1, 'Container exists');

        transformer.scale($container, 2);
        assert.deepEqual(
            $container.data('oriTrans').obj,
            {
                translateX: 0,
                translateY: 0,
                rotate: 0,
                skew: 0,
                scaleX: 1,
                scaleY: 1
            },
            'Container memorizes its original transformation'
        );
    });


    QUnit.test('Translating / neutral container', function (assert) {
        var $container = $('#container'),
            rect,
            origRect = getRect($container);

        QUnit.expect(5);

        // Translation neutral container
        transformer.translateX($container, 100);
        rect = getRect($container);
        assert.ok(rect.left === origRect.left + 100, 'translateX(100)');

        $container = resetContainer();
        transformer.translateY($container, 100);
        rect = getRect($container);
        assert.ok(rect.top === origRect.top + 100, 'translateY(100)');

        $container = resetContainer();
        transformer.translate($container, 100);
        rect = getRect($container);
        assert.ok(rect.left === origRect.left + 100 && rect.top === origRect.top + 100, 'translate(100)');

        $container = resetContainer();
        transformer.translateXY($container, 150, 125);
        rect = getRect($container);
        assert.ok(rect.left === origRect.left + 150 && rect.top === origRect.top + 125, 'translateXY(150, 125)');

        $container = resetContainer();
        transformer.translateXY($container, 150, 0);
        rect = getRect($container);
        assert.ok(rect.left === origRect.left + 150 && rect.top === origRect.top, 'translateXY(150, 0)');
    });


    QUnit.test('Translating / pre-transformed container', function (assert) {
        var $container = resetContainer('translate'),
            rect,
            origTop = getRect($container).top;

        QUnit.expect(5);

        transformer.translateX($container, 100);
        rect = getRect($container);
        assert.ok(rect.left === 200, 'translateX(100) on top of existing 100px');

        $container = resetContainer('translate');
        transformer.translateY($container, 100);
        rect = getRect($container);
        assert.ok(rect.top === origTop + 100, 'translateY(100) on top of existing 100px');

        $container = resetContainer('translate');
        transformer.translate($container, 100);
        rect = getRect($container);
        assert.ok(rect.left === 200 && rect.top === origTop + 100, 'translate(100) on top of existing 100px');

        $container = resetContainer('translate');
        transformer.translateXY($container, 150, 125);
        rect = getRect($container);
        assert.ok(rect.left === 250 && rect.top === origTop + 125, 'translate(150, 125) on top of existing 100px');

        $container = resetContainer('translate');
        transformer.translateXY($container, 150, 0);
        rect = getRect($container);
        assert.ok(rect.left === 250 && rect.top === origTop, 'translate(150, 0) on top of existing 100px');
    });


    QUnit.test('Rotating / neutral container', function (assert) {
        var $container = $('#container'),
            rect;

        QUnit.expect(1);

        transformer.rotate($container, 45);
        rect = getRect($container);
        assert.ok(rect.height === rect.width && rect.height === 141, 'rotate(45)');
    });


    QUnit.test('Rotating / pre-transformed container', function (assert) {
        var $container = resetContainer('rotate'),
            rect;

        QUnit.expect(1);

        transformer.rotate($container, 25);
        rect = getRect($container);
        assert.ok(rect.height === rect.width && rect.height === 141, 'rotate(25) on top of existing 20deg');
    });


    QUnit.test('Skewing / neutral container', function (assert) {
        var $container = $('#container'),
            rect;

        QUnit.expect(1);

        transformer.skew($container, 45);
        rect = getRect($container);
        assert.ok(rect.width === 200 && rect.height === 100, 'skew(45)');
    });


     QUnit.test('Skewing / pre-transformed container', function (assert) {
         var $container = resetContainer('skew'),
             rect;

         QUnit.expect(1);

         transformer.skew($container, 25);
         rect = getRect($container);
         assert.ok(rect.width === 200 && rect.height === 100, 'skew(45) on top of existing 20deg');
     });


    QUnit.test('Scaling / neutral container', function (assert) {
        var $container = $('#container'),
            rect;

        QUnit.expect(3);

        // Scaling
        transformer.scaleX($container, 3);
        rect = getRect($container);
        assert.ok(rect.height === 100 && rect.width === 300, 'scaleX(3)');

        $container = resetContainer();
        transformer.scaleY($container, 3);
        rect = getRect($container);
        assert.ok(rect.width === 100 && rect.height === 300, 'scaleY(3)');

        $container = resetContainer();
        transformer.scale($container, 3);
        rect = getRect($container);
        assert.ok(rect.width === rect.height && rect.width === 300, 'scale(3)');
    });


    QUnit.test('Scaling / pre-transformed container', function (assert) {
        var $container = resetContainer('scale'),
            rect;

        QUnit.expect(3);

        transformer.scaleX($container, 3);
        rect = getRect($container);
        assert.ok(rect.height === 100 && rect.width === 450, 'scaleX(3) on top of existing 1.5');

        $container = resetContainer('scale');
        transformer.scaleY($container, 3);
        rect = getRect($container);
        assert.ok(rect.width === 100 && rect.height === 450, 'scaleY(3) on top of existing 1.5');

        $container = resetContainer('scale');
        transformer.scale($container, 3);
        rect = getRect($container);
        assert.ok(rect.width === rect.height && rect.width === 450, 'scale(3) on top of existing 1.5');
    });


});
