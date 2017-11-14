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
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'tao/test/core/logger/testLogger'
], function (_, testLogger) {
    'use strict';

    QUnit.module('API');

    QUnit.test('module', function (assert) {
        QUnit.expect(1);

        assert.ok(typeof testLogger === 'object', 'The module expose an object');
    });

    QUnit.module('TestLogger');

    QUnit.test('can log and retrieve messages', function(assert) {
        var debug = { level: 'debug', msg: 'debug'},
            info  = { level: 'info',  msg: 'info'},
            notice  = { level: 'notice',  msg: 'notice'},
            warning  = { level: 'warning',  msg: 'warning'},
            error = { level: 'error', msg: 'error'},
            critical = { level: 'critical', msg: 'critical'},
            alert = { level: 'alert', msg: 'alert'},
            emergency = { level: 'emergency', msg: 'emergency'},
            messages;

        QUnit.expect(32);

        testLogger.log(debug);
        testLogger.log(info);
        testLogger.log(notice);
        testLogger.log(warning);
        testLogger.log(error);
        testLogger.log(critical);
        testLogger.log(alert);
        testLogger.log(emergency);

        messages = testLogger.getMessages();

        assert.ok(_.isArray(messages.debug), 'debug record are in an array');
        assert.equal(messages.debug.length, 1, 'on debug record has been logged');
        assert.deepEqual(messages.debug[0], debug, 'correct debug record has been logged');

        assert.ok(_.isArray(messages.info), 'info record are in an array');
        assert.equal(messages.info.length, 1, 'on info record has been logged');
        assert.deepEqual(messages.info[0], info, 'correct info record has been logged');

        assert.ok(_.isArray(messages.notice), 'notice record are in an array');
        assert.equal(messages.notice.length, 1, 'on notice record has been logged');
        assert.deepEqual(messages.notice[0], notice, 'correct notice record has been logged');

        assert.ok(_.isArray(messages.warning), 'warning record are in an array');
        assert.equal(messages.warning.length, 1, 'on warning record has been logged');
        assert.deepEqual(messages.warning[0], warning, 'correct warning record has been logged');

        assert.ok(_.isArray(messages.error), 'error record are in an array');
        assert.equal(messages.error.length, 1, 'on error record has been logged');
        assert.deepEqual(messages.error[0], error, 'correct error record has been logged');

        assert.ok(_.isArray(messages.critical), 'critical record are in an array');
        assert.equal(messages.critical.length, 1, 'on critical record has been logged');
        assert.deepEqual(messages.critical[0], critical, 'correct critical record has been logged');

        assert.ok(_.isArray(messages.alert), 'alert record are in an array');
        assert.equal(messages.alert.length, 1, 'on alert record has been logged');
        assert.deepEqual(messages.alert[0], alert, 'correct alert record has been logged');

        assert.ok(_.isArray(messages.emergency), 'emergency record are in an array');
        assert.equal(messages.emergency.length, 1, 'on emergency record has been logged');
        assert.deepEqual(messages.emergency[0], emergency, 'correct emergency record has been logged');

        testLogger.reset();
        messages = testLogger.getMessages();

        assert.equal(messages.debug.length, 0, 'debug records have been reseted');
        assert.equal(messages.info.length, 0,  'info records have been reseted');
        assert.equal(messages.notice.length, 0,  'notice records have been reseted');
        assert.equal(messages.warning.length, 0,  'warning records have been reseted');
        assert.equal(messages.error.length, 0, 'error records have been reseted');
        assert.equal(messages.critical.length, 0, 'critical records have been reseted');
        assert.equal(messages.alert.length, 0, 'alert records have been reseted');
        assert.equal(messages.emergency.length, 0, 'emergency records have been reseted');
    });

});