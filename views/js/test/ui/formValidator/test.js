define(['lodash', 'jquery', 'ui/formValidator/formValidator'], function(_, $, FormValidator) {
    'use strict';

    var validData = {
        '#field_1' : '1',
        '#field_2' : 'str',
        '#field_3' : 'foo bar BAZ',
        '#field_4' : 'http://code.jquery.com/jquery.js',
        '#field_5' : '12345'
    };

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

    QUnit.test('prevent submit form', function(assert) {
        QUnit.expect(2);

        var validator = new FormValidator({
            container : $('#form_1'),
            event : 'change',
            selector : '[data-validate]:not(.ignore)'
        });

        $('#form_1').on('submit', function () {
            assert.ok(validator.validate());
            return false;
        });

        bindValues(validData, $('#form_1'));
        $('#field_1').val('non-numeric');
        assert.ok(validator.validate());
        //$('#form_1').submit();

        //should not be submitted.
        //$('#form_1').submit();
    });

    /*QUnit.test('Highlight field', function(assert) {
        //add error class
        //add message
    });

    QUnit.test('Unhighlight field', function(assert) {
        //remove error class
        //remove message
    });*/



});
