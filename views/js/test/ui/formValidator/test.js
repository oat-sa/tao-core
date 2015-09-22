define(['lodash', 'jquery', 'ui/formValidator/formValidator'], function(_, $, FormValidator) {
    'use strict';

    var validData = {
            '#field_1' : '1',
            '#field_2' : 'str',
            '#field_3' : 'foo bar BAZ',
            '#field_4' : 'http://code.jquery.com/jquery.js',
            '#field_5' : 'qwert',
            '#field_7' : '12345'
        },
        invalidData = {
            '#field_1' : 'a',
            '#field_2' : '',
            '#field_3' : 'invalid',
            '#field_4' : 'http://wrongpath',
            '#field_5' : 'a',
            '#field_7' : 'b'
        },
        fieldSelector = '[data-validate]:not(.ignore)';

    function bindValues(values, $container) {
        var $elt;
        _.forEach(values, function (value, selector) {
            if ($container !== undefined && $container.length) {
                $elt = $container.find(selector);
            } else {
                $elt = $(selector);
            }
            if ($elt.length) {
                $elt.val(value);
            }
        });
    }

    QUnit.test('validate', function(assert) {
        QUnit.expect(2);

        var validator = new FormValidator({
            container : $('#form_1'),
            event : 'change',
            selector : fieldSelector
        });

        bindValues(invalidData, $('#form_1'));
        assert.ok(!validator.validate(), 'Form is not valid');


        bindValues(validData, $('#form_1'));
        assert.ok(validator.validate(), 'Form is valid');
    });

    QUnit.test('Highlight/unhighlight fields', function(assert) {
        var validator = new FormValidator({
            container : $('#form_1'),
            event : 'change',
            selector : fieldSelector
        });

        bindValues(invalidData, $('#form_1'));
        validator.validate();
        assert.equal($('#form_1').find('.error').length, $(fieldSelector).length, 'Fields highlighted');
        assert.equal($('#form_1').find('.validate-error').length, $(fieldSelector).length, 'Error messages rendered');

        bindValues(validData, $('#form_1'));
        validator.validate();
        assert.equal($('#form_1').find('.error').length, 0, 'Fields unhighlighted');
        assert.equal($('#form_1').find('.validate-error').length, 0, 'Error messages removed');
    });

    QUnit.test('getState', function(assert) {
        var validator = new FormValidator({
            container : $('#form_1'),
            event : 'change',
            selector : fieldSelector
        });

        bindValues(invalidData, $('#form_1'));
        validator.validate();
        assert.ok(validator.getState().valid === false);
        assert.ok(validator.getState().errors.length > 0);
        assert.ok(validator.getState().errors[0].field.length === 1, 'Error filed object is represented in the report');
        assert.ok(!!validator.getState().errors[0].message, 'Error message is represented in the report');
        assert.ok(!!validator.getState().errors[0].validator, 'Validator name is represented in the report');


        bindValues(validData, $('#form_1'));
        validator.validate();
        assert.ok(validator.getState().valid);
        assert.ok(validator.getState().errors.length === 0);
    });
});
