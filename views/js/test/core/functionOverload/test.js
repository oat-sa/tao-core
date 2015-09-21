define(['lodash', 'jquery', 'core/functionOverload'], function(_, $, functionOverload) {
    'use strict';

    QUnit.test('add method to the top level function', function(assert){
        var arg1 = 'one',
            arg2 = 'two',
            arg3 = 'three';

        // bind behaviour if one argument given
        functionOverload.addMethod(window, 'test', function (arg1) {
            return {num: 1, args: Array.prototype.slice.call(arguments)};
        });
        // bind behaviour if two arguments given
        functionOverload.addMethod(window, 'test', function (arg1, arg2) {
            return {num: 2, args: Array.prototype.slice.call(arguments)};
        });
        // bind behaviour if three arguments given
        functionOverload.addMethod(window, 'test', function (arg1, arg2, arg3) {
            return {num: 3, args: Array.prototype.slice.call(arguments)};
        });

        assert.deepEqual(
            test(arg1),
            {num: 1, args: [arg1]}
        );
        assert.deepEqual(
            test(arg1, arg2),
            {num: 2, args: [arg1, arg2]}
        );
        assert.deepEqual(
            test(arg1, arg2, arg3),
            {num: 3, args: [arg1, arg2, arg3]}
        );
    });

    QUnit.test('add method to the object method', function(assert){
        var arg1 = 'one',
            arg2 = 'two',
            arg3 = 'three';

        var testObject = {};

        // bind behaviour if one argument given
        functionOverload.addMethod(testObject, 'test', function (arg1) {
            return {num: 1, args: Array.prototype.slice.call(arguments)};
        });
        // bind behaviour if two arguments given
        functionOverload.addMethod(testObject, 'test', function (arg1, arg2) {
            return {num: 2, args: Array.prototype.slice.call(arguments)};
        });
        // bind behaviour if three arguments given
        functionOverload.addMethod(testObject, 'test', function (arg1, arg2, arg3) {
            return {num: 3, args: Array.prototype.slice.call(arguments)};
        });

        assert.deepEqual(
            testObject.test(arg1),
            {num: 1, args: [arg1]}
        );
        assert.deepEqual(
            testObject.test(arg1, arg2),
            {num: 2, args: [arg1, arg2]}
        );
        assert.deepEqual(
            testObject.test(arg1, arg2, arg3),
            {num: 3, args: [arg1, arg2, arg3]}
        );
    });
});
