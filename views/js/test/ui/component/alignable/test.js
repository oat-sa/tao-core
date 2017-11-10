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
/**
< * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'jquery',
    'ui/component',
    'ui/component/placeable',
    'ui/component/alignable'
], function ($, componentFactory, makePlaceable, makeAlignable) {
    'use strict';

    var fixtureContainer = '#qunit-fixture';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof makeAlignable === 'function', 'The module expose a function');
    });

    QUnit
        .cases([
            { title: 'alignWith' },
            { title: 'hAlignWith' },
            { title: 'vAlignWith' },
            { title: '_getAlignedCoords' },
            { title: '_getDefaultHOrigin' },
            { title: '_getDefaultVOrigin' }
        ])
        .test('component API', function(data, assert) {
            var component = makeAlignable(componentFactory());

            QUnit.expect(1);
            assert.equal(typeof component[data.title], 'function', 'The component has the method ' + data.title);
        });

    QUnit.test('auto makes the component placeable', function(assert) {
        var component = makeAlignable(componentFactory());
        QUnit.expect(1);
        assert.ok(makePlaceable.isPlaceable(component), 'created component is placeable');
    });

    QUnit.module('Alignable Component');

    QUnit
        .cases([
            // with default origins
            { title: 'centerH, centerV', hPos: 'center', vPos: 'center', expectedX: 350, expectedY: 325 },
            { title: 'leftH, centerV',   hPos: 'left',   vPos: 'center', expectedX: 200, expectedY: 325 },
            { title: 'rightH, centerV',  hPos: 'right',  vPos: 'center', expectedX: 500, expectedY: 325 },
            { title: 'centerH, topV',    hPos: 'center', vPos: 'top',    expectedX: 350, expectedY: 250 },
            { title: 'centerH, bottomV', hPos: 'center', vPos: 'bottom', expectedX: 350, expectedY: 400 },

            // with custom hOrigin
            { title: 'centerH, hOrigin left',    hPos: 'center', hOrigin: 'left',    vPos: 'center', expectedX: 400, expectedY: 325 },
            { title: 'centerH, hOrigin center',  hPos: 'center', hOrigin: 'center',  vPos: 'center', expectedX: 350, expectedY: 325 },
            { title: 'centerH, hOrigin right',   hPos: 'center', hOrigin: 'right',   vPos: 'center', expectedX: 300, expectedY: 325 },
            { title: 'leftH, hOrigin left',      hPos: 'left',   hOrigin: 'left',    vPos: 'center', expectedX: 300, expectedY: 325 },
            { title: 'leftH, hOrigin center',    hPos: 'left',   hOrigin: 'center',  vPos: 'center', expectedX: 250, expectedY: 325 },
            { title: 'leftH, hOrigin right',     hPos: 'left',   hOrigin: 'right',   vPos: 'center', expectedX: 200, expectedY: 325 },
            { title: 'rightH, hOrigin left',     hPos: 'right',  hOrigin: 'left',    vPos: 'center', expectedX: 500, expectedY: 325 },
            { title: 'rightH, hOrigin center',   hPos: 'right',  hOrigin: 'center',  vPos: 'center', expectedX: 450, expectedY: 325 },
            { title: 'rightH, hOrigin right',    hPos: 'right',  hOrigin: 'right',   vPos: 'center', expectedX: 400, expectedY: 325 },

            // with custom vOrigin
            { title: 'centerV, vOrigin top',     hPos: 'center', vOrigin: 'top',    vPos: 'center', expectedX: 350, expectedY: 350 },
            { title: 'centerV, vOrigin center',  hPos: 'center', vOrigin: 'center', vPos: 'center', expectedX: 350, expectedY: 325 },
            { title: 'centerV, vOrigin bottom',  hPos: 'center', vOrigin: 'bottom', vPos: 'center', expectedX: 350, expectedY: 300 },
            { title: 'topV, vOrigin top',        hPos: 'center', vOrigin: 'top',    vPos: 'top',    expectedX: 350, expectedY: 300 },
            { title: 'topV, vOrigin center',     hPos: 'center', vOrigin: 'center', vPos: 'top',    expectedX: 350, expectedY: 275 },
            { title: 'topV, vOrigin bottom',     hPos: 'center', vOrigin: 'bottom', vPos: 'top',    expectedX: 350, expectedY: 250 },
            { title: 'bottomV, vOrigin top',     hPos: 'center', vOrigin: 'top',    vPos: 'bottom', expectedX: 350, expectedY: 400 },
            { title: 'bottomV, vOrigin center',  hPos: 'center', vOrigin: 'center', vPos: 'bottom', expectedX: 350, expectedY: 375 },
            { title: 'bottomV, vOrigin bottom',  hPos: 'center', vOrigin: 'bottom', vPos: 'bottom', expectedX: 350, expectedY: 350 },

            // with offsets
            { title: 'positive Offset', hPos: 'center', vPos: 'center', hOffset:  10, vOffset:  10, expectedX: 360, expectedY: 335 },
            { title: 'negative Offset', hPos: 'center', vPos: 'center', hOffset: -10, vOffset: -10, expectedX: 340, expectedY: 315 }


        ])
        .asyncTest('.alignWith(), .hAlignWith(), .vAlignWith()', function (data, assert) {
            var component = makeAlignable(componentFactory()),
                $container = $(fixtureContainer),
                $refElement = ($('<div>REFERENCE</div>')),
                moveCounter = 0;

            QUnit.expect(13);

            $container.append($refElement);

            $refElement.css({
                position: 'absolute',
                width: '200px',
                height: '100px',
                left: '300px',
                top: '300px'
            });

            component
                .on('render', function() {
                    this.alignWith($refElement, {
                        hPos: data.hPos,
                        vPos: data.vPos,
                        hOrigin: data.hOrigin,
                        vOrigin: data.vOrigin,
                        hOffset: data.hOffset,
                        vOffset: data.vOffset
                    });
                })
                .on('move', function() {
                    var componentPosition;

                    moveCounter++;

                    // .alignWith() result
                    if (moveCounter === 2) {
                        componentPosition = this.getPosition();

                        assert.ok(true, 'move event has been triggered');
                        assert.equal(componentPosition.x, data.expectedX, 'component has the correct x position');
                        assert.equal(componentPosition.y, data.expectedY, 'component has the correct y position');

                        this.resetPosition();
                        componentPosition = this.getPosition();
                        assert.equal(componentPosition.x, 0, 'component x position has been reset');
                        assert.equal(componentPosition.y, 0, 'component y position has been reset');

                        this.hAlignWith($refElement, data.hPos, data.hOrigin, data.hOffset);

                    // .hAlignWith() result
                    } else if(moveCounter === 4) {
                        componentPosition = this.getPosition();

                        assert.ok(true, 'move event has been triggered');
                        assert.equal(componentPosition.x, data.expectedX, 'component has the correct x position');
                        assert.equal(componentPosition.y, 0, 'component has the correct y position');

                        this.resetPosition();
                        componentPosition = this.getPosition();
                        assert.equal(componentPosition.x, 0, 'component x position has been reset');
                        assert.equal(componentPosition.y, 0, 'component y position has been reset');

                        this.vAlignWith($refElement, data.vPos, data.vOrigin, data.vOffset) ;

                    // .vAlignWith() result
                    } else if(moveCounter === 6) {
                        componentPosition = this.getPosition();

                        assert.ok(true, 'move event has been triggered');
                        assert.equal(componentPosition.x, 0, 'component has the correct x position');
                        assert.equal(componentPosition.y, data.expectedY, 'component has the correct y position');

                        QUnit.start();
                    }
                })
                .init({
                    width: 100,
                    height: 50
                })
                .render($container);
        });


    QUnit.module('Visual test');

    QUnit.asyncTest('display and play', function (assert) {
        var component = makeAlignable(componentFactory()),
            $container = $('#outside');

        QUnit.expect(1);

        component
            .on('render', function(){
                var self = this,
                    $target = $container.find('#target'),
                    $hPos = $container.find('#hPos'),
                    $vPos = $container.find('#vPos'),
                    $hOrigin = $container.find('#hOrigin'),
                    $vOrigin = $container.find('#vOrigin');

                $container.find('select').on('change', function(e) {
                    var options = {
                        hPos: $hPos.val(),
                        vPos: $vPos.val()
                    };
                    e.preventDefault();

                    if ($hOrigin.val() && $hOrigin.val() !== 'default') {
                        options.hOrigin = $hOrigin.val();
                    }
                    if ($vOrigin.val() && $vOrigin.val() !== 'default') {
                        options.vOrigin = $vOrigin.val();
                    }

                    self.alignWith($($target.val()), options);
                });

                assert.ok(true);
                QUnit.start();
            })
            .init()
            .render($container)
            .setSize(200, 100)
            .moveBy(100, 100);
    });

});