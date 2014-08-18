define(['jquery', 'lodash', 'lib/uuid', 'layout/actionBinding'], function($, _, uuid, binding){

    
    var actionManager = {

            _actions : {},

            lookup : function(){
                var self = this;
                $('.action-bar .action').each(function(){
        
                    var $this = $(this);
                    var id;
                    
                    //use the element id
                    if($this.attr('id')){
                        id = $this.attr('id');
                    } else {
                        //or generate one
                        do {
                            id = 'action-' + uuid(8, 16);
                        } while (self._actions[id]);

                        $this.attr('id', id);
                    }

                    self._actions[id] = {
                        name    : $this.attr('title'),
                        binding : $this.data('action'),
                        url     : $('a', $this).attr('href'),
                        context : $this.data('context'),
                        state : {
                            disabled    : $this.hasClass('disabled'),
                            hidden      : $this.hasClass('hidden')
                        }
                    };
                });
            },
    
            bind   : function(){
                var self = this;
                $('.action-bar .action').not('.hidden,.disabled').on('click', function(e){
                    e.preventDefault();
                    binding.exec(self._actions[$(this).attr('id')]);
                });
            }, 

            update : function(uri, classUri, acl){
                            
            }
    };
    
    return actionManager;
});
