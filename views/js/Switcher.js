switcherClass.instances = [];

//create a grid, and update it after each hardification:
function switcherClass(tableElementId){
        
//        if(switcherClass.instances[tableElementId]){
//                return switcherClass.instances[tableElementId]
//        }
        
        this.$grid = $('#'+tableElementId);
        this.theData = [];
        this.currentIndex = -1;
        
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

switcherClass.prototype.init = function(forcedMode){
        
        var __this = this;
        var forced = false;
        if(forcedMode){
                //check if there is already a compilation running:
                for(i in this.theData){
                        if(this.theData[i].status == __('compiling')){
                                return false;
                        }
                }
                
                forced = true;
        }else{
                //check if already inited:
                if(this.theData.length){
                        return false;
                }
        }
        
        
        $.ajax({
                type: "POST",
                url: __this.getActionUrl('optimizeClasses'),
                data: {},
                dataType: 'json',
                success: function(r){
                        
                        var gridOptions = {
                                datatype: "local", 
                                colNames: [ __('Classes'), __('Status'), __('Action')], 
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
                                subGrid: true,
                                subGridModel:[
                                        {
                                                name: [__('related classes'), __('compiled instances')],
                                                width:[200, 50],
                                                align: ['left', 'center']
                                        }
                                ],
                                subGridRowExpanded: function(subgrid_id, row_id) {
                                        
                                        if(__this.theData[row_id].compilationResults != undefined){
                                                
                                                var localData = __this.theData[row_id].compilationResults.relatedClasses;
                                                
                                                var count = 0;
                                                for(val in localData){
                                                        count++;
                                                        break;
                                                }
                                                if(count==0) return false;
                                                
                                                var subgrid_table_id;
                                                subgrid_table_id = subgrid_id+"_t";
                                                $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table>");
                                                var $subGrid = $("#"+subgrid_table_id).jqGrid({
                                                        datatype: "local",
                                                        colNames: [__('Related Classes'), __('Compiled Instances')],
                                                        colModel: [
                                                        {
                                                                name:"class",
                                                                index:"class",
                                                                width:200,
                                                                key:true
                                                        },

                                                        {
                                                                name:"count",
                                                                index:"count",
                                                                width:50
                                                        }
                                                        ],
                                                        width:250,
                                                        height: '100%',
                                                        rowNum:20,
                                                        sortname: 'class',
                                                        sortorder: 'asc'
                                                });

                                                var i=0;
                                                for(className in localData){
                                                        $subGrid.jqGrid('addRowData', i, {'class':className, 'count':localData[className]});
                                                        i++;
                                                }
                                                
                                                
                                                
                                                return true;
                                        }else{
                                                return false;
                                        }
                                }
                        };

                        __this.$grid.jqGrid(gridOptions);
                        
			for(var j=0; j<r.length; j++){
				__this.setRowData(j, r[j]);
                                if(forced){
                                       __this.currentIndex = 0;  
                                }else{
                                      if(__this.currentIndex < 0 && __this.theData[j].status != __('compiled')){
                                              __this.currentIndex = j;
                                      }  
                                }
			}
                        
                        if(__this.currentIndex >= 0){
                                __this.startCompilation();
                        }

                                
                }
        });
        
        return true;
}

switcherClass.prototype.startCompilation = function(){
        this.nextStep();
}

switcherClass.prototype.addRowData = function(rowId, data){
        this.$grid.jqGrid('addRowData', rowId, data);
        this.theData[rowId] = data;
}

switcherClass.prototype.setRowData = function(rowId, data){
       
        if(typeof(this.theData[rowId]) != 'undefined'){
                this.$grid.jqGrid('setRowData', rowId, data);
        }else{
                this.$grid.jqGrid('addRowData', rowId, data);
        }
        this.theData[rowId] = data;
}

switcherClass.prototype.setCellData = function(rowId, colName, data){
        this.$grid.jqGrid('setCell', rowId, colName, data);
        this.theData[rowId][colName] = data;
}

switcherClass.prototype.addResultData = function(rowId, data){
        this.theData[rowId].compilationResults = null;
        this.theData[rowId].compilationResults = data;
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
                        __this.addResultData(rowId, r);
                        
                        if(r.success){
                                //update grid
                                var selfCount = r.count;
                                var relatedCount = 0;
                                for(relatedClassName in r.relatedClasses){
                                        relatedCount += parseInt(r.relatedClasses[relatedClassName]);
                                }
                                var count = ' (' + eval(selfCount+relatedCount) + ' ' + __('instances') + ': '+selfCount+' self / '+relatedCount+' related)';
                                __this.setCellData(rowId, 'status', __('compiled') + count);
                        }else{
                                __this.setCellData(rowId, 'status', __('fail'));
                        }
                        
                        __this.currentIndex ++;
                        __this.nextStep();
                }
        });
}

switcherClass.prototype.end = function(){
        alert("compilation completed");
}
