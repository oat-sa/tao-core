define([
  'jquery',
  'tao/layout/cookiesBanner'
], function ($, cookiesBanner) {
  'use strict';

  QUnit.module('layout/cookiesBanner');

  QUnit.test('Module exports an init function', function (assert) {
    assert.equal(typeof cookiesBanner.init, 'function', 'The module exposes an init function');
  });

  QUnit.test('Accepting cookies hides the banner and sets analytics', function (assert) {
    const done = assert.async();

    // Stub cookieStorage and context
    const fakeStorage = {};
    const cookieKey = 'CookiePolicy-test';

    // Fake the required dependencies
    window.context = {
      tenantId: 'tenant',
      currentUser: { login: 'test' }
    };

    define('util/encode', [], function () {
      return {
        stringToSha256: async (str) => 'test'
      };
    });

    define('util/cookies', [], function () {
      return {
        createCookieStorage: () => ({
          getItem: () => null,
          setItem: (key, value) => {
            fakeStorage[key] = value;
          }
        })
      };
    });

    // Mock analytics init
    window.initGoogleAnalytics = () => {
      assert.ok(true, 'Google Analytics initialized');
    };

    window.initUserpilot = () => {
      assert.ok(true, 'Userpilot initialized');
    };

    // Run the init and simulate click
    cookiesBanner.init().then(() => {
      $('#accept-cookies').trigger('click');

      setTimeout(() => {
        assert.deepEqual(fakeStorage[cookieKey], {
          essentials: true,
          analytics: true
        }, 'Cookie was saved with analytics');

        assert.ok($('#cookies-banner').is(':hidden'), 'Banner is hidden after accept');
        done();
      }, 50);
    });
  });

});
