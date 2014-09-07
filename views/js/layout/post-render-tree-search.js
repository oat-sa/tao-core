define([
    'jquery',
    'i18n'
],
    function($, __){


        /**
         * Make search form look nice
         */
        function init() {
            var $container = $('.search-form');

            if(!$container.length) {
                return;
            }

            var $trigger = $('.search-trigger'),
                $toolBars  = $container.find('.form-toolbar'),
                $formGroups = $container.find('.form-group'),
                $filters = $formGroups.last(),
                $submitBtn = $container.find('.form-submitter'),
                $langSelector = $container.find('[name="lang"]'),
                titles = [
                    $container.find('.form-group')[0].firstChild,
                    $filters[0].firstChild
                ],
                iTit = titles.length;

            // remove unwanted classes
            $container.find('#form-container').removeClass(function(idx, className) {
                return className;
            });

            // remove unwanted ids to avoid conflicts
            $container.find('[id]').each(function() {
                this.removeAttribute('id');
            });

            // remove first toolbar
            if($toolBars.length > 1) {
                $toolBars.first().remove();
            }

            // pimp titles
            while(iTit--) {
                if(titles[iTit].nodeType !== Node.TEXT_NODE){
                    continue;
                }
                $(titles[iTit]).replaceWith($('<h2>', { text: $.trim(titles[iTit].textContent)}))
            }

            // add some explanatory text to lang selector
            $langSelector.find('option:first').text('-- ' + __('any') + ' --');

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

            $trigger.on('click', function() {
                $container.slideToggle();
            })
        }


        return {
            /**
             * Initialize post renderer
             */
            init : init
        };
    });


