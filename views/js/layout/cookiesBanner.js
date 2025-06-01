define(["jquery"], function ($) {

  /**
   * Returns the base domain for setting the cookie.
   * @returns {String}
   */
  function getBaseDomain() {
    const parts = location.hostname.split(".");
    return parts.length > 2
      ? "." + parts.slice(-2).join(".")
      : window.location.hostname;
  }

  /**
   * Sets the cookie consent preferences as cookie.
   * @param {boolean} isAnalyticsEnabled
   */
  function setCookie(isAnalyticsEnabled) {
    const value = {
      essentials: true,
      analytics: isAnalyticsEnabled,
    };

    const maxAge = 60 * 60 * 24 * 365; // 1 year
    document.cookie = `${window.userCookies}=${JSON.stringify(value)};path=/; max-age=${
      maxAge}; domain=${getBaseDomain()};`;
    $("#cookies-banner").hide();
    // Reload the page to apply the new cookie settings
    window.location.reload();
  }

  return {
    init: function () {
      const $acceptButton = $("#accept-cookies");
      const $declineButton = $("#decline-cookies");
      const $cookiesPreferencesBlock = $("#cookies-preferences");
      const $cookiesMessageBlock = $("#cookies-message");

      $acceptButton.on("click", function () {
        const isAnalyticsEnabled = $cookiesPreferencesBlock.is(":visible")
          ? $("#analytics-toggle").prop("checked")
          : true;
        setCookie(isAnalyticsEnabled);
      });

     $declineButton.on("click", function () {
        setCookie(false);
      });

      $("#cookies-preferences-link").on("click", function (e) {
        e.preventDefault();
        $cookiesPreferencesBlock.toggle();
        $cookiesMessageBlock.toggle();
        $acceptButton.text($acceptButton.data('confirm-text') || 'Confirm choices');
      });
    },
  };
});
