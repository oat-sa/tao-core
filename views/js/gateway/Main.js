
var TaoGateway = {};

/**
 *
 * @param url
 * @param classUri
 * @return
 */
TaoGateway.addInstance = function (url, classUri){
	$.ajax({
		url: url,
		type: "POST",
		data: {classUri: classUri, type: 'instance'},
		dataType: 'json',
		success: function(response){
			if (response.uri) {
				eventMgr.trigger('instanceAdded', [response]);
			}
		}
	});
}
