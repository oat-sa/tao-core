<div class="main-container">
    <?if(get_data('message')):?>
    <div id="info-box" class="ui-corner-all auto-highlight auto-hide">
    	<?=get_data('message')?>
    </div>
    <?endif?>

    <h2><?=get_data('formTitle')?></h2>
    <div class="form-content">
    	<?=get_data('myForm')?>
    </div>

    <script>
    $(function(){
    	$("#section-meta").empty();
    	<?if(get_data('reload')):?>
    		window.location.reload();
    	<?endif?>
    });
    </script>
</div>
