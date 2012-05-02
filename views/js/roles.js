(function($){
	var _dataFn = $.fn.data;
	$.fn.data = function(key, val){
		if (typeof val !== 'undefined'){
			$.expr.attrHandle[key] = function(elem){
				return $(elem).attr(key) || $(elem).data(key);
			};
		}
		return _dataFn.apply(this, arguments);
	};
})(jQuery);

$(function() {
	//Change role
	$('#roles').change(function() {
		loadModules($('#roles').val());
	});
});

function loadModules(role) {
	$.ajax({
		type: "POST",
		url: root_url + "/tao/Roles/getModules?extension=users",
		data: 'role='+role,
		dataType: 'json',
		async: false,
		success: function(data) {
			$('#aclModules ul.group-list').empty();
			$('#aclActions ul.group-list').empty();
			for (e in data) {
				ext = data[e];
				extra = '';
				if (ext['have-access']) extra = ' have-access';
				if (ext['have-allaccess']) extra = ' have-allaccess';
				$group = $('<li class="group expendable closed'+extra+'"><div class="group-title"><span class="title">'+ e +'</span><span class="selector all checkable" title="' + __('Add all') + '"></span></div><ul></ul></li>');
				$group.data('uri', ext.uri);
				if (ext['have-access']) $('.selector', $group).click(function (e) {e.stopPropagation();Access2All($(this))});
				else if (ext['have-allaccess']) $('.selector', $group).click(function (e) {e.stopPropagation();Access2None($(this))});
				else $('.selector', $group).click(function (e) {e.stopPropagation();Access2All($(this))});
				//Open/close group
				$('.group-title', $group).click(function(e) {
					if ($(this).parent().hasClass('open')) $(this).parent().removeClass('open').addClass('closed');
					else $(this).parent().removeClass('closed').addClass('open');
				});
				for (m in ext.modules) {
					mod = ext.modules[m];
					extra = '';
					if (mod['have-access']) extra = ' have-access';
					if (mod['have-allaccess']) extra = ' have-allaccess';
					//if (mod['bymodule']) extra += ' bymodule';
					$el = $('<li class="selectable'+extra+'"><span class="label">'+ m +'</span><span class="selector checkable"></span></li>');
					$el.data('uri', mod.uri);
					if (mod['have-access']) $('.selector', $el).click(function (e) {e.stopPropagation();Access2All($(this))});
					else if (mod['have-allaccess']) $('.selector', $el).click(function (e) {e.stopPropagation();Access2None($(this))});
					else $('.selector', $el).click(function (e) {e.stopPropagation();Access2All($(this))});
					//Select module
					$el.click(function() {
						$('#aclModules .selectable').removeClass('selected');
						$(this).addClass('selected');
						loadActions($('#roles').val(), $(this).data('uri'));
						loadAttachedModuleRoles($(this).data('uri'));
					});
					$el.appendTo($('ul', $group));
				}
				$group.appendTo($('#aclModules ul.group-list'));
			}
		}
	});
}

function loadActions(role, module) {
	$.ajax({
		type: "POST",
		url: root_url + "/tao/Roles/getActions?extension=users",
		data: 'role='+role+'&module='+module,
		dataType: 'json',
		async: false,
		success: function(data) {
			$('#aclActions ul.group-list').empty();
			nballaccess = 0;
			for (e in data.actions) {
				act = data.actions[e];
				extra = '';
				//if (data.bymodule) extra = ' have-heritedaccess';
				if (data.bymodule || act['have-allaccess'] || act['have-access']) {
					extra = ' have-allaccess';
					nballaccess++;
				}
				$el = $('<li class="selectable'+extra+'"><span class="label">'+ e +'</span><span class="selector checkable"></span></li>');
				$el.data('uri', act.uri);
				if ($el.hasClass('have-allaccess')) $('.selector', $el).click(function (e) {e.stopPropagation();Access2None($(this))});
				//else if ($el.hasClass('have-heritedaccess')) $('.selector', $el).click(function (e) {e.stopPropagation();Module2ActionAccess($(this))});
				else $('.selector', $el).click(function (e) {e.stopPropagation();Access2All($(this))});
				//Select action
				$el.click(function() {
					$('#aclActions .selectable').removeClass('selected');
					$(this).toggleClass('selected');
					loadAttachedActionRoles($(this).data('uri'));
				});
				$el.appendTo($('#aclActions ul.group-list'));
			}
			if (nballaccess == Object.keys(data.actions).length) {
				if (data.bymodule) extra = ' checked';
				$el = $('<li class="autoadd'+extra+'"><span class="label">'+ __('Auto. add new') +'</span><span class="selector checkable"></span></li>');
				$el.click(function() {
					if ($(this).hasClass('checked')) actOnUri($('#aclModules .selected').data('uri'), 'mod2acts', $('#roles').val());
					else actOnUri($('#aclModules .selected').data('uri'), 'acts2mod', $('#roles').val());
				});
				$el.appendTo($('#aclActions ul.group-list'));
			}
		}
	});
}

