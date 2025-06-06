define(["jquery", "tao/layout/cookiesBanner"], function ($, cookiesBanner) {
  "use strict";

  let googleAnalyticsMock = false;
  let userpilotMock = false;

  QUnit.module("layout/cookiesBanner", {
    beforeEach: function () {
      googleAnalyticsMock = false;
      userpilotMock = false;

      window.initGoogleAnalytics = function () {
        googleAnalyticsMock = true;
      };
      window.initUserpilot = function () {
        userpilotMock = true;
      };
    },
  });

  QUnit.test(
    "Accepting cookies starts analytics initializers",
    function (assert) {
      const done = assert.async();

      cookiesBanner.init().then(() => {
        $("#accept-cookies").trigger("click");

        setTimeout(() => {
          assert.ok($("#cookies-banner").is(":hidden"), "Banner is hidden");
          assert.ok(googleAnalyticsMock, "Google Analytics initialized");
          assert.ok(userpilotMock, "Userpilot initialized");
          done();
        }, 50);
      });
    }
  );

  QUnit.test(
    "Rejecting cookies does NOT call analytics initializers",
    function (assert) {
      const done = assert.async();

      cookiesBanner.init().then(() => {
        $("#decline-cookies").trigger("click");

        setTimeout(() => {
          assert.ok($("#cookies-banner").is(":hidden"), "Banner is hidden");
          assert.notOk(googleAnalyticsMock, "Google Analytics not initialized");
          assert.notOk(userpilotMock, "Userpilot not initialized");
          done();
        }, 50);
      });
    }
  );

  QUnit.test(
    "Clicking preferences link toggles blocks and updates button text",
    function (assert) {
      const done = assert.async();

      cookiesBanner.init().then(() => {
        $("#cookies-preferences-link").trigger("click");

        setTimeout(() => {
          assert.ok($("#cookies-preferences").is(":visible"),"Preferences block shown");
          assert.ok(!$("#cookies-message").is(":visible"),"Message block hidden");
          assert.equal($("#accept-cookies").text(),"Confirm choices", "Confirm button updated");
          done();
        }, 20);
      });
    }
  );
});
