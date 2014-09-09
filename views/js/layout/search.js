define([
    'jquery',
    'i18n',
    'context'
],
    function(
        $,
        __,
        context
        ){


        var $container;

        /**
         * Retrieve instance of the container element
         *
         * @returns {*}
         */
        function getContainer() {
            if(!$container) {
                $container = $('.search-form > [data-purpose="search"]');
            }

            return $container;
        }


        /**
         * Load and beautify search form
         *
         * @param $searchForm
         */
        function init($searchForm) {

            // reduce to one element only
            $searchForm = $($searchForm.replace(/id=("|')[\w-]+("|')/g, ''));

            if(!$searchForm.length) {
                return;
            }

            var $toolBars      = $searchForm.find('.form-toolbar'),
                $formGroups    = $searchForm.find('.form-group'),
                $filters       = $formGroups.last(),
                $submitBtn     = $searchForm.find('.form-submitter'),
                $langSelector  = $searchForm.find('[name="lang"]'),
                $formContainer = $searchForm.find('.xhtml_form');

            // remove unwanted classes
            $formContainer.parent().removeClass(function(idx, className) {
                return className;
            });


            // remove first toolbar
            if($toolBars.length > 1) {
                $toolBars.first().remove();
            }

            // remove 'options' and 'filters'
            $searchForm.find('.form-group')[0].firstChild.remove();
            $filters[0].firstChild.remove();

            // add some explanatory text to lang selector
            $langSelector.find('option:first').text('-- ' + __('any') + ' --');
            $langSelector.val(context.locale);

            // add regular placeholder
            $filters.find('input[type="text"]').each(function() {
                var $parentDiv;
                if((/schema_[\d]+_label$/).test(this.name)) {
                    this.placeholder = __('You can use * as a wildcard');
                    $parentDiv = $(this).closest('div');
                    // remove fake placeholder
                    $parentDiv.prev().remove();
                    // remove 'original filename' when empty
                    if(!$.trim($parentDiv.next().find('span').last().html())) {
                        $parentDiv.next().remove();
                    }
                }
            });

            // turn submit button in standard design
            $submitBtn.addClass('btn-success small')
            $submitBtn.html('');
            $submitBtn.append($('<span>', {'class': 'icon-find'}));
            $submitBtn.append($('<span>' + __('Find') + '</span>'));

            getContainer().html($searchForm)

        }

        /**
         * show/hide search form
         */
        function toggle() {
            $('.search-form').slideToggle();
        }


        return {
            /**
             * Initialize post renderer
             */
            init : init,

            // show/hide search
            toggle : toggle,

            // access to container
            getContainer: getContainer
        };
    });


