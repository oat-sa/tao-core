define(["jquery"], function ($) {
  "use strict";

  /**
   * Returns the base domain for setting cookies
   * @returns {String}
   */
  function getBaseDomain() {
    const parts = location.hostname.split(".");
    return parts.length > 2
      ? "." + parts.slice(-2).join(".")
      : window.location.hostname;
  }
  /**
   * Sets the cookie consent preferences as a cookie.
   * @param {boolean} analytics Whether analytics cookies are allowed.
   * @param {String} cookieName Name of the cookie to set.
   */
  function setCookie(analytics, cookieName) {
    const value = {
      essentials: true,
      analytics,
    };
    document.cookie = `${cookieName}=${JSON.stringify(
      value
    )}; path=/; max-age=${60 * 60 * 24 * 365}; domain=${getBaseDomain()};`;
    window.location.reload();
  }

  return {
    init: function () {
      const $cookieBanner = $("#cookies-banner");
      const $cookiesMessage = $("#cookies-message");
      const $acceptButton = $("#accept-cookies");
      const $declineButton = $("#decline-cookies");
      const $cookiesPreferencesLink = $("#cookies-preferences-link");
      const $cookiesPreferences = $("#cookies-preferences");
      const $analyticsToggle = $("#analytics-toggle");
      const cookieName = $cookieBanner.data("cookie-name");

      $acceptButton.on("click", function () {
        const isPrefsVisible = $cookiesPreferences.is(":visible");
        const analyticsAllowed = isPrefsVisible
          ? $analyticsToggle.prop("checked")
          : true;
        setCookie(analyticsAllowed, cookieName);
      });

      $declineButton.on("click", function () {
        setCookie(false, cookieName);
      });

      $cookiesPreferencesLink.on("click", function (e) {
        e.preventDefault();
        const isVisible = $cookiesPreferences.is(":visible");
        $cookiesPreferences.toggle();
        $cookiesMessage.toggle();

        if ($acceptButton.length) {
          $acceptButton.text(isVisible ? "Accept all" : "Confirm choices");
        }
      });
    },
  };
});