function loadAttachedModuleRoles(module) {
	$.ajax({
		type: "POST",
		url: root_url + "/tao/Roles/getAttachedModuleRoles?extension=users",
		data: 'module='+module,
		dataType: 'json',
		success: function(data) {
			addRolesAffected(data);
		}
	});
}

function loadAttachedActionRoles(action) {
	$.ajax({
		type: "POST",
		url: root_url + "/tao/Roles/getAttachedActionRoles?extension=users",
		data: 'action='+action,
		dataType: 'json',
		success: function(data) {
			addRolesAffected(data);
		}
	});
}

function addRolesAffected(data) {
	$('#aclRolesAffected ul.group-list').empty();
	for (e in data.roles) {
		role = data.roles[e];
		$el = $('<li class="selectable"><span class="label">'+ role.label +'</span><span class="selector checkable"></span></li>');
		$el.data('uri', role.uri);
		$el.appendTo($('#aclRolesAffected ul.group-list'));
	}
}

function Access2All(el) {
	//Act
	uri = $(el).closest('li').removeClass('have-access').addClass('have-allaccess').data('uri');
	actOnUri(uri, 'add', $('#roles').val());
	el.unbind('click').click(function (e) {e.stopPropagation();Access2None($(this))});
}

function Access2None(el) {
	//Act
	uri = $(el).closest('li').removeClass('have-access').removeClass('have-allaccess').data('uri');
	actOnUri(uri, 'remove', $('#roles').val());
	el.unbind('click').click(function (e) {e.stopPropagation();Access2All($(this))});
}

/*function Module2ActionAccess(el) {
	$li = $(el).closest('li');
	uri = $li.removeClass('have-heritedaccess').data('uri');
	actOnUri(uri, 'mod2act', $('#roles').val());
	el.unbind('click').click(function (e) {e.stopPropagation();Access2All($(this))});
}*/

function actOnUri(uri, act, role) {
  type = uri.split('#')[1].split('_')[0];
	action = '';
	switch (type) {
		case 'e':
			action = 'Extension';
			break;

		case 'm':
			action = 'Module';
			break;

		case 'a':
			action = 'Action';
			break;
	}
	switch (act) {
		case 'add':
			action = "add"+action+"Access";
			break;

		case 'remove':
			action = "remove"+action+"Access";
			break;

		case 'mod2act':
			action = "moduleTo"+action+"Access";
			break;

		case 'mod2acts':
			action = "moduleToActionsAccess";
			break;

		case 'acts2mod':
			action = "actionsToModuleAccess";
			break;
	}
	//Do act
	$.ajax({
		type: "POST",
		url: root_url + "/tao/Roles/" + action + "?extension=users",
		data: 'role='+role+'&uri='+uri,
		dataType: 'json',
		success: function(data) {
			open = $('#aclModules .group.expendable.open').index();
			$el = $('#aclModules .selected');
			if ($el.length) {
				uri = $el.data('uri');
				elidx = $el.index();
			} else elidx = 0;
			loadModules($('#roles').val());
			$('#aclModules .group.expendable:eq('+open+')').removeClass('closed').addClass('open');
			if (elidx) {
				$('#aclModules .open li:eq('+elidx+')').addClass('selected');
				loadActions($('#roles').val(), uri);
			}
		}
	});
}