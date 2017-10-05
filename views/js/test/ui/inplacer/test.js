define(['jquery', 'ui',  'ui/inplacer'], function($, ui){
    'use strict';

    QUnit.module('Inplacer Stand Alone Test');

    QUnit.test('plugin', function(assert){
        QUnit.expect(1);
        assert.ok(typeof $.fn.inplacer === 'function', 'The Inplacer plugin is registered');
    });

    QUnit.asyncTest('initialization', function(assert){
        QUnit.expect(5);

        var $container = $('#div-0');
        assert.ok($container.length === 1, 'Test the fixture is available');

        $container.on('create.inplacer', function(){
            assert.ok(typeof $container.data('ui.inplacer') === 'object', 'config object stored in data');
            assert.ok($container.hasClass('inplace'), 'has inplace class');
            assert.equal($container.siblings('#edit-me').length, 1, 'target created');
            QUnit.start();
        });

        assert.equal($container.siblings('#edit-me').length, 0, 'target not created yet');
        $container.inplacer({
            target : $('#edit-me')
        });
    });

    QUnit.asyncTest('edit and leave a <div>', function(assert){
        QUnit.expect(5);

        var $container = $('#div-0');
        assert.ok($container.length === 1, 'Test the fixture is available');

        $container.on('create.inplacer', function(){
            //click the editable to start editing mode
            $container.click();
        }).on('edit.inplacer', function(){
            assert.equal($container.find('textarea').length, 1, 'input in focus');

            //make some text change
            $container.find('textarea').val('AAA');

            //leave
            $container.find('textarea').blur();
        }).on('leave.inplacer', function(e, val){

            //check that the the container has been correctly updated
            assert.equal($container.find('textarea').length, 0, 'input is blurred');
            assert.equal(val, 'AAA', 'returned value is correct');
            assert.equal($container.html(), 'AAA', 'editable container has been updater');

            QUnit.start();
        }).inplacer({
            target : $('#edit-me')
        });
    });

    QUnit.asyncTest('edit and leave a <span>', function(assert){
        QUnit.expect(5);

        var $container = $('#span-0');
        assert.ok($container.length === 1, 'Test the fixture is available');

        $container.on('create.inplacer', function(){
            //click the editable to start editing mode
            $container.click();
        }).on('edit.inplacer', function(){
            assert.equal($container.find(':text').length, 1, 'input in focus');

            //make some text change
            $container.find(':text').val('AAA');

            //leave
            $container.find(':text').blur();
        }).on('leave.inplacer', function(e, val){

            //check that the the container has been correctly updated
            assert.equal($container.find(':text').length, 0, 'input is blurred');
            assert.equal(val, 'AAA', 'returned value is correct');
            assert.equal($container.html(), 'AAA', 'editable container has been updated');

            QUnit.start();
        }).inplacer({
            target : $('#edit-me')
        });
    });

    QUnit.asyncTest('destroy', function(assert){
        QUnit.expect(6);

        var $container = $('#span-0');
        assert.ok($container.length === 1, 'Test the fixture is available');

        $container.on('create.inplacer', function(){
            assert.ok(typeof $container.data('ui.inplacer') === 'object', 'config object stored in data');
            assert.ok($container.hasClass('inplace'), 'has inplace class');
            assert.equal($container.siblings('#edit-me').length, 1, 'target created');

            //test destroy method
            $container.inplacer('destroy');
        }).on('destroy.inplacer', function(){

            //check clean up
            assert.equal($container.data('ui.inplacer'), undefined, 'data object removed');
            assert.ok(!$container.hasClass('inplace'), 'inplace class removed');

            QUnit.start();
        }).inplacer({
            target : $('#edit-me')
        });
    });
});


