var nowconnectAddonTimer; 

function refresh_nowconnect_user_count(selector, refreshDuration) {
	nowconnectAddonTimer = setInterval(refreshNowconnectAddon, nowconnectRefreshDuration);
}

function refreshNowconnect()
{
		var response_tags = new Array('error', 'message', 'count');
		show_waiting_message = false;
		jQuery.exec_xml('nowconnect', 'getNowconnectUserCount', callbackRefreshNowconnectAddon, response_tags);
		show_waiting_message = true;
}


function callbackRefreshNowconnectAddon(response) {

	var error = parseInt(response.error);
	if(error) {
		clearInterval(nowconnectAddonTimer);
	}

	var count = parseInt(response.count);
	var selector = response.selector;

	jQuery(selector).text(count);
}