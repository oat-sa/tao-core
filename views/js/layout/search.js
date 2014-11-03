define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'context',
    'layout/section'
],
function($, _, __, module, context, section){

    var changeFormLayout = function changeFormLayout($form){

        var $toolBars      = $form.find('.form-toolbar');
        var $formGroups    = $form.find('.form-group');
        var $filters       = $formGroups.last();
        var $langSelector  = $form.find('[name="lang"]');
        var $formContainer = $form.find('.xhtml_form');
        var $formTitle     = $form.find('h2');

        // remove unwanted classes
        $formContainer.parent().removeClass(function(idx, className) {
            return className;
        });

        // remove first toolbar
        if($toolBars.length > 1) {
            $toolBars.first().remove();
        }


        // remove 'options', 'filters' and headings
        $form.find('del').remove();
        $formTitle.remove();


        // select current locale
        if(!$langSelector.val()){
            $langSelector.val(context.locale);
        }

        // add regular placeholder
        $filters.find('input[type="text"]').each(function() {
            var $parentDiv;
            if((/schema_[\d]+_label$/).test(this.name)) {
                this.placeholder = __('You can use * as a wildcard');
                $parentDiv = $(this).closest('div');
                // remove 'original filename when empty
                if(!$.trim($parentDiv.next().find('span').last().html())) {
                    $parentDiv.next().remove();
                }
                $parentDiv.prependTo($form.find('.form-group:first > div'));
            }
        });
    };

    return {

        /**
         * Initialize post renderer
         */
        init : function init($container, searchForm){
            var self = this;
            var conf = module.config();
            var $searchForm;
            if(searchForm){        
    
                // build jquery obj, make ids unique
                $searchForm = $(searchForm.replace(/(for|id)=("|')/g, '$1=$2search_field_'));

                //tweaks form layout 
                changeFormLayout($searchForm);

                //re-init itself on submit
                
                _.defer(function(){     //defer tp bind after the uiForm stuffs
                    $('.form-submitter', $searchForm)
                        .off('click')
                        .on('click', function(e){
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            var $formElt = $('form', $searchForm);
                            $.ajax({
                                url : $formElt.attr('action'),
                                type : 'POST',
                                data : $formElt.serializeArray()
                            }).done(function(response){
                                self.init($container, response);
                            }); 
                        });
                });
                $container.html($searchForm);

                if(conf.result && conf.result.model){
                   console.log({
                        url: conf.result.url,
                        model : _.values(conf.result.model),
                        querytype : 'POST',
                        params : {
                            filters : conf.result.filters,
                            params : conf.result.params
                        },
                    });
           
                }
            }
        }
    };
});
