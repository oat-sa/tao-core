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
 * Copyright (c) 2019 Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'ui/maths/calculator/core/labels',
    'tpl!test/ui/maths/calculator/core/labels/labels'
], function ($, _, labels, labelsTpl) {
    'use strict';

    QUnit.module('Module');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);
        assert.equal(typeof labels, 'object', "The module exposes an object");
    });

    QUnit.test('labels', function (assert) {
        QUnit.expect(1 + _.size(labels));

        assert.ok(_.size(labels) > 0, 'A list of labels is exposed');

        _.forEach(labels, function (label, id) {
            assert.equal(typeof label, 'string', 'The term ' + id + ' has a label');
        });
    });

    QUnit.module('visual test');

    QUnit.test('labels', function (assert) {
        var $container = $('#visual-test');

        QUnit.expect(2);

        assert.equal($container.children().length, 0, 'The container is empty');
        $container.html(labelsTpl(labels));
        assert.equal($container.children().length, _.size(labels), 'The labels are rendered');
    });

});
