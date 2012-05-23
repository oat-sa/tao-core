		<div id="footer">
			<?if(get_data('user_lang')):?>
				<div id="section-lg">
					<?=__('Data language')?>: <strong><?=__(get_data('user_lang'))?></strong> 
				</div>
			<?endif?>			
                        <div class="ui-state-highlight ui-corner-all" style="width:500px; margin: 20px auto 20px auto; padding:5px;text-align:center; font-weight:bold;">
                                <img src="<?=TAOBASE_WWW?>img/warning.png" alt="!" /><br/><strong>DEMO Version : All data are removed regularly!</strong><br />
                                Please report bugs, ideas, comments, any feedback on the <a href="http://forge.tao.lu" target="_blank">TAO Forge</a>
                        </div>
			TAO<sup>&reg;</sup> - <?=date('Y')?> - A joint initiative of CRP Henri Tudor and the University of Luxembourg
		</div>
	</body>
</html>