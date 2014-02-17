define(['jquery', 'controller/home/custom-scrollbar'], function($){
    
   var SplashScreen = {
      /**
       * Initialize the splash screen
       */
      init: function (){
         //Url to redirect after closing
         this.redirectUrl = '';
         
         /**
          * Place lock icon for disabled modules
          */
         $('[data-module-name]' , '.diagram').each(function(){
             var $this = $(this);
             
             if($this.hasClass('disabled')){
                 $this.prepend('<span class="icon-lock"></span>');
             }
         });
         
         /**
          * Initialize custom scrollbar for the description
          */
         $('.desc').customScrollbar({
            updateOnWindowResize: true,
            skin: 'gray-skin',
            hScroll: false
         });
         
         /**
          * Open modal window immediately
          */
         $('#splash-screen').modal({disableOverlayClose: true});

         this.initNav();
         this.initModulesNav();
         this.initCloseButton();
      },
      
      /**
       * Initialize a listener for the navigation tab buttons
       */
      initNav: function(){
         $('.modal-nav a').on('click', function(){
            var selectedEl = $(this),
                selectedPanelId = selectedEl.data('panel');

            $('.modal-nav li').removeClass('active');
            $("a[data-panel='"+selectedPanelId+"']").parent().addClass('active');	

            $('.panels').hide();
            $("div[data-panel-id='"+selectedPanelId+"']").show();
         });
      },
      
      /**
       * Initialize a listener for the modules buttons
       */
      initModulesNav: function(){
         var splashObj = this;
         
         $('[data-module-name]').not('.disabled').on('click', function(){
            var selectedEl = $(this),
                selectedModuleName = selectedEl.data('module-name');
                splashObj.redirectUrl = selectedEl.data('url');
                
            $('#splash-close-btn').removeAttr('disabled');
                
            if(!selectedEl.hasClass('new-module')){
               var selectedClass = selectedEl.hasClass('groups')?$('.test-takers').find('span').first().attr('class'):selectedEl.find('span').first().attr('class');
               $('.module-desc>span').attr({'class':selectedClass});
            }
            else{
               $('.module-desc>span').attr({'class':''});
            }
            
            $('[data-module-name]').removeClass('active');
            $('.module-desc').hide();
            
            selectedEl.addClass('active');
            $("div[data-module='"+selectedModuleName+"']").show();
            
            $('.desc').customScrollbar('resize', true);
         });
      },
      
      /**
       * Initialize a listener for the close button
       */
      initCloseButton: function() {
          var splashObj = this;
      
          $('#splash-close-btn').on('click', function(){
              
              //if the checkbox is checked, then add and set the additional GET parameter 'nosplash'
              if($('#nosplash').prop('checked')){
                  splashObj.redirectUrl += '&nosplash=true';
              }
              
              splashObj.closeSplash(splashObj.redirectUrl);
          });
      },
      
      /**
       * Close the splash screen and redirect to selected module
       * @param {string} url
       */
      closeSplash: function(url){
          window.location = url;
      }
   };
   
   return SplashScreen;
});
