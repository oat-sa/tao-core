/**
 * Bridge for Chrome<->QUnit 2 (legacy hooks + QUnit.on for grunt-contrib-qunit 9).
 * We expect QUnit to be injected by the page itself.
 * The bridge is an adaptation of https://github.com/gruntjs/grunt-contrib-qunit/blob/master/chrome/bridge.js, under MIT license.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
(function(){
    'use strict';

    /**
     * Keep track of the running tests
     * and trigger the timeout manually,
     * because it seems not working in the pupeteer config,
     * and otherwise the suite get stuck.
     */
    const runningTestsTimeouts = new Map();

    /**
     * Max time to wait for a test before it is considered as timed out.
     */
    const testTimeoutMs = 60 * 1000;

    /**
     * Max time to wait between the page loads and the first test to run
     */
    const pageLoadTimeoutMs = 10 * 1000;

    /**
     * Emit an event to the grunt task (report and control)
     * @param {String} eventName
     * @param {...*}   [args]
     */
    function emit(eventName, ...args) {
        self.__grunt_contrib_qunit__(eventName, ...args);
    }

    /**
     * Ensure failed assertion objects can cross the Puppeteer boundary to Node
     * @param {Array<Object>|undefined|null} errors - QUnit `testEnd` `errors` array
     * @returns {Array<Object>} same array if serializable, else shallow copies with stringified actual/expected
     */
    function sanitizeErrorsForBridge(errors) {
        if (!errors || !errors.length) {
            return [];
        }
        try {
            JSON.stringify(errors);
            return errors;
        } catch (e) {
            return errors.map(function (error) {
                function repl(x) {
                    try {
                        JSON.stringify(x);
                        return x;
                    } catch (err) {
                        return String(x);
                    }
                }
                return {
                    passed: error.passed,
                    message: error.message,
                    stack: error.stack,
                    actual: repl(error.actual),
                    expected: repl(error.expected)
                };
            });
        }
    }

    const pageLoadTimer = setTimeout(() => {
        emit('fail.load', window.location.href);
    }, pageLoadTimeoutMs);

    //Keep test other and run them in serie.
    QUnit.config.reorder = false;
    QUnit.config.autorun = false;

    /**
     * QUnit begins
     */
    QUnit.begin( () => {
        clearTimeout(pageLoadTimer);
        emit('qunit.begin');
    });

    /**
     * A module get started
     */
    QUnit.moduleStart( ({name}) => emit('qunit.moduleStart', name));

    /**
     * A test case get started
     */
    QUnit.on('testStart', logs => {
        const testRunKey = logs.testId != null ? logs.testId : logs.fullName.join(' > ');
        //start a timeout and
        //keep it in the map under the test name
        runningTestsTimeouts.set(testRunKey,
            setTimeout( () => {
                emit('fail.timeout', testRunKey);
                runningTestsTimeouts.delete(testRunKey);
            }, testTimeoutMs)
        );

        emit('qunit.on.testStart', logs);
        emit('qunit.testStart', testRunKey);
    });

    /**
     * QUnit sends some logs,
     * especially when something goes bad
     */
    QUnit.log( logs => {
        if (!logs.result) {
            //QUnit 1<->2 compat
            const dump = QUnit.dump || QUnit.jsDump;

            emit('qunit.log', false, dump.parse(logs.actual), dump.parse(logs.expected), logs.message, logs.source);
        } else {
            emit('qunit.log', logs.result, false, false, logs.message, logs.source);
        }
    });

    /**
     * The test case is done
     */
    QUnit.on('testEnd', logs => {
        const testRunKey = logs.testId != null ? logs.testId : logs.fullName.join(' > ');
        if(runningTestsTimeouts.has(testRunKey)){
            clearTimeout(runningTestsTimeouts.get(testRunKey));
            runningTestsTimeouts.delete(testRunKey);
        }

        const errors = sanitizeErrorsForBridge(logs.errors);
        emit('qunit.on.testEnd', {
            name: logs.name,
            moduleName: logs.moduleName || logs.suiteName || '',
            fullName: logs.fullName,
            status: logs.status,
            runtime: logs.runtime,
            errors
        });

        const assertions = logs.assertions || [];
        const failed = logs.errors ? logs.errors.length : 0;
        const passed = assertions.filter(function (a) {
            return a.passed;
        }).length;
        const total = assertions.length;
        const skipped = logs.status === 'skipped' ? 1 : 0;
        emit('qunit.testDone', logs.name, failed, passed, total, logs.runtime, skipped, 0);
    });

    /**
     * The test run is done
     */
    QUnit.on('runEnd', logs => {
        emit('qunit.on.runEnd', {
            testCounts: logs.testCounts,
            runtime: logs.runtime,
            status: logs.status
        });
    });

    /**
     * The module is done
     */
    QUnit.moduleDone( logs => emit('qunit.moduleDone', logs.name, logs.failed, logs.passed, logs.total));

    /**
     * And QUnit has finished
     */
    QUnit.done( logs => emit('qunit.done', logs.failed, logs.passed, logs.total, logs.runtime));

})();
