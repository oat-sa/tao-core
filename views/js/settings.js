$(function(){
	$("#settings-loader").click(function(){
		_load(getMainContainerSelector(), this.href, {});
		return false;
	});
})
