<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error 500 - Internal Server Error</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>tao/views/css/tao-main-style.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>tao/views/css/error-pages.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>tao/views/css/error-page.css">
</head>

<body>
<div class="content-wrap">
    <header aria-label="Main Menu" class="dark-bar clearfix">
        <a href="<?= ROOT_URL ?>" class="lft" target="_blank">
            <img src="<?= ROOT_URL ?>tao/views/img/tao-logo.png" alt="TAO Logo" id="tao-main-logo">
        </a>
    </header>

    <div class="error-page">
        <div class="error-page__form">
            <img class="error-page__form__logo"
                 src="<?= ROOT_URL ?>tao/views/media/thunderbolt.svg"
                 alt="<?= __('Error') ?>"
            >
            <div>
                <h2 class="error-page__form__title"><?= __('Something unexpected happened.') ?></h2>
                <p class="error-page__form__message">
                    <?= __(
                        'It appears there were some issues and we were unable to process your request. You can contact your TAO administrator if needed. Or, alternatively:'
                    ) ?>
                </p>
                <div class="error-page__form__action-bar">
                    <a class="button" href="<?= ROOT_URL ?>"><?= __('go back to home page') ?></a>
                </div>
            </div>
        </div>
        <?php if (defined('DEBUG_MODE') && DEBUG_MODE === true): ?>
            <div class="section-container">

                <?php if (!empty($message)): ?>
                    <h2>Debug Message</h2>
                    <pre><?= $message ?></pre>
                <?php endif; ?>

                <?php if (!empty($trace)): ?>
                    <h2>Stack Trace</h2>
                    <pre><?= $trace ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer aria-label="About" class="dark-bar">
    © 2013 - <?= date('Y') ?> · <a href="http://taotesting.com" target="_blank">Open Assessment Technologies S.A.</a>
    · All rights reserved.
</footer>

</body>
</html>
