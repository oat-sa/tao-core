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
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien.conan@vesperiagroup.com>
 */
define(['core/connectivity'], function(connectivity) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function(assert) {
        QUnit.expect(1);

        assert.equal(typeof connectivity, 'object', "The module exposes an object");
    });

    QUnit.test('eventifier', function(assert) {
        QUnit.expect(3);

        assert.equal(typeof connectivity.on, 'function', "The module has the eventifier's on method");
        assert.equal(typeof connectivity.off, 'function', "The module has the eventifier's off method");
        assert.equal(typeof connectivity.trigger, 'function', "The module has the eventifier's trigger method");
    });

    QUnit.cases([
        {title : 'isOnline' },
        {title : 'isOffline'},
        {title : 'setOnline'},
        {title : 'setOffline'}
    ])
    .test(' method ', function(data, assert) {
        QUnit.expect(1);

        assert.equal(typeof connectivity[data.title], 'function', 'The tokenHandler instanceexposes a "' + data.name + '" function');
    });

    QUnit.module('Behavior');

    QUnit.module('Manual');

    QUnit.test('manual test', function(assert) {
        var container = document.querySelector('.visual');
        var update = function(online){
            if(online){
                container.classList.remove('offline');
                container.classList.add('online');
            } else {
                container.classList.remove('online');
                container.classList.add('offline');
            }
        };
        QUnit.expect(1);

        assert.ok(container instanceof HTMLElement);

        update(connectivity.isOnline);
        connectivity.on('change', update);
    });
});
