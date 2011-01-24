<?php
   /*
    * @todo implement this script in a real update process
    * 1. go to update dir
    * 2. checkout the right version id
    * 3. execute the php scripts and run the sql instructions from the last version to the current version
    */
require_once dirname(__FILE__).'/../../includes/raw_start.php';

define('UPDATE_URI_SOURCE', true);
define('UPDATE_URI_DB', true);

include_once dirname(__FILE__).'/../update/1.1/04_uri.php';
?>
