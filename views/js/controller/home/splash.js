define(['controller/home/custom-scrollbar'], function(){
   var SplashScreen = {
      /**
       * Initialize the splash screen
       */
      init: function (){     
         /*
          * Unites subjects and groups into one wrapper
          */
         var subjectsWrapper = $('.subjects-wrapper:first');
         $('.arrow-3-wrapper:first').appendTo(subjectsWrapper);
         $('.groups-wrapper:first').children().appendTo(subjectsWrapper);
         $('.groups-wrapper:first').remove();
         
         /**
          * Initialize custom scrollbar for the description
          */
         $('.desc').customScrollbar({
            updateOnWindowResize: true,
            skin: 'gray-skin',
            hScroll: false,
         });
         
         /**
          * Open modal window immediately
          */
         $('#splash-screen').modal();

         this.initNav();
         this.initModulesNav();
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
         $('.module').not('.disabled').on('click', function(){
            var selectedEl = $(this),
                selectedModuleName = selectedEl.data('module-name');
                
            if(selectedEl.parent().attr('class')=='diagram'){
               var selectedClass = selectedEl.find('.icon').first().attr('class');
               $('.module-desc>span').attr({'class':selectedClass});
            }
            else{
               $('.module-desc>span').attr({'class':''});
            }

            $('.module').removeClass('active-module');
            selectedEl.addClass('active-module');

            $('.module-desc').hide();
            $("div[data-module='"+selectedModuleName+"']").show();
            $('.desc').customScrollbar('resize', true);
         });
      },
      
      /**
       * Close the splash screen
       */
      close: function(){
          $('#splash-screen').modal('closeModal');
      }
   };
   
   return SplashScreen;
});
