define(['jquery', 'ui', 'ui/modal'], function($, ui, modal){
    
    
    module('Modal Stand Alone Test');
   
    test('plugin', function(){
       expect(1);
       ok(typeof $.fn.modal === 'function', 'The Modal plugin is registered');
    });
   
    asyncTest('Initialization', function(){
        expect(4);
        
        var $modal = $('#modal-test');
        ok($modal.length === 1, 'Test the fixture is available');
        
        
        $modal.on('create.modal', function(){
            
            var modalCloseId = $modal.data('ui.modal').modalClose,
                modalOverlayId = $modal.data('ui.modal').modalOverlay,
                $modalClose = $('#'+modalCloseId),
                $modalOverlay = $('#'+modalOverlayId);
                
            ok($modalClose.length === 1, 'Close button is available');
            
            ok($modalOverlay.length === 1, 'Overlay is available');            
            
            ok(typeof $modal.data('ui.modal') === 'object', 'The element is runing the plugin');
            start();
        });
        $modal.modal();
    });
    
    asyncTest('Closing', function(){
        expect(2);
        
        var $modal = $('#modal-test');
        ok($modal.length === 1, 'Test the fixture is available');
        
        $modal.on('create.modal', function(){
            var modalCloseId = $modal.data('ui.modal').modalClose,
                $modalClose = $('#'+modalCloseId);
            $modalClose.trigger('click');
        });
        
        $modal.on('closed.modal', function(){
            ok(true, 'Close event was triggered');
            start();
        });
        $modal.modal();
    });
});


