<?php
/*  
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>

<input type="radio" name="rdftpl_mode" id="rdftpl_mode_namespaces" value="namespaces" /><label for="rdftpl_mode_namespaces" class="elt_desc"><?=__('Namespaces')?></label><br />
<div id="rdftpl_mode_container_namespaces" class="rdftpl_mode_container" style="display:none;">
	<span class="form-elt-container"><?=__('Select')?> : 
		<a href="#" id="ns_filter_all" title="<?=__('All (the complete TAO Module)')?>" ><?=__('All')?></a>
		<a href="#" id="ns_filter_current" title="<?=__('Current (the current extension, the local data and their dependancies)')?>"><?=__('Current')?></a>
		<a href="#" id="ns_filter_local" title="<?=__('Local Data (the local namespace containing only the data inserted by the users)')?>"><?=__('Local')?></a>
	</span>
	<table class="form-elt-container">
		<tbody>
	<?foreach($namespaces as $ns):?>
			<tr>
				<td>
					<input 
						type="checkbox" 
						name="rdftpl_ns_<?=$ns->getModelId()?>"  
						id="rdftpl_ns_<?=$ns->getModelId()?>"
					<?if($localNs == $ns->getModelId()):?>
						class="rdftpl_ns rdftpl_ns_local" 
					<?elseif(in_array(str_replace('#', '', $ns), $currentNs)):?>
						class="rdftpl_ns rdftpl_ns_current" 
					<?else:?>
						class="rdftpl_ns" 
					<?endif?>
						
					/>
				</td>
				<td><?=(string)$ns?></td>
			</tr>
	<?endforeach?>
			
		</tbody>
	</table>
	
</div>
<?if(count($instances) > 0):?>
<br />
<input type="radio" name="rdftpl_mode" id="rdftpl_mode_instances" value="instances" /><label for="rdftpl_mode_instances" class="elt_desc"><?=__('Instances')?></label>
<div id="rdftpl_mode_container_instances" class="rdftpl_mode_container" style="display:none;">
	<?foreach($instances as $uri => $label):?>
		<input type="checkbox" name="rdftpl_instance_<?=$uri?>" id="rdftpl_instance_<?=$uri?>" /><label for="rdftpl_instance_<?=$uri?>"><?=$label?></label><br />
	<?endforeach?>
	<span class="checker-container">
		<a href="#" class="box-checker" id="rdftpl_instance_checker"><?=__('Check all')?></a>
	</span>
</div>
<?endif?>

<script type="text/javascript">
$(document).ready(function(){

	$(":radio[name='rdftpl_mode']").change(function(){
		if($(this).attr('checked')){
			$('.rdftpl_mode_container').hide();
			$('#rdftpl_mode_container_' + this.id.replace('rdftpl_mode_', '')).show();
		}
	});

	<?if(count($instances) == 0):?>
		$('#rdftpl_mode_namespaces').attr('checked', 'checked');
		$('#rdftpl_mode_container_namespaces').show();
	<?endif?>
	
	$('#ns_filter_all').click(function(){
		$('.rdftpl_ns').attr('checked', 'checked');
	});
	$('#ns_filter_current').click(function(){
		$('.rdftpl_ns').removeAttr('checked');
		$('.rdftpl_ns_current').attr('checked', 'checked');
		$('.rdftpl_ns_local').attr('checked', 'checked');
	});
	$('#ns_filter_local').click(function(){
		$('.rdftpl_ns').removeAttr('checked');
		$('.rdftpl_ns_local').attr('checked', 'checked');
	});

});
</script>