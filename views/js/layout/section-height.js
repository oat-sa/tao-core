/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA ;
 *
 */

/**
 * @author Dieter Raber <dieter@taotesting.com>
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash'
], function ($, _) {
    'use strict';


    var $versionWarning = $('.version-warning'),
        $window = $(window),
        $footer = $('body > footer');

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

        while (liNum < 5) {
            $treeActionUl.append($('<li class="dummy"><a/></li>'));
            liNum++;
        }

        idealHeight = $treeActions.outerHeight(true);
        $treeActionUl.find('li.dummy').remove();
        return idealHeight;
    }

    /**
     * Compute the max height of the navi- and content container
     *
     * @returns {number}
     */
    function getContainerMaxHeight($scope) {
        var winHeight = $window.height(),
            footerHeight = $footer.outerHeight(),
            headerHeight = $('header.dark-bar').outerHeight() + Number($versionWarning.outerHeight()),
            actionBarHeight = $scope.find('.content-container .action-bar').outerHeight();

        return winHeight - headerHeight - footerHeight - actionBarHeight;
    }


    /**
     * Resize section heights
     * @private
     * @param {jQueryElement} $scope - the section scope
     */
    function setHeights($scope) {
        var containerMaxHeight = getContainerMaxHeight($scope),
            $contentBlock = $scope.find('.content-block'),
            $tree = $scope.find('.taotree');

        if (!$tree.length) {
            return;
        }

        $contentBlock.css( { height: containerMaxHeight, maxHeight: containerMaxHeight });
        $tree.css({
            maxHeight: containerMaxHeight - getTreeActionIdealHeight()
        });
    }

    /**
     * Helps you to manage the section heights
     * @exports layout/section-height
     */
    return {

        /**
         * Initialize behaviour of section height
         * @param {jQueryElement} $scope - the section scope
         */
        init: function ($scope) {

            $window
                .off('resize.sectionheight')
                .on('resize.sectionheight', _.debounce(function () {
                    setHeights($scope);
                }, 50));

            $versionWarning
                .off('hiding.versionwarning')
                .on('hiding.versionwarning', function () {

                    setHeights($scope);
                });
        },

        /**
         * Resize section heights
         * @param {jQueryElement} $scope - the section scope
         */
        setHeights: setHeights
    };
});
