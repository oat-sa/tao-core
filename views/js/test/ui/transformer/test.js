define([
    'jquery',
    'ui/transformer'
], function($, transformer){

    'use strict';

    var $container = $('#container');

    function resetContainer(transformed){
        var $fixture   = $('#qunit-fixture').empty();
        $container = $('<div id="container"/>');
        if(!!transformed){
            $container.addClass('transformed');
        }
        $fixture.append($container);
    }

    function getRect($container) {
        var rect = $container[0].getBoundingClientRect(),
            key,
            retVal = {};

        // ceiling because especially on rotation and skewing values can differ slightly
        for(key in rect) {
            retVal[key] = Math.ceil(rect[key]);
        }
        return retVal;
    }

    QUnit.module('Transformer');

    QUnit.test('Module', function(assert){
        assert.ok(typeof transformer !== 'undefined', 'The module exports something');
    });

    QUnit.test('API', function(assert){
        assert.ok(typeof transformer === 'object', 'Transformer returns an object');
        assert.ok(typeof transformer.transform === 'function', 'Exposes method transform()');
        assert.ok(typeof transformer.translate === 'function', 'Exposes method translate()');
        assert.ok(typeof transformer.translateX === 'function', 'Exposes method translateX()');
        assert.ok(typeof transformer.translateY === 'function', 'Exposes method translateY()');
        assert.ok(typeof transformer.rotate === 'function', 'Exposes method rotate()');
        assert.ok(typeof transformer.skew === 'function', 'Exposes method skew()');
        assert.ok(typeof transformer.skewX === 'function', 'Exposes method skewX()');
        assert.ok(typeof transformer.skewY === 'function', 'Exposes method skewY()');
        assert.ok(typeof transformer.scale === 'function', 'Exposes method scale()');
        assert.ok(typeof transformer.scaleX === 'function', 'Exposes method scaleX()');
        assert.ok(typeof transformer.scaleY === 'function', 'Exposes method scaleY()');
    });

    QUnit.test('Basics', function(assert){

        assert.equal($container.length, 1, 'Container exists');

        transformer.scale($container, 2);
        assert.deepEqual(
            $container.data('oriTrans'),
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


    QUnit.test('Transformations', function(assert){

        var rect,
            origRectNeutral = getRect($container),
            origRectTrans = (function() {
                resetContainer(true);
                var rect = getRect($container);
                resetContainer();
                return rect;
            }());

        var $bar = $('#bar');

//        // Translation neutral container
//        transformer.translateX($container, 100);
//        rect = getRect($container);
//        assert.ok(rect.left === origRectNeutral.left + 100, 'translateX() on a neutral container');
//        resetContainer();
//
//        transformer.translateY($container, 100);
//        rect = getRect($container);
//        assert.ok(rect.top === origRectNeutral.top + 100, 'translateY() on a neutral container');
//        resetContainer();
//
//        transformer.translate($container, 100);
//        rect = getRect($container);
//        assert.ok(rect.left === origRectNeutral.left + 100 && rect.top === origRectNeutral.top + 100, 'translate() on a neutral container');
//        resetContainer(true);

        // Translation pre-transformed container
        console.log(origRectTrans)
        transformer.translateX($container, 100);
        rect = getRect($container);
        console.log(rect)
        assert.ok(rect.left === origRectTrans.left + 100, 'translateX() on a pre-transformed container');
        resetContainer(true);

//        transformer.translateY($container, 100);
//        rect = getRect($container);
//        assert.ok(rect.top === origRectTrans.top + 100, 'translateY() on a pre-transformed container');
//        resetContainer(true);
//
//        transformer.translate($container, 100);
//        rect = getRect($container);
//        assert.ok(rect.left === origRectTrans.left + 100 && rect.top === origRectTrans.top + 100, 'translate() on a pre-transformed container');
//        resetContainer(true);

//        // Scaling
//        transformer.scaleX($container, 3);
//        rect = getRect($container);
//        assert.ok(rect.height === 100 && rect.width === 300, 'scaleX() on a neutral container');
//        resetContainer();
//
//        transformer.scaleY($container, 3);
//        rect = getRect($container);
//        assert.ok(rect.width === 100 && rect.height === 300, 'scaleY() on a neutral container');
//        resetContainer();
//
//        transformer.scale($container, 3);
//        rect = getRect($container);
//        assert.ok(rect.width === rect.height && rect.width === 300, 'scale() on a neutral container');
//
//        // 'true' adds a set of transformations to the container
//        resetContainer(true);
//
//        transformer.scaleX($container, 3);
//        rect = getRect($container);
//        assert.ok(rect.height === 100 && rect.width === 450, 'scaleX() on a pre-transformed container');
//        resetContainer(true);
//
//        transformer.scaleY($container, 3);
//        rect = getRect($container);
//        assert.ok(rect.width === 100 && rect.height === 450, 'scaleY() on a pre-transformed container');
//        resetContainer(true);
//
//        transformer.scale($container, 3);
//        rect = getRect($container);
//        assert.ok(rect.width === rect.height && rect.width === 450, 'scale() on a pre-transformed container');
//        resetContainer();
//
//
//        // Rotation
//        transformer.rotate($container, 45);
//        rect = getRect($container);
//        assert.ok(rect.height === rect.width && rect.height === 142, 'rotate() on a neutral container');
//        resetContainer(true);
//
//        transformer.rotate($container, 25);
//        rect = getRect($container);
//        assert.ok(rect.height === rect.width && rect.height === 142, 'rotate() on a pre-transformed container');
//        resetContainer();

//
//        // Skew
//        transformer.skew($container, 10);
//        transformer.skew($('#bar'), 10);
//        var bRect =getRect($('#bar'));
//        console.log(bRect)
//
//        rect = getRect($container);
//        console.log(rect)
//        assert.ok(rect.height === rect.width && rect.height === 142, 'skew() on a neutral container');
//        resetContainer(true);

//        transformer.skew($container, 25);
//        rect = getSize($container);
//        assert.ok(rect.height === rect.width && rect.height === 142, 'skew() on a pre-transformed container');

    });


});
