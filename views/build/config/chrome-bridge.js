/**
 * Bridge for Chrome<->QUnit 1.
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
    const testTimeoutMs = 30 * 1000;

    /**
     * Emit an event to the grunt task (report and control)
     * @param {String} eventName
     * @param {...*}   [args]
     */
    function emit(eventName, ...args) {
        self.__grunt_contrib_qunit__(eventName, ...args);
    }

    //Keep test other and run them in serie.
    QUnit.config.reorder = false;
    QUnit.config.autorun = false;

    /**
     * QUnit begins
     */
    QUnit.begin( () => emit('qunit.begin') );

    /**
     * A module get started
     */
    QUnit.moduleStart( ({name}) => emit('qunit.moduleStart', name));

    /**
     * A test case get started
     */
    QUnit.testStart( ({ testName }) =>  {

        //start a timeout and
        //keep it in the map under the test name
        runningTestsTimeouts.set(testName,
            setTimeout( () => {
                emit('fail.timeout', testName);
                runningTestsTimeouts.delete(testName);
            }, testTimeoutMs)
        );
        emit('qunit.testStart', testName);
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
    QUnit.testDone(logs => {
        const testName = logs.name;
        if(runningTestsTimeouts.has(testName)){
            clearTimeout(runningTestsTimeouts.get(testName));
            runningTestsTimeouts.delete(testName);
        }

        emit('qunit.testDone', logs.name, logs.failed, logs.passed, logs.total, logs.runtime, logs.skipped, 0);
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
