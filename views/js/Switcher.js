switcherClass.instances = [];

//create a grid, and update it after each hardification:
function switcherClass(tableElementId){
        
//        if(switcherClass.instances[tableElementId]){
//                return switcherClass.instances[tableElementId]
//        }
        
        this.$grid = $('#'+tableElementId);
        this.theData = [];
        this.currentIndex = 0;
        
        switcherClass.instances[tableElementId] = this;
}

switcherClass.prototype.getActionUrl = function(action){
        var url = root_url;
        if(typeof(ctx_extension) != 'undefined'){
                url += '/' + ctx_extension + '/Settings/' + action;
        }else{
                url += '/tao/Settings/' + action;
        }
        
        return url;
}

switcherClass.prototype.init = function(){
        var __this = this;
        
        $.ajax({
                type: "POST",
                url: __this.getActionUrl('optimizeClasses'),
                data: {},
                dataType: 'json',
                success: function(r){
                        
                        var gridOptions = {
                                url: "/tao.settings.save.action",
                                editData: {responseId:'empty'},
                                datatype: "local", 
                                colNames: [ __('Class'), __('Status'), __('Action')], 
                                colModel: [ 
                                        {name:'class',index:'class'},
                                        {name:'status',index:'status', align:"center"}, 
                                        {name:'actions',index:'actions', align:"center", sortable: false}
                                ], 
                                rowNum:10, 
                                height: 'auto', 
                                autowidth:true,
                                sortname: 'status', 
                                viewrecords: false, 
                                sortorder: "asc", 
                                caption: __("Optimizable Classes"),
                                gridComplete: function(){}
                        };

                        __this.$grid.jqGrid(gridOptions);
                        
//                        __this.theData = r;
                        
                        
			for(var j=0; j<r.length; j++){
				__this.addRowData(j, r[j]);
                                if(!__this.currentIndex && __this.theData[j]['status'] == __('compiled')){
                                        __this.currentIndex = j;
                                }
			}
                        
                        __this.startCompilation();
                                
                }
        });    
}

switcherClass.prototype.startCompilation = function(){
        this.nextStep();
}

switcherClass.prototype.addRowData = function(rowId, data){
        this.$grid.jqGrid('addRowData', rowId, data);
        this.theData[rowId] = data;
}

switcherClass.prototype.setRowData = function(rowId, data){
        this.$grid.jqGrid('setRowData', rowId, data);
        this.theData[rowId] = data;
}

switcherClass.prototype.setCellData = function(rowId, colName, data){
        this.$grid.jqGrid('setCell', rowId, colName, data);
        this.theData[rowId][colName] = data;
}

switcherClass.prototype.getRowIdByUri = function(classUri){
        var returnValue = -1;
        
        for(rowId in this.theData){
                
                if(this.theData[rowId].classUri == classUri){
                        returnValue = rowId;
                        break;
                }
        }
        
        return returnValue;
}

switcherClass.prototype.nextStep = function(){
        
        
        if(this.currentIndex < this.theData.length){
                
		this.compileClass(this.theData[this.currentIndex].classUri);
		this.currentIndex ++;
                
	}else{
		this.end();
	}
}

switcherClass.prototype.compileClass = function(classUri){
        var __this = this;
        var rowId = this.getRowIdByUri(classUri);
        
        this.setCellData(rowId, 'status', __('compiling'));
        
        $.ajax({
		type: "POST",
		url: __this.getActionUrl('compileClass'),
		data: {classUri : classUri, options: ''},
		dataType: "json",
		success: function(r){
                        __this.theData[rowId].compilationResults = r;
                        
                        if(r.success){
                                //update grid
                                var count = ' (' + r.count + ' ' + __('instances') + ')';
                                __this.setCellData(rowId, 'status', __('compiled')+count);
                        }else{
                                __this.setCellData(rowId, 'status', __('fail'));
                        }
                        
                        __this.nextStep();
                }
        });
}

switcherClass.prototype.end = function(){
        alert("compilation completed");
}

//switcherClass.prototype.