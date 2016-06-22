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
define([
    'jquery',
    'util/shortcut/shortcut',
    'json!test/util/shortcut/keycheck'
], function ($, shortcutHelper, listOfKeys) {
    'use strict';

    var platformType = navigator.platform.indexOf('Mac') < 0 ? 'win' : 'mac';
    var specialKeys = {
        mac: {
            '<Shift>' : 'Shift',
            '<Ctrl>' : 'Ctrl',
            '<Meta>' : 'Cmd',
            '<Alt>' : 'Option'
        },
        win: {
            '<Shift>' : 'Shift',
            '<Ctrl>' : 'Ctrl',
            '<Meta>' : 'Win',
            '<Alt>' : 'Alt'
        }
    };

    var current = -1;
    var currentShortcut = null;
    var shortcutCaught = false;
    var shortcutsResults = [];
    var resultData = {
        platform: navigator.platform,
        browser: navigator.userAgent,
        shortcuts: shortcutsResults
    };

    var actions = {
        showShortcuts: function () {
            displaySection('shortcuts');
        },
        showResults: function () {
            displaySection('results');
        },
        selectResults: function () {
            selectText($('.results pre').get(0));
            try {
                document.execCommand('copy');
            } catch (err) {
                alert('Oops, unable to copy');
            }
        },
        resetAll: function () {
            displaySection('shortcuts');
            removeShortcut();
            current = -1;
            currentShortcut = null;
            shortcutsResults = [];
            setResults();
            actions.nextShortcut();
        },
        nextShortcut: function () {
            removeShortcut();
            if (!getNextShortcut()) {
                $('desk').hide();
                $('end').show();
                actions.showResults();
            } else {
                resetKeyChecker();
            }
        }
    };

    function getNextShortcut() {
        currentShortcut = null;
        while (!currentShortcut) {
            currentShortcut = listOfKeys[++ current];
            if (currentShortcut && currentShortcut.platform && currentShortcut.platform.indexOf(platformType) < 0) {
                currentShortcut = null;
            }
            if (current >= listOfKeys.length) {
                currentShortcut = null;
                break;
            }
        }
        return currentShortcut;
    }

    function setResults() {
        resultData.shortcuts = _.compact(shortcutsResults);
        $('.results pre').html(JSON.stringify(resultData, null, 2));
    }

    function updateCurrentResult() {
        var defaultPrevented = $('input[name="default_prevented"]').is(':checked');
        var comment = $('comment textarea').val();
        if (currentShortcut) {
            shortcutsResults[current] = {
                shortcut: formatShortcut(currentShortcut.label),
                shortcutCaught: shortcutCaught,
                defaultPrevented: defaultPrevented
            };

            $('prevented label[for="default_prevented_yes"]').css('color', defaultPrevented ? 'green' : null);
            $('prevented label[for="default_prevented_no"]').css('color', defaultPrevented ? null : 'red');

            if (comment) {
                shortcutsResults[current].comment = comment;
            } else {
                delete shortcutsResults[current].comment;
            }

            setResults();
        }
    }

    function resetKeyChecker() {
        $('prevented input[value="0"]').click();
        $('caught value').html('No').css('color', 'red');
        $('comment textarea').val('');

        shortcutCaught = false;
        addShortcut();
        updateCurrentResult();
        $('prevented').hide();
        $('end').hide();
        $('desk').show();
    }

    function addShortcut() {
        if (currentShortcut) {
            shortcutHelper.add(currentShortcut.shortcut, function() {
                shortcutCaught = true;
                $('caught value').html('Yes').css('color', 'green');
                $('prevented').show();
                updateCurrentResult();
            });

            $('key value').html(formatShortcut(currentShortcut.label));
            $('description value').html(formatShortcut(currentShortcut.description));
            $('playground textarea').val('The quick brown fox jumps over the lazy dog');
            $('playground').toggle(!!currentShortcut.playground);
        }
    }

    function removeShortcut() {
        if (currentShortcut) {
            shortcutHelper.remove(currentShortcut.shortcut);
        }
    }

    function formatShortcut(label) {
        _.forEach(specialKeys[platformType], function(spec, code) {
            label = label.replace(code, spec);
        });
        return label;
    }

    function displaySection(name) {
        $('nav button').removeClass('active');
        $('nav button.' + name).addClass('active');
        $('section').hide();
        $('section.' + name).show();
    }

    function selectText(element) {
        var range;
        if (document.selection) {
            range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
        } else if (window.getSelection) {
            range = document.createRange();
            range.selectNode(element);
            window.getSelection().addRange(range);
        }
        return element;
    }

    function start() {
        $(document).on('click', 'button', function (e) {
            var $btn = $(e.target);
            var control = $btn.data('control');
            actions[control] && actions[control]();
        });
        $('prevented input').on('change', updateCurrentResult);
        $('comment textarea').on('change', updateCurrentResult).on('blur', updateCurrentResult);


        /** Get info from the platform and the browser **/
        $('platform value').html(resultData.platform);
        $('browser value').html(resultData.browser);

        /** Start the tester **/
        actions.resetAll();
    }

    return start;
});