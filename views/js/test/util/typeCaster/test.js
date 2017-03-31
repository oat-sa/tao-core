define([
    'jquery',
    'util/typeCaster'
], function($, typeCaster){
    'use strict';

    var strToBoolStringData,
        strToBoolBooleanData,
        strToBoolOtherData;

    QUnit.module('typeCaster');

    QUnit.test('module', function(assert){
        QUnit.expect(1);

        assert.ok(typeof typeCaster === 'object', 'The module expose an object');
    });

    QUnit.module('strToBool()');

    strToBoolStringData = [
        { value: 'true',    result: true,   title: '"true" => true' },
        { value: 'TRUE',    result: true,   title: '"TRUE" => true' },
        { value: 'trUe',    result: true,   title: '"trUe" => true' },
        { value: '',        result: false,  title: 'empty string => false' },
        { value: 'false',   result: false,  title: '"false" => false' },
        { value: 'false',   result: false,  title: '"FALSE" => false' },
        { value: '1',       result: false,  title: '"1" => false' },
        { value: '0',       result: false,  title: '"0" => false' }
    ];

    QUnit
        .cases(strToBoolStringData)
        .test('strToBool() with String input', function(data, assert) {
            assert.equal(typeCaster.strToBool(data.value), data.result, 'correct value returned with no default value');
            assert.equal(typeCaster.strToBool(data.value, true), data.result, 'correct value returned with default value === true');
            assert.equal(typeCaster.strToBool(data.value, false), data.result, 'correct value returned with default value === false');
        });

    strToBoolBooleanData = [
        { value: true,    title: 'true => true' },
        { value: false,   title: 'false => false' }
    ];

    QUnit
        .cases(strToBoolBooleanData)
        .test('strToBool() with Boolean input', function(data, assert) {
            assert.equal(typeCaster.strToBool(data.value), data.value, 'correct value returned with no default value');
            assert.equal(typeCaster.strToBool(data.value, true), data.value, 'correct value returned with default value === true');
            assert.equal(typeCaster.strToBool(data.value, false), data.value, 'correct value returned with default value === false');
        });

    strToBoolOtherData = [
        {                   result: false,  title: 'undefined => false or default value' },
        { value: null,      result: false,  title: 'null => false or default value' },
        { value: {},        result: false,  title: '{} => false or default value' },
        { value: 1,         result: false,  title: '1 => false or default value' },
        { value: 0,         result: false,  title: '0 => false or default value' },
        { value: [true],    result: false,  title: '[true] => false or default value' }
    ];

    QUnit
        .cases(strToBoolOtherData)
        .test('strToBool() with other type of data', function(data, assert) {
            assert.equal(typeCaster.strToBool(data.value), false, 'non string value with no defaultValue returns false');
            assert.equal(typeCaster.strToBool(data.value, true), true, 'non string value with defaultValue === true returns true');
            assert.equal(typeCaster.strToBool(data.value, false), false, 'non string value with no defaultValue === false returns false');
        });

});