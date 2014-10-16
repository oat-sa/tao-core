/**
 * @author Dieter Raber <dieter@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'jquery.cookie'
],
    function($, _){
    'use strict';

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


        var setHeights = function setHeights($scope) {
            var $contentPanel = $scope.is('.content-panel') ? $scope : $('.content-panel', $scope),
                $searchBar,
                searchBarHeight,
                $tree,
                footerTop,
                contentWrapperTop,
                $itemEditorPanel,
                $itemSidebars,
                remainingHeight;

            if (!$contentPanel.length) {
                return;
            }
            
            $searchBar = $contentPanel.find('.search-action-bar');
            searchBarHeight = $searchBar.outerHeight() + parseInt($searchBar.css('margin-bottom')) + parseInt($searchBar.css('margin-top'));
            $tree = $contentPanel.find('.tree .ltr, .tree .rtl');
            $itemSidebars = $('.item-editor-sidebar');
            footerTop = (function() {
                var $footer = $('footer'),
                    footerTop;
                if(!$itemSidebars.length) {
                    return $footer.offset().top;
                }
                $itemSidebars.hide();
                footerTop = $footer.offset().top;
                $itemSidebars.show();
                return footerTop;
            }());
            contentWrapperTop = $contentPanel.offset().top;
            remainingHeight = footerTop - contentWrapperTop;


            $itemEditorPanel = $('#item-editor-panel');

            if(!$itemEditorPanel.length){
                $contentPanel.find('.content-container').css({ minHeight: remainingHeight });
            }
            else {
                // in the item editor the action bars are constructed slightly differently
                remainingHeight -= $('.item-editor-action-bar').outerHeight();
                $itemEditorPanel.find('#item-editor-scroll-outer').css({ minHeight: remainingHeight, maxHeight: remainingHeight, height: remainingHeight });
                $itemSidebars.css({ minHeight: remainingHeight, maxHeight: remainingHeight, height: remainingHeight });
            }

            if($tree.length) {
                $tree.css({
                    maxHeight: (footerTop - contentWrapperTop) - searchBarHeight - getTreeActionIdealHeight()
                });
            }        
        };


        return {
            /**
             * Initialize behaviour of section height
             */
            init : function($scope){

                $(window)
                    .off('resize.sectioneight')
                    .on('resize.sectionheight', _.debounce(function(){ setHeights($scope); }, 50));

                $('.version-warning')
                    .off('hiding.versionwarning')
                    .on('hiding.versionwarning', function(){

                    setHeights($scope);
                });

                $('.taotree', $scope).on('ready.taotree', function() {
                    setHeights($scope);
                });
            },
            setHeights: setHeights
        };
    });
