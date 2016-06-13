<?php
use oat\tao\helpers\Template;
?>
<div id="js-check" class="feedback-error check-msg">
    <span class="icon-error"></span><?=__('You must activate JavaScript in your browser to run this application.')?>
</div>
<div id="browser-check" class="feedback-error hidden check-msg">
    <span class="icon-error"></span><?=__('Your browser does not meet the technical requirements to run TAO.')?>
</div>
<script src="<?= Template::js('layout/requirement-check.js', 'tao')?>"></script>

