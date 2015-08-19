define([
    'jquery',
    'ui/scaler'
], function($, scaler){


    QUnit.module('Scaler');

    QUnit.test('Module', function(assert){
        assert.ok(typeof scaler !== 'undefined', 'The module exports something');
    });

    QUnit.test('API', function(assert){

        assert.ok(typeof scaler === 'object', 'Scaler returns an object');
        assert.ok(typeof scaler.scale === 'function', 'The loader exposes a method scale()');
        assert.ok(typeof scaler.reset === 'function', 'The loader exposes a method reset()');

    });

    QUnit.test('Scaling and resetting', function(assert){
        var $container = $('#scale-container');
        assert.equal($container.length, 1, 'The container exists');

        scaler.scale($container, 2);
        assert.equal($container[0].getBoundingClientRect().width, 200, 'x-scaling works correctly');
        assert.equal($container[0].getBoundingClientRect().height, 200, 'y-scaling works correctly');

        scaler.scale($container, -5);
        assert.equal($container[0].getBoundingClientRect().width, 0, 'Factors below 0 default to factor 1');

        scaler.reset($container);
        assert.equal($container[0].getBoundingClientRect().width, 100, 'Container resets to its original size');
    });

});
