<div id="cookies-banner" class="cookies-banner xhtml_form">
  <div class="cookies-banner-icon">
    <span class="icon-info" aria-hidden="true"></span>
  </div>
  <div>
    <h3><strong><?= __('Cookie Policy') ?></strong></h3>
    <div id="cookies-message">
      <p>
      <?= __('This site uses cookies to improve your browsing experience and to perform analytics and research.') ?>
        <?= __('You can update your choice clicking on') ?>
        <a href="#" id="cookies-preferences-link" class="cookies-banner-preferences-link"><?= __('Cookies Preferences') ?></a>.
        <?= __('Otherwise, clicking Accept all cookies indicates you agree to our use of cookies on your device. Clicking Reject all cookies means you do not agree to our use of non-strictly necessary cookies on your device.') ?>
      </p>
      <p>
        <?= __('You can read more about this in our') ?>
        <a href="<?= get_data('privacyPolicyLink') ?: 'https://www.taotesting.com/about/privacy/' ?>" target="_blank"><?= __('Privacy Policy') ?></a>
        <?= __('and') ?>
        <a href="<?= get_data('cookiePolicyLink') ?: 'https://www.taotesting.com/about/privacy/' ?>" target="_blank"><?= __('Cookie Policy') ?></a>
      </p>
    </div>

    <div id="cookies-preferences" style="display: none;">
      <h4><strong><?= __('Manage Preferences') ?></strong></h4>
      <div>
        <p><?= __('You can choose which types of cookies you want to allow to improve your experience.') ?></p>
        <form id="cookies-preferences-form">
          <label class="checkbox">
            <input type="checkbox" disabled checked />
            <span class="icon-checkbox"></span>
            <strong><?= __('Essential cookies (required)') ?></strong>
          </label>
          <div>
             <?= __('Strictly Necessary Cookie should be enabled at all times so that we can save your preferences for cookie settings.') ?>
          </div>
          <label class="checkbox">
            <input type="checkbox" data-cookie-value="analytics" checked id="analytics-toggle" />
            <span class="icon-checkbox"></span>
             <strong><?= __('Analytics cookies') ?></strong>
          </label>
          <div>
             <?= __('These cookies allow us to count visits and various other analytical data so we can measure and improve the performance of our site. If you do not allow these cookies we will not know when you have visited our site, and will not be able to monitor its performance.') ?>
          </div>
        </form>
      </div>
    </div>
    <div class="cookies-banner-actions">
      <button id="decline-cookies" class="btn-secondary btn-info small"><?= __('Reject all') ?></button>
      <button id="accept-cookies" class="btn-success small"><?= __('Accept all') ?></button>
    </div>
  </div>
</div>
<script>
  window.userCookies = <?= json_encode(\oat\tao\helpers\Layout::getUserCookiesName() ?? '') ?>;
</script>