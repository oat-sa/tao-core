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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

define([
    'lodash',
    'core/promise',
    'ui/component',
    'ui/component/alignable',
    'tpl!ui/animable/absorbable/tpl/absorb',
    'css!ui/animable/absorbable/css/absorb'
], function (_, Promise, componentFactory, makeAlignable, absorbTpl) {
    'use strict';

    var defaultConfig = {
        animationDuration: 1
    };

    var absorbableComponent = {
        absorb : function absorb($target){
            var self = this;
            var $component = this.getElement();
            var targetWidth = $target.width();
            var targetHeight = $target.height();
            var finalWidth = 10;
            var finalHeight = 10;
            var animationDuration = parseInt(this.config.animationDuration, 10) || 1;
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
                        .getElement().addClass('animate').css({
                        transitionDuration : animationDuration+'s'
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
                        animatedComponent.destroy();
                        resolve(self);//finish the animation by resolving the promise
                    },  1000 * animationDuration + animationStartOffset);

                }, animationStartOffset);
            });
        },
        absorbBurst : function($target, delayArray){

            var animations = [];
            var self = this;

            delayArray = _.isArray(delayArray) ? delayArray : [0];

            _.forEach(delayArray, function(startTimeOffset){
                animations.push(new Promise(function(resolve){
                    _.delay(function(){
                        self.absorb($target).then(resolve);
                    }, startTimeOffset);
                }));
            });
            return Promise.all(animations);
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
