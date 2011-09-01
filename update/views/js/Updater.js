function UpdaterClass(tableElementId) {
	var self = this;
	this.$grid = $('#'+tableElementId);
	this.checkUpdateUrl = root_url + '/tao/Updater/checkUpdate';
	this.getUpdateDetailsUrl = root_url + '/tao/Updater/getUpdatesDetails';
	this.updateUrl = root_url + '/tao/Updater/update';
	this.availableUpdates = [];
	this.updatable = null;

	// Init
	this.init ();
}

UpdaterClass.prototype.init = function() {
	var self = this;
	var gridOptions = {
			datatype : "local",
			hidegrid : false,
			colNames : [ __('Version'), __('Comment')],
			colModel : [ {
				name : 'version',
				index : 'version',
				width : 75,
				align : "center",
				sortable : false
			}, {
				name : 'comment',
				index : 'comment',
				width : 450,
				sortable : false
			} ], 
			rowNum : 15,
			height : 'auto',
			autowidth : true,
			sortname : 'status',
			viewrecords : false,
			sortorder : "asc",
			caption : __("Available update")
		};

		//buil jqGrid:
		this.$grid.jqGrid(gridOptions);
}

UpdaterClass.prototype.checkUpdate = function() {
	var returnValue = false;
	var self = this;

	$.ajax({
		type : "POST",
		async : false,
		url : this.checkUpdateUrl,
		data : {},
		dataType : 'json',
		success : function(data) {
			self.updatable = data.updatable;
			returnValue = data.updatable;
		}
	});

	return returnValue;
}

UpdaterClass.prototype.update = function (onlySecurity){
	var self = this;
	var returnValue = false;
	var onlySecurity = typeof onlySecurity != 'undefined' ? onlySecurity : false;

	for (var i in this.availableUpdates){
		$.ajax({
			type : "POST",
			async : false,
			url : this.updateUrl,
			data : {
				version: this.availableUpdates[i].version
			},
			dataType : 'json',
			success : function(data) {
				if (typeof (data.updated) != 'undefined' && data.updated){
					self.availableUpdates[i]['version'] = self.availableUpdates[i]['version']+'<img src="'+root_url+'/tao/views/img/tick.png"/>';
					returnValue = self.setRowData(i, self.availableUpdates[i]);
				}
			}
		});
	}
	
	return returnValue;
}

UpdaterClass.prototype.addRowData = function(rowId, data) {
	this.$grid.jqGrid('addRowData', rowId, data);
	this.availableUpdates.push(data);
}

UpdaterClass.prototype.setRowData = function(rowId, data) {
	this.$grid.jqGrid('setRowData', rowId, data);
}

UpdaterClass.prototype.showUpdatesDetails = function() {
	var self = this;
	returnValue = [];
	
	if (this.updatable){
		$.ajax({
			type : "POST",
			async : false,
			url : this.getUpdateDetailsUrl,
			data : {},
			dataType : 'json',
			success : function(data) {
				returnValue = data;
				for (var i in data){
					self.addRowData(i, data[i]);
				}
			}
		});
	} else {
		this.$grid.html ('No Update available');
	}

	return returnValue;
}
