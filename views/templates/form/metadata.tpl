<?if(get_data('metadata')):?>
<div id="meta-title" class="ui-widget-header ui-corner-top ui-state-default">
	<?=__('Meta Data')?>
</div>
<div id="meta-content" class="ui-widget-content">
	<table>
		<thead>
			<tr>
				<th class="first"><?=__('Date')?></th>
				<th><?=__('User')?></th>
				<th class="last"><?=__('Comment')?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="first"><?=get_data('date')?></td>
				<td><?=get_data('user')?></td>
				<td class="last">
					<span id="comment-field"><?=get_data('comment')?></span>
					<a href="#" id="comment-editor" title="<?=__('Edit Comment')?>">
						<img src="../views/img/edit.png" alt="<?=__('Edit Comment')?>" />
					</a>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!--  
<span id="comment-form-container-title" style="display:none;"><?=__("Edit item comment")?></span>
<div id="comment-form-container" style="display:none;">
	<form method="post" id="comment-form">
		<textarea name="comment" rows="4" cols="30"><?=get_data('comment')?></textarea><br />
		<input type="hidden" name="uri" value="<?=get_data('uri')?>" />
		<input type="hidden" name="classUri" value="<?=get_data('classUri')?>" />
		<input id="comment-saver" type="button" value="<?=__('Save')?>" />
	</form>
</div>
-->
<?endif?>