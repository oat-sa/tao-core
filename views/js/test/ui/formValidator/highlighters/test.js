define(['lodash', 'jquery', 'ui/formValidator/highlighters/highlighter'], function(_, $, Highlighter) {
    'use strict';

    var highlighter,
        message;

    QUnit.module("Message highlighter", {
        setup: function() {
            highlighter = new Highlighter({
                type : 'message',
                errorClass : 'testErrorClass',
                errorMessageClass : 'testErrorMessageClass'
            });
            message = 'highlight (message)';
        },
        teardown: function() {
            highlighter.destroy($('#field_1'));
        }
    });
    QUnit.test('highlight (message)', function(assert) {
        highlighter.highlight($('#field_1'), message);
        assert.equal($('#field_1').next('.testErrorMessageClass').length, 1, 'Highlighted');
        assert.equal($('#field_1').next('.testErrorMessageClass').text(), message, 'Message is correct');
        assert.ok($('#field_1').hasClass('testErrorClass'), 'Field has error class');

        highlighter.highlight($('#field_1'), message);
        assert.equal($('#field_1').next('.testErrorMessageClass').length, 1, 'Highlighted (message is not duplicated)');
    });
    QUnit.test('uhighlight (message)', function(assert) {
        highlighter.highlight($('#field_1'), message);
        highlighter.unhighlight($('#field_1'));
        assert.equal($('#field_1').next('.testErrorMessageClass').length, 0, 'Unhighlighted');
        assert.ok(!$('#field_1').hasClass('testErrorClass'), 'Field has no error class');
    });


    QUnit.module("Qtip highlighter", {
        setup: function() {
            highlighter = new Highlighter({
                type : 'qtip',
                errorClass : 'testErrorClass',
                qtip : {
                    show: {
                        delay: 0
                    },
                    hide: {
                        delay: 0
                    }
                }
            });
            message = 'highlight (tooltip)';
        },
        teardown: function() {
            highlighter.destroy($('#field_1'));
        }
    });
    QUnit.test('highlight (tooltip)', function(assert) {
        highlighter.highlight($('#field_1'), message);

        assert.ok($('#field_1').hasClass('testErrorClass'), 'Field has error class');
        assert.equal($('.qtip-content').length, 1, 'Highlighted (tooltip is rendered)');
        assert.equal($('.qtip-content').text(), message, 'Message is correct');
    });

    QUnit.test('unhighlight (tooltip)', function(assert) {
        highlighter.highlight($('#field_1'), message);
        highlighter.unhighlight($('#field_1'));
        QUnit.stop();
        setTimeout(function() {
            assert.ok(!$('#field_1').hasClass('testErrorClass'), 'Field has no error class');
            assert.equal($('.qtip-content').length, 0, 'Unhighlighted tooltip is removed');
            QUnit.start();
        }, 100);
    });
});
