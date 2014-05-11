var nowconnectRetry = 0;
var nowconnectChecker = false;

(function($){
	var ncxe = {
		/* Variables */
		'nowconnectTimer' : null,
		'nowconnectStopTimer' : null,
		'realtimeStatus' : false,
		/* Callback */
		'callbackRefreshNowconnect' : function (response) {
			var error = parseInt(response.error);
			if(error) {
				alert(response.message);
				nowconnectRetry++;

				if(nowconnectRetry > 5)
				{
					var $lbl = $('#lblRt');
					$lbl.click();
				}

				clearInterval(ncxe.nowconnectTimer);
			}

			var html = response.html;
			if(html) {
				$('div.ncxe').html(html);
				if(ncxe.realtimeStatus)
				{
					var $lbl = $('#lblRt');
					$lbl.click();
				}

				clearInterval(ncxe.nowconnectTimer);

				Cufon.replace('.nowconnectList caption .title .brand', { fontFamily: 'NanumGothic' } );
			}
		},
		/* Timer Callback */
		'refreshNowconnect' : function() {
			if($('#chkRt').is(':checked')) {
				var response_tags = new Array('error', 'message', 'html');
				show_waiting_message = false;
				exec_xml('nowconnect', 'dispNowconnect', { 'mid' : current_mid, 'page' : current_page }, ncxe.callbackRefreshNowconnect, response_tags);
				show_waiting_message = true;
				ncxe.nowconnectStopTimer = setTimeout(ncxe.refreshStop, 600000);
			} else {
				clearInterval(ncxe.nowconnectTimer);
				clearTimeout(ncxe.nowconnectStopTimer);
			}
		},
		'refreshStop': function() {
			ncxe
				.setUnchecked()
				.deactiveRealtimeUpdate();
		},
		/* Methods */
		'isActiveRealtimeUpdate' : function() {
			return $.cookie('ncxeRt') == 'y';
		},
		'activeRealtimeUpdate' : function() {
			ncxe.realtimeStatus = true;
			$.cookie('ncxeRt', 'y', { expires : 7 });
			return this;
		},
		'deactiveRealtimeUpdate' : function() {
			ncxe.realtimeStatus = false;
			$.cookie('ncxeRt', 'n', { expires : 7 });
			clearInterval(ncxe.nowconnectTimer);
			return this;
		},
		'setChecked': function() {
			var $lbl = $('#lblRt');

			$lbl
				.find('i.fa-square-o')
					.removeClass('fa-square-o')
					.addClass('fa-check-square-o');
			return this;
		},
		'setUnchecked': function() {
			var $lbl = $('#lblRt');

			$lbl
				.find('i.fa-check-square-o')
					.removeClass('fa-check-square-o')
					.addClass('fa-square-o');
			return this;
		},
		'init': function() {
			if(typeof(nowconnectRefresh) == 'undefined' || !nowconnectRefresh) return false;
			if(typeof($.cookie('ncxeRt')) == 'undefined')
			{
				ncxe.deactiveRealtimeUpdate();
			}
			else
			{
				if(ncxe.isActiveRealtimeUpdate())
				{
					ncxe.activeRealtimeUpdate();
					ncxe.nowconnectTimer = setInterval(this.refreshNowconnect, nowconnectRefreshDuration);
					ncxe.setChecked();
					$('#chkRt').prop('checked', true);
				}
			}
		}
	};
	$(document).ready(function()
	{
			$(document).on('change', '#chkRt', function(){
				if(this.checked) {
					ncxe.nowconnectTimer = setInterval(ncxe.refreshNowconnect, nowconnectRefreshDuration);
					ncxe
						.activeRealtimeUpdate()
						.setChecked();
				} else {
					ncxe
						.deactiveRealtimeUpdate()
						.setUnchecked();
				}
			});

			ncxe.init();
	});
	$(window).load(function(){
		Cufon.replace('.nowconnectList caption .title .brand', { fontFamily: 'NanumGothic' } );
	});
})(jQuery);