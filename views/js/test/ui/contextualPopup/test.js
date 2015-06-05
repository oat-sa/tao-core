define(['jquery', 'ui/contextualPopup'], function($, contextualPopup){
    
    'use strict';
    
    QUnit.test('init dialog', function(){

        var $container = $('#main-container');
        var popup1 = contextualPopup($('#center1'), $container, {content : 'content 1'});
        QUnit.assert.equal(popup1.getPopup().find('.done').length, 0, 'done button absent');
        QUnit.assert.equal(popup1.getPopup().find('.cancel').length, 0, 'cancel button absent');

        var $content2 = $('<ul><li>element 1</li><li>element 2</li><li>element 3</li></ul');
        var popup2 = contextualPopup($('#center2'), $container, {content : $content2});
        QUnit.assert.equal(popup2.getPopup().find('.done').length, 0, 'done button absent');
        QUnit.assert.equal(popup2.getPopup().find('.cancel').length, 0, 'cancel button absent');
        QUnit.assert.equal(popup2.getPopup().find('li').length, 3, 'html content');

        var popup3 = contextualPopup($('#center3'), $container, {content : 'content 3', controls : true});
        QUnit.assert.equal(popup3.getPopup().find('.done').length, 1, 'done button added');
        QUnit.assert.equal(popup3.getPopup().find('.cancel').length, 1, 'cancel button added');

        popup1.show();
        popup2.show();
        popup3.show();
    });
});