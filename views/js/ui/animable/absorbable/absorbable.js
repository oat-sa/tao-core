/**
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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA;
 */

define([
    'lodash',
    'ui/component',
    'ui/component/alignable',
    'tpl!ui/animable/absorbable/tpl/absorb',
    'css!ui/animable/absorbable/css/absorb'
], function (_, componentFactory, makeAlignable, absorbTpl) {
    'use strict';

    var defaultConfig = {
        initialX: 0,
        initialY: 0
    };

    var absorbableComponent = {
        absorb : function absorb($target){
            var self = this;
            var $component = this.getElement();
            var targetWidth = $target.width();
            var targetHeight = $target.height();
            var finalWidth = 10;
            var finalHeight = 10;
            var animationDuration = 1;
            var animationStartOffset = 10;//safety duration padding to allow styles to be properly applied
            var animatedComponent = makeAlignable(componentFactory())
                .setTemplate(absorbTpl)
                .init()
                .render($component)
                .setSize(targetWidth, targetHeight)
                .alignWith($target, {
                    hPos : 0,
                    vPos : 0,
                    hOrigin : 'center',
                    vOrigin : 'center'
                });

            return new Promise(function(resolve){
                _.delay(function(){
                    //css
                    animatedComponent
                        .getElement().css({
                        transition : animationDuration+'s cubic-bezier(.17,.61,1,.39)',
                        borderRadius : '50%'
                    });

                    animatedComponent
                        .setSize(finalWidth, finalHeight)
                        .alignWith($component, {
                            hPos : 0,
                            vPos : 0,
                            hOrigin : 'center',
                            vOrigin : 'center',
                            hOffset: targetWidth/2-finalWidth/2,
                            vOffset: targetHeight/2-finalHeight/2,
                        });

                    _.delay(function(){
                        animatedComponent.getElement().remove();
                        resolve(self);

                    },  1000 * animationDuration  + animationStartOffset);

                }, animationStartOffset);
            });
        }
    };

    /**
     * @param {Component} component - an instance of ui/component
     * @param {Object} config
     */
    function makeAbsorbable(component, config) {
        _.assign(component, absorbableComponent);

        return component
            .off('.makeAbsorbable')
            .on('init.makeAbsorbable', function() {
                _.defaults(this.config, config || {}, defaultConfig);
            });
    }

    return makeAbsorbable;
});
