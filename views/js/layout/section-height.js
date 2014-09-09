/**
 * @author Dieter Raber <dieter@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'jquery.cookie'
],
    function($, _){


        /**
         * Bar with the tree actions (providing room for two lines)
         *
         * @returns {number}
         */
        function getTreeActionIdealHeight() {
            // we need at least four actions to have a two-row ul
            var $treeActions = $('.tree-action-bar-box'),
                $treeActionUl = $treeActions.find('ul'),
                liNum = $treeActions.find('li:visible').length || 0,
                idealHeight;

            while(liNum < 5){
                $treeActionUl.append($('<li class="dummy"><a/></li>'));
                liNum++;
            }
            idealHeight = $treeActions.outerHeight() + parseInt($treeActions.css('margin-bottom'));
            $treeActionUl.find('li.dummy').remove();
            return idealHeight;
        }


        var setHeights = _.throttle(function setHeights() {
             var $contentWrapper = $('.content-wrapper'),
                 $searchBar = $('.search-action-bar'),
                 searchBarHeight = $searchBar.outerHeight()
                    + parseInt($searchBar.css('margin-bottom'))
                    + parseInt($searchBar.css('margin-top')),
                 $tree = $('.tree .ltr, .tree .rtl');

            if($contentWrapper.length) {
                $contentWrapper.find('.content-container').css({ minHeight: $('footer').offset().top - $contentWrapper.offset().top });
            }
            if($tree.length) {
                $tree.css({
                    maxHeight: ($('footer').offset().top - $contentWrapper.offset().top) - searchBarHeight - getTreeActionIdealHeight()
                });
            }        
        }, 100);

        $(window)
            .off('resize.sectioneight')
            .on('resize.sectionheight', _.debounce(setHeights, 50));

        $('.version-warning').on('hiding.versionwarning', setHeights);


        return {
            /**
             * Initialize behaviour of section height
             */
            init : function(){
                $('.taotree').on('ready.taotree', function() {
                    $('.navi-container').show();
                    setHeights();
                });
            },
            setHeights: setHeights
        };
    });
