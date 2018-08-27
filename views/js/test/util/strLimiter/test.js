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
 * Copyright (c) 2018 Open Assessment Technologies SA
 *
 *
 */
define([
    'util/strLimiter'
], function(strLimiter){

    'use strict';

    var txt = 'Lorem ipsum dolor sit amet'; // 5 words, 26 characters

    var weirdWhiteSpace = 'Lorem    ipsum   dolor  sit   amet';

    QUnit.module('API');

    QUnit.test('Limit by Word Count', function(assert){
        assert.equal(strLimiter.limitByWordCount(txt, 5), 'Lorem ipsum dolor sit amet', 'Word count, input already correct size');
        assert.equal(strLimiter.limitByWordCount(txt, 10), 'Lorem ipsum dolor sit amet', 'Word count, input too short');
        assert.equal(strLimiter.limitByWordCount(txt, 2), 'Lorem ipsum', 'Word count, input too long');
    });

    QUnit.test('Limit by Word Count, weird whitespace', function(assert){
        assert.equal(strLimiter.limitByWordCount(weirdWhiteSpace, 5), 'Lorem    ipsum   dolor  sit   amet', 'Word count, input already correct size');
        assert.equal(strLimiter.limitByWordCount(weirdWhiteSpace, 10), 'Lorem    ipsum   dolor  sit   amet', 'Word count, input too short');
        assert.equal(strLimiter.limitByWordCount(weirdWhiteSpace, 2), 'Lorem    ipsum', 'Word count, input too long');
    });

    QUnit.test('Limit by Character count', function(assert){
        assert.equal(strLimiter.limitByCharCount(txt, 26), 'Lorem ipsum dolor sit amet', 'Char count, input already correct size');
        assert.equal(strLimiter.limitByCharCount(txt, 100), 'Lorem ipsum dolor sit amet', 'Char count, input too short');
        assert.equal(strLimiter.limitByCharCount(txt, 11), 'Lorem ipsum', 'Char count, input too long');
    });
});
