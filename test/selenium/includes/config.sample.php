<?php
// Testing accounts.
// Backend testing account.
define('TAO_SELENIUM_BACKEND_LOGIN', 'admin');
define('TAO_SELENIUM_BACKEND_PASSWORD', 'admin');

// Root URL of the TAO instance to be tested.
define('TAO_SELENIUM_ROOT_URL', 'http://taotransfer');

// Speed of the client-side testing. The value of this
// constant is expressed in milliseconds. It corresponds to
// the time the client has to wait between each Selenese command
// execution.
define('TAO_SELENIUM_SPEED', 500);

// Selenium RC server host name.
define('TAO_SELENIUM_HOST', 'localhost');

// Selenium RC server port.
define('TAO_SELENIUM_PORT', 4444);
?>