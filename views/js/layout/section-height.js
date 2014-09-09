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
                liNum = (5 - $treeActions.find('li:visible').length),
                idealHeight;

            //TODO check (added to prevent inifinite loop on liNum)
            if(liNum < 0){
                liNum = 0;
            }

            while(liNum--){
                $treeActionUl.append($('<li class="dummy"><a/></li>'));
            }
            idealHeight = $treeActions.outerHeight() + parseInt($treeActions.css('margin-bottom'));
            $treeActionUl.find('li.dummy').remove();
            return idealHeight;
        }


        var setHeights = _.debounce(function setHeights() {
            var $contentWrapper     = $('.content-wrapper'),
                contentWrapperTop   = $contentWrapper.offset().top, 
                $searchBar          = $('.search-action-bar'),
                searchBarHeight     = $searchBar.outerHeight(true),
                footerTop           = $('footer').offset.top,
                $tree               = $('.tree .ltr, .tree .rtl'),
                treeIdealHeight     = 0;

            if($tree.length) {
                treeIdealHeight = getTreeActionIdealHeight();            
            }

            //height must be set in another animation frame
            _.defer(function(){
                if($contentWrapper.length) {
                    $contentWrapper.find('.content-container').css({ minHeight: footerTop - contentWrapperTop });
                }

                if($tree.length) {
                    $tree.css({
                        maxHeight: (footerTop - contentWrapperTop) - searchBarHeight - treeIdealHeight
                    });
                }
            });
        }, 50);

        $(window)
            .off('resize.sectioneight')
            .on('resize.sectionheight', setHeights);
        $('.version-warning').on('hiding.versionwarning', setHeights);


        return {
            /**
             * Initialize behaviour of section height
             */
            init : function(){
                $('.taotree').on('ready.taotree', setHeights);
            },
            setHeights: setHeights
        };
    });
