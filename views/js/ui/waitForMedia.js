define(['jquery'], function($){

    var _ns = '.wait';
    
    /**
     * Register a plugin that enable waiting for all media being loaded
     * 
     * @fires loaded.wait
     * @fires all-loaded.wait
     * @param {Function} allLoadedCallback - (optional) callback to be executed when all media has been loaded
     * @returns {jQueryElement} for chaingin
     */
    $.fn.waitForMedia = function(allLoadedCallback){

        return this.each(function(){
            
            var $container = $(this);
            var $img = $container.find('img');
            var count = $img.length;
            var loaded = 0;
            
            /**
             * The function to be executed whenever an image is considered loaded
             */
            function imageLoaded(){
                $(this).trigger('loaded' + _ns);
                $(this).off('load'+_ns).off('error'+_ns);
                loaded ++;
                if(loaded === count){
                    $container.trigger('all-loaded' + _ns);
                    if(typeof allLoadedCallback === 'function'){
                        allLoadedCallback.call($container[0]);
                    }
                }
            }
            
            $img.each(function(){
                if(this.complete){
                    //the image is already loaded by the browser
                    imageLoaded.call(this);
                }else{
                    //the image is not yet loaded : add "load" listener
                    $(this).on('load'+_ns+' error'+_ns, imageLoaded);
                }
            });

        });

    };

});