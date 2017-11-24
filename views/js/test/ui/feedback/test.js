define(['jquery', 'ui/feedback'], function($, feedback){
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof feedback, 'function', "The feedback module exposes a function");
        assert.equal(typeof feedback(), 'object', "The feedback factory produces an object");
        assert.notStrictEqual(feedback(), feedback(), "The feedback factory provides a different object on each call");
    });

    QUnit.cases([
        { name : 'init',         title : 'init' },
        { name : 'destroy',      title : 'destroy' },
        { name : 'render',       title : 'render' },
        { name : 'show',         title : 'show' },
        { name : 'hide',         title : 'hide' },
        { name : 'enable',       title : 'enable' },
        { name : 'disable',      title : 'disable' },
        { name : 'is',           title : 'is' },
        { name : 'setState',     title : 'setState' },
        { name : 'getContainer', title : 'getContainer' },
        { name : 'getElement',   title : 'getElement' },
        { name : 'getTemplate',  title : 'getTemplate' },
        { name : 'setTemplate',  title : 'setTemplate' }
    ])
    .test('component ', function(data, assert) {
        var instance = feedback();
        assert.equal(typeof instance[data.name], 'function', 'The feedback instance exposes a "' + data.title + '" function');
    });

    QUnit.cases([
        { name : 'on',      title : 'on' },
        { name : 'off',     title : 'off' },
        { name : 'trigger', title : 'trigger' }
    ])
    .test('eventifier ', function(data, assert) {
        var instance = feedback();
        assert.equal(typeof instance[data.name], 'function', 'The feedback instance exposes a "' + data.title + '" function');
    });

    QUnit.cases([
        { name : 'message', title : 'message' },
        { name : 'info',    title : 'info' },
        { name : 'success', title : 'success' },
        { name : 'warning', title : 'warning' },
        { name : 'danger',  title : 'danger' },
        { name : 'error',   title : 'error' },
        { name : 'open',    title : 'open' },
        { name : 'close',   title : 'close' },
        { name : 'display', title : 'display' },
    ])
    .test('spec ', function(data, assert) {
        var instance = feedback();
        assert.equal(typeof instance[data.name], 'function', 'The feedback instance exposes a "' + data.title + '" function');
    });

    QUnit.test('wrong container', function(assert){
        QUnit.expect(1);

        $('#feedback-box').remove();

        assert.throws(function(){
            feedback();
        }, TypeError, 'An exception should be thrown if the container is not an existing element');
    });


    QUnit.module('Behavior');

    QUnit.asyncTest('DOM rendering', function(assert) {
        var $container = $('#feedback-box');

        QUnit.expect(8);

        feedback( $container )
            .on('render', function(){
                var $element = $('.feedback', $container);

                assert.equal($element.length, 1, 'The container has the component root element');
                assert.ok($element.hasClass('rendered'), 'The component root element has the rendered class');
                assert.ok($element.hasClass('feedback-info'), 'The component root element has the correct level class');

                assert.equal($('.icon-info', $element).length, 1, 'The component has the correct level icon');
                assert.equal($('[data-close]', $element).length, 1, 'The component has the closer');
                assert.equal($element.children('div').length, 1, 'The component has the message box');
                assert.equal($element.children('div').text().trim(), 'foo', 'The component has the correct message');

                assert.deepEqual($element[0], this.getElement()[0], 'The element is the one bound to the component');

                QUnit.start();
            })
            .info('foo');
    });

    QUnit.asyncTest('mounting lifecycle', function(assert) {
        var $container = $('#feedback-box');

        QUnit.expect(2);

        feedback( $container )
            .on('render', function(){
                assert.ok(this.is('rendered'), 'The component is rendered');
                assert.equal($('.feedback', $container).length, 1, 'The component is  appended');

                this.destroy();
            })
            .on('destroy', function(){
                QUnit.start();
            })
            .success('foo');
    });

    QUnit.cases([{
        title : 'basic info',
        level : 'info',
        message : 'foo',
        params : [],
        options : {},
        expected : {
            popup : true,
            message : 'foo'
        }
    }, {
        title : 'parameterized success',
        level : 'success',
        message : 'Greeting from %s, today you are %d',
        params : ['OAT S.A.', 33],
        options : {},
        expected : {
            popup : true,
            message : 'Greeting from OAT S.A., today you are 33'
        }
    }, {
        title : 'parameterized HTML danger',
        level : 'success',
        message : 'You are going to <strong>delete %d <em>%s </em></strong>',
        params : [5, 'test takers'],
        options : {
            encodeHtml : false
        },
        expected : {
            popup : true,
            message : 'You are going to <strong>delete 5 <em>test takers </em></strong>'
        }
    }, {
        title : 'inline encoded HTML error',
        level : 'success',
        message : '<i>error</i>',
        params : [],
        options : {
            popup : false
        },
        expected : {
            popup : false,
            message : '&lt;i&gt;error&lt;/i&gt;'
        }
    }, {
        title : 'very long warning',
        level : 'warning',
        message : 'It seems you suffer from hippopotomonstrosesquipedaliophobia',
        params : [],
        options : {
            wrapLongWordsAfter : 20
        },
        expected : {
            popup : true,
            message : 'It seems you suffer from hippopotomonstrosesq uipedaliophobia'
        }
    }, {
        title : 'very long warning with a dot at threshold position',
        level : 'warning',
        message : 'It seems you suffer from hippopotomonstrosesq.uipedaliophobia',
        params : [],
        options : {
            wrapLongWordsAfter : 20
        },
        expected : {
            popup : true,
            message : 'It seems you suffer from hippopotomonstrosesq. uipedaliophobia'
        }
    }])
    .asyncTest('message ', function(data, assert) {
        QUnit.expect(5);

        feedback( )
            .on('render', function(){
                var $element = this.getElement();
                assert.ok(this.is('rendered'), 'The component is rendered');
                assert.ok($element.hasClass('feedback-' + data.level), 'The component root element has the correct level class');
                assert.equal($('.icon-' + data.level, $element).length, 1, 'The component has the correct level icon');
                assert.equal($($element).children('div').html().trim(), data.expected.message.trim(), 'The component has the correct message');
                assert.equal($element.hasClass('popup'), data.expected.popup, 'The component has the correct popup state');

                QUnit.start();
            })
            .message(data.level, data.message, data.params, data.options)
            .open();
    });

    QUnit.asyncTest('timeout close', function(assert){
        var $container = $('#feedback-box');

        QUnit.expect(5);

        feedback($container, {
            timeout : 500
        })
        .on('render', function(){
            assert.ok(this.is('rendered'), 'The component is rendered');
            assert.equal($('.feedback', $container).length, 1, 'There is 1 feedback');
        })
        .on('destroy', function(){
            assert.ok(true, 'Destroy is called');
        })
        .danger('foo');

        setTimeout(function(){
            assert.equal($('.feedback', $container).length, 1, 'There is 1 feedback');
        }, 450);
        setTimeout(function(){
            assert.equal($('.feedback', $container).length, 0, 'There are no feedbacks anymore');
            QUnit.start();
        }, 600);
    });

    QUnit.asyncTest('specialized timeout', function(assert){
        var $container = $('#feedback-box');

        QUnit.expect(5);

        feedback($container, {
            timeout : {
                warning: 1000
            }
        })
        .on('render', function(){
            assert.ok(this.is('rendered'), 'The component is rendered');
            assert.equal($('.feedback', $container).length, 1, 'There is 1 feedback');
        })
        .on('destroy', function(){
            assert.ok(true, 'Destroy is called');
        })
        .warning('foo');

        setTimeout(function(){
            assert.equal($('.feedback', $container).length, 1, 'There is 1 feedback');
        }, 950);
        setTimeout(function(){
            assert.equal($('.feedback', $container).length, 0, 'There are no feedbacks anymore');
            QUnit.start();
        }, 1050);
    });

    QUnit.asyncTest('close button', function(assert){
        var $container = $('#feedback-box');

        QUnit.expect(2);

        feedback($container, {
            timeout : 500
        })
        .on('render', function(){
            var $element = this.getElement();
            var $closer   = $('[data-close]', $element);
            assert.equal($closer.length, 1, 'The closer is attached');

            $closer.trigger('click');
        })
        .on('destroy', function(){
            assert.ok(true, 'Destroy is called');
            QUnit.start();
        })
        .error('foo');
    });

    QUnit.asyncTest('only one', function(assert){
        var $container = $('#feedback-box');
        var fb1, fb2;

        QUnit.expect(5);

        assert.equal($('.feedback', $container).length, 0, 'The contaier has no feedback');

        fb1 = feedback($container)
            .on('render', function(){
                assert.equal($('.feedback', $container).length, 1, 'The contaier has no feedback');
                assert.ok($('.feedback', $container).hasClass('feedback-error'), 'The only feedback is an error');
                fb2.danger('bar', { timeout: 1000 });
            });

        fb2 = feedback($container)
            .on('render', function(){
                assert.equal($('.feedback', $container).length, 1, 'The contaier has no feedback');
                assert.ok($('.feedback', $container).hasClass('feedback-danger'), 'The only feedback is an danger');
                QUnit.start();
            });

        fb1.error('foo', { timeout: 2000 });
    });
});
