<?php
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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */
require_once '../../vendor/autoload.php';

if(tao_install_utils_System::isTAOInstalled()){
    $bootStrap = new oat\tao\model\mvc\Bootstrap(__DIR__.'/../../config/generis.conf.php');
    if(!DEBUG_MODE){
        header("location:production.html");
        die();
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>TAO Installation</title>
    <link rel="stylesheet" type="text/css" href="../views/css/reset.css" />
    <link rel="stylesheet" type="text/css" href="css/installation_main.css" />
    <link rel="stylesheet" type="text/css" href="../views/css/custom-theme/jquery-ui-1.9.2.custom.css" />

    <script type="text/javascript" src="../views/js/lib/require.js" data-main="js/install"></script>
</head>

<body>
<!-- Screen Shield -->
<div id="screenShield"></div>

<!-- Support Tab -->
<p id="supportTab" class="ui-corner-tl ui-corner-bl"></p>

<!-- Help Popup -->
<div id="mainGenericPopup">
    <div id="genericPopupOverlay" class="ui-overlay"><div class="ui-widget-overlay  ui-corner-all"></div></div>
    <div id="genericPopup" class="ui-widget ui-widget-content ui-corner-all">
        <div id="genericPopupClose" class="js-genericPopupClose" title="Close">X</div>
        <h4></h4>
        <div id="genericPopupContent">

        </div>
        <button class="js-genericPopupClose button green" type="button">OK</button>
    </div>
</div>

<!-- Support Popup -->
<div id="mainSupportPopup">
    <div id="supportPopupOverlay" class="ui-overlay"><div class="ui-widget-overlay  ui-corner-all"></div></div>
    <div id="supportPopup" class="ui-widget ui-widget-content ui-corner-all">
        <div id="supportPopupClose" title="Close">X</div>
        <div id="supportPopupContent">
            <h5>Are you looking for support?</h5>
        </div>
    </div>
</div>

</body>

</html>



