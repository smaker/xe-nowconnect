var nowconnectRefreshDuration = 12000;
var nowconnectTimer;
var nowconnectChecker = false;
var qTipOptions = {
	style : {
		width : {
			min : 300
		},
		background : '#333',
		color : '#FFF',
		border : {
			width: 1,
			radius: 8,
			color : '#666'
		}
	}
};
(function($){
	$(document).ready(function(){
		if(nowconnectRefresh) {
			$('#use_realtime').change(function(){
				if(this.checked) {
					nowconnectTimer = setInterval(refreshNowconnect, nowconnectRefreshDuration);
					$('div.ncxe .realtime label[for=use_realtime]').find('i.unchecked').remove();
					$('div.ncxe .realtime label[for=use_realtime]').prepend('<i class="checked"></i>');
				} else {
					clearInterval(nowconnectTimer);
					$('div.ncxe .realtime label[for=use_realtime]').find('i.checked').remove();
					$('div.ncxe .realtime label[for=use_realtime]').prepend('<i class="unchecked"></i>');
				}
			});
		}

		$('div.ncxe span.location').qtip(qTipOptions);
	});
})(jQuery);

function refreshNowconnect()
{
	if(jQuery('#use_realtime').is(':checked')) {
		var response_tags = new Array('error', 'message', 'html');
		show_waiting_message = false;
		jQuery.exec_xml('nowconnect', 'dispNowconnect', { 'mid' : current_mid }, callbackRefreshNowconnect, response_tags);
		show_waiting_message = true;
	} else {
		clearInterval(nowconnectTimer);
	}
}

function callbackRefreshNowconnect(response) {
	var error = parseInt(response.error);
	if(error) {
		clearInterval(nowconnectTimer);
	}

	var html = response.html;
	if(html) {
		jQuery('div.ncxe').html(html);
		jQuery('div.ncxe span.location').qtip(qTipOptions);

		clearInterval(nowconnectTimer);

		jQuery('#use_realtime').change(function(){
			if(this.checked) {
				nowconnectTimer = setInterval(refreshNowconnect, nowconnectRefreshDuration);
				jQuery('div.ncxe .realtime label[for=use_realtime]').find('i.unchecked').remove();
				jQuery('div.ncxe .realtime label[for=use_realtime]').prepend('<i class="checked"></i>');
			} else {
				clearInterval(nowconnectTimer);
				jQuery('div.ncxe .realtime label[for=use_realtime]').find('i.checked').remove();
				jQuery('div.ncxe .realtime label[for=use_realtime]').prepend('<i class="unchecked"></i>');
			}
		});

		jQuery('#use_realtime').click();
	}
}

