define(['jquery', 'ui/contextualPopup'], function($, contextualPopup){

    QUnit.test('init dialog', function(){

        QUnit.expect(0);
        
        var $container = $('#main-container');
        var popup1 = contextualPopup($('#center1'), $container, {content : 'content 1'});
        var popup2 = contextualPopup($('#center2'), $container, {content : 'content 2'});
        var popup3 = contextualPopup($('#center3'), $container, {content : 'content 3'});
        
        popup1.show();
        popup2.show();
        popup3.show();
        return;
        
        $container.on('selected.element-selector', function(e, qtiClass, $trigger){
            if(qtiClass === 'hotspotInteraction'){
                QUnit.assert.ok($('#center1 .element-list li[data-qti-class=hotspotInteraction]').hasClass('active'),'hotspotInteraction selected');
            }else if(qtiClass === 'choiceInteraction'){
                QUnit.assert.ok($('#center1 .element-list li[data-qti-class=choiceInteraction]').hasClass('active'),'choiceInteraction selected');
                QUnit.assert.ok(!$('#center1 .element-list li[data-qti-class=hotspotInteraction]').hasClass('active'),'hotspotInteraction unselected');
            }else if(qtiClass === '_container'){
                QUnit.assert.ok($('#center1 .element-list li[data-qti-class=_container]').hasClass('active'),'_container selected');
                QUnit.assert.ok(!$('#center1 .element-list li[data-qti-class=choiceInteraction]').hasClass('active'),'choiceInteraction unselected');
            }
        });
        
        $container.find('#center1 .element-list li[data-qti-class=hotspotInteraction]').click();
        selector.activateElement($('#center1'), 'choiceInteraction');
        selector.activateElement($('#center1'), '_container');
        
        selector.activatePanel($container, 'Text Block');
    });
});