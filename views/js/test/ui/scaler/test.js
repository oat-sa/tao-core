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
        assert.ok(typeof scaler.toggle === 'function', 'The loader exposes a method toggle()');

    });

    QUnit.test('Scaling', function(assert){
        var $container = $('#scale-container');
        assert.equal($container.length, 1, 'The container exists');

        scaler.scale($container, 3);
        assert.equal($container[0].getBoundingClientRect().width, 200, 'X-scaling works correctly');
        assert.equal($container[0].getBoundingClientRect().height, 200, 'Y-scaling works correctly');

        scaler.scale($container, -5);
        assert.equal($container[0].getBoundingClientRect().width, 100, 'Negative factors default to 1');

        scaler.reset($container);
        assert.equal($container[0].getBoundingClientRect().width, 100, 'Container resets to its original size');

        scaler.toggle($container, 2);
        assert.equal($container[0].getBoundingClientRect().width, 200, 'Toggle transforms when container has original size');

        scaler.toggle($container);
        assert.equal($container[0].getBoundingClientRect().width, 100, 'Toggle resets when the container had been transformed before');
    });

});
