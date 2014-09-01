/**
 * @author Dieter Raber <dieter@taotesting.com>
 */
define([
    'jquery',
    'jquery.cookie'
],
    function($){


        /**
         * Bar with the tree actions (providing room for two lines)
         *
         * @returns {number}
         */
        function getTreeActionIdealHeight() {
            // we need at least four actions to have a two-row ul
            var $treeActions = $('.tree-action-bar-box'),
                $treeActionUl = $treeActions.find('ul'),
                liNum = (5 - $treeActions.find('li:visible').length),
                idealHeight;

            while(liNum--){
                $treeActionUl.append($('<li class="dummy"><a/></li>'));
            }
            idealHeight = $treeActions.outerHeight() + parseInt($treeActions.css('margin-bottom'));
            $treeActionUl.find('li.dummy').remove();
            return idealHeight;
        }


        function setHeights() {
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
        }


        return {
            /**
             * Initialize behaviour of section height
             */
            init : function(){
                $('.taotree').on('ready.taotree', function() {
                    setHeights();
                });
            },
            setHeights: setHeights
        };
    });