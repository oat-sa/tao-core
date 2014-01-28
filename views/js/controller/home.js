define(['jquery'], function ($) {
    'use strict';
    
    
        $(".extension-nav").each(function(){
                var url = $(this).attr('href');
                $(this).parent("div.home-box").click(function(){
                        window.location = url;
                });
        });
        $('.home-box').mouseover(function(){
                if($('.extension-desc', this).css('display') === 'none') {
                        $('.extension-desc', this).show();
                }
        }).mouseout(function(){
                if($('.extension-desc', this).css('display') !== 'none') {
                        $('.extension-desc', this).hide();
                }
        });
        
});