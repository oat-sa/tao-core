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
 * Copyright (c) 2025-2026 Open Assessment Technologies SA;
 */

define(["jquery", "context", "util/cookies", "util/encode"], function (
  $,
  context,
  cookies,
  encode
) {
  const cookieStorage = cookies.createCookieStorage({ domainLevel: 2 });

/**
 * Initializes analytics tools.
 */
function initAnalytics() {
  if (window.initGoogleAnalytics) {
    window.initGoogleAnalytics();
  }
  if (window.initUserpilot) {
    window.initUserpilot();
  }
}

  /**
   * Returns the cookie name using SHA-256 of tenantId-userLogin.
   * @returns {Promise<string|null>}
   */
  async function getUserCookieName() {
    const tenantId = context.tenantId;
    const userLogin = context.currentUser.login;

    if (!tenantId|| !userLogin) return null;
    return `CookiePolicy-${await encode.stringToSha256(`${tenantId}-${userLogin}`)}`;
  }

  /**
   * Sets the cookie consent preferences.
   * @param {object} value
   */
  async function setCookiesPolicy(value) {
    const cookieKey = await getUserCookieName();
    cookieStorage.setItem(cookieKey, value);
    if (value.analytics) {
      initAnalytics();
    }
  }

  /**
   * Get cookie policy config, waiting if needed for production builds
   * @returns {Promise<object>} The cookie policy configuration
   */
  function getCookiePolicyConfig() {
    return new Promise((resolve) => {
      // Check immediately (works in dev, and if config is already ready)
      if (context.cookiePolicy) {
        resolve(context.cookiePolicy);
        return;
      }
      
      // In production, config may load asynchronously - wait briefly
      let attempts = 0;
      const check = () => {
        if (context.cookiePolicy) {
          resolve(context.cookiePolicy);
        } else if (attempts++ < 10) {
          setTimeout(check, 50);
        } else {
          // Fallback to defaults
          resolve({
            privacyPolicyUrl: 'https://www.taotesting.com/about/privacy/',
            cookiePolicyUrl: 'https://www.taotesting.com/about/privacy/',
            display: true
          });
        }
      };
      check();
    });
  }

  return {
    init: async function () {
      const $banner = $("#cookies-banner");
      const $acceptButton = $("#accept-cookies");
      const $declineButton = $("#decline-cookies");
      const $cookiesPreferencesBlock = $("#cookies-preferences");
      const $cookiesMessageBlock = $("#cookies-message");
      const $toggleBannerMessage = $("#cookies-preferences-link");
      const privacyPolicyUrl = $('#privacy-policy-link');
      const cookiePolicyUrl = $('#cookie-policy-link');
      const userCookieName = await getUserCookieName();

      const cookiePolicyConfig = await getCookiePolicyConfig();
      
      // Check if banner should be displayed based on configuration
      // If display is false, don't show the banner
      if (cookiePolicyConfig.display === false) {
        return; // Exit early - don't show banner
      }

      // Apply links to the template
      if (cookiePolicyConfig.privacyPolicyUrl) {
        privacyPolicyUrl.attr('href', cookiePolicyConfig.privacyPolicyUrl);
      }
      if (cookiePolicyConfig.cookiePolicyUrl) {
        cookiePolicyUrl.attr('href', cookiePolicyConfig.cookiePolicyUrl);
      }

      // Check if user already has cookie preferences saved
      if (userCookieName) {
        const cookieValue = cookieStorage.getItem(userCookieName);
        if (cookieValue) {
          const isAnalyticsEnabled = cookieValue.analytics;
          if (isAnalyticsEnabled) {
            initAnalytics();
          }
          return; // Don't show banner
        }
      }

      $banner.show();

      $acceptButton.on("click", function () {
        const isAnalyticsChecked = $cookiesPreferencesBlock.is(":visible")
          ? $("#analytics-toggle").prop("checked")
          : true;
        setCookiesPolicy({ essentials: true, analytics: isAnalyticsChecked });
        $banner.hide();
      });

      $declineButton.on("click", function () {
        setCookiesPolicy({ essentials: true, analytics: false });
        $banner.hide();
      });

      $($toggleBannerMessage).on("click", function (e) {
        e.preventDefault();
        $cookiesPreferencesBlock.toggle();
        $cookiesMessageBlock.toggle();
        $toggleBannerMessage.hide();
        $acceptButton.text($acceptButton.data("confirm-text") || "Confirm choices");
      });
    },
  };
});
