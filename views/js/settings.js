$(function(){
	$("#settings-loader").click(function(){
		_load(getMainContainerSelector(tabs), this.href, {});
		return false;
	});
})
