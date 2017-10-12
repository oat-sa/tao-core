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
        var trace = { level: 'trace', msg: 'trace'},
            debug = { level: 'debug', msg: 'debug'},
            info  = { level: 'info',  msg: 'info'},
            warn  = { level: 'warn',  msg: 'warn'},
            error = { level: 'error', msg: 'error'},
            fatal = { level: 'fatal', msg: 'fatal'},
            messages;

        QUnit.expect(24);

        testLogger.log(trace);
        testLogger.log(debug);
        testLogger.log(info);
        testLogger.log(warn);
        testLogger.log(error);
        testLogger.log(fatal);

        messages = testLogger.getMessages();

        assert.ok(_.isArray(messages.trace), 'trace record are in an array');
        assert.equal(messages.trace.length, 1, 'on trace record has been logged');
        assert.deepEqual(messages.trace[0], trace, 'correct trace record has been logged');

        assert.ok(_.isArray(messages.debug), 'debug record are in an array');
        assert.equal(messages.debug.length, 1, 'on debug record has been logged');
        assert.deepEqual(messages.debug[0], debug, 'correct debug record has been logged');

        assert.ok(_.isArray(messages.info), 'info record are in an array');
        assert.equal(messages.info.length, 1, 'on info record has been logged');
        assert.deepEqual(messages.info[0], info, 'correct info record has been logged');

        assert.ok(_.isArray(messages.warn), 'warn record are in an array');
        assert.equal(messages.warn.length, 1, 'on warn record has been logged');
        assert.deepEqual(messages.warn[0], warn, 'correct warn record has been logged');

        assert.ok(_.isArray(messages.error), 'error record are in an array');
        assert.equal(messages.error.length, 1, 'on error record has been logged');
        assert.deepEqual(messages.error[0], error, 'correct error record has been logged');

        assert.ok(_.isArray(messages.fatal), 'fatal record are in an array');
        assert.equal(messages.fatal.length, 1, 'on fatal record has been logged');
        assert.deepEqual(messages.fatal[0], fatal, 'correct fatal record has been logged');

        testLogger.reset();
        messages = testLogger.getMessages();

        assert.equal(messages.trace.length, 0, 'trace records have been reseted');
        assert.equal(messages.debug.length, 0, 'debug records have been reseted');
        assert.equal(messages.info.length, 0,  'info records have been reseted');
        assert.equal(messages.warn.length, 0,  'warn records have been reseted');
        assert.equal(messages.error.length, 0, 'error records have been reseted');
        assert.equal(messages.fatal.length, 0, 'fatal records have been reseted');
    });

});