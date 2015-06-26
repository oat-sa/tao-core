define(['jquery', 'ui/contextualPopup'], function($, contextualPopup){

    'use strict';

    QUnit.test('init dialog', function(){

        var $container = $('#main-container');
        var popup1 = contextualPopup($('#center1'), $container, {content : 'content 1'});
        QUnit.assert.equal(popup1.getPopup().length, 1, 'popup created');
        QUnit.assert.ok(popup1.getPopup().hasClass('bottom'), 'positioned on the bottom');
        QUnit.assert.equal(popup1.getPopup().find('.done').length, 0, 'done button absent');
        QUnit.assert.equal(popup1.getPopup().find('.cancel').length, 0, 'cancel button absent');

        var $content2 = $('<ul><li>element 1</li><li>element 2</li><li>element 3</li></ul');
        var popup2 = contextualPopup($('#center2'), $container, {content : $content2});
        QUnit.assert.equal(popup2.getPopup().length, 1, 'popup created');
        QUnit.assert.ok(popup2.getPopup().hasClass('bottom'), 'positioned on the bottom');
        QUnit.assert.equal(popup2.getPopup().find('.done').length, 0, 'done button absent');
        QUnit.assert.equal(popup2.getPopup().find('.cancel').length, 0, 'cancel button absent');
        QUnit.assert.equal(popup2.getPopup().find('li').length, 3, 'html content');

        var popup3 = contextualPopup($('#center3'), $container, {content : 'content 3', controls : {done : true, cancel : true}});
        QUnit.assert.equal(popup3.getPopup().length, 1, 'popup created');
        QUnit.assert.ok(popup3.getPopup().hasClass('bottom'), 'positioned on the bottom');
        QUnit.assert.equal(popup3.getPopup().find('.done').length, 1, 'done button added');
        QUnit.assert.equal(popup3.getPopup().find('.cancel').length, 1, 'cancel button added');
        
        var popup4 = contextualPopup($('#center4'), $container, {content: 'content 4', position:'top'});
        QUnit.assert.equal(popup4.getPopup().length, 1, 'popup created');
        QUnit.assert.ok(popup4.getPopup().hasClass('top'), 'positioned on top');
        
        popup1.show();
        popup2.show();
        popup3.show();
        popup4.show();
        
    });
});