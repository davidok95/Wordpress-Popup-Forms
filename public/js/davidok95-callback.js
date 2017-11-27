(function($) { $(function() {
	$("#davidok95-callback").dialog({
		autoOpen: false,
		width: "310px",
	    'z-index': "1000",
		create: function( event, ui ) {
			$('.ui-dialog').wrap('<div class="davidok95-scope" style="position: relative; z-index: 1000"></div>');
			$('.ui-widget-overlay').wrap('<div class="davidok95-scope" style="position: relative; z-index: 1000"></div>');
		},
		open: function(event, ui) {
			$(".ui-widget-overlay").removeClass("ui-helper-hidden");
		},
		close: function( event, ui ) {
			$(".ui-widget-overlay").addClass("ui-helper-hidden");
		},
		buttons: [
			{
				text: "Отправить",
				icon: "ui-icon-check",
				click: function() {
					var error = false;
					var phone = $(".davidok95-callback__input-phone");
					var name = $(".davidok95-callback__input-name");
					if (phone.val() == '') {
						phone.addClass("ui-state-error");
						error = true;
					}
					else {
						phone.removeClass("ui-state-error");
					}
					if (name.val() == '') {
						name.addClass("ui-state-error");
						error = true;
					} else {
						name.removeClass("ui-state-error");
					}
					if (error)
						return false;

					var sendVars = {
						_ajax_nonce: my_ajax_obj.nonce, //nonce
						action: "davidok95_callback"        //action
					};
					var formVals = $(".davidok95-callback__form").serializeArray();
					for (var i = 0; i < formVals.length; ++i)
						sendVars[formVals[i].name] = formVals[i].value;

					$.post(my_ajax_obj.ajax_url, sendVars, function(data) {
						$("#davidok95-callback").dialog("close");
						$("#davidok95-callback-result").dialog("open");
					});
				}
			}
		],
	});

	$("#davidok95-callback-result").dialog({
		autoOpen: false,
	    'z-index': "1000",
		width: "310px",
		create: function( event, ui ) {
			$('#davidok95-callback-result').closest('.ui-dialog').wrap('<div class="davidok95-scope davidok95-scope-result" style="display: none; position: fixed; z-index: 1000; top: 0; left:0; width: 100%; height: 100%;"></div>');
			$('.ui-widget-overlay').wrap('<div class="davidok95-scope" style="position: relative; z-index: 1000"></div>');
		},
		open: function(event, ui) {
			$(".ui-widget-overlay").removeClass("ui-helper-hidden");
			$('.davidok95-scope-result').fadeIn(0);
		},
		close: function( event, ui ) {
			$(".ui-widget-overlay").addClass("ui-helper-hidden");
			$(".davidok95-scope").addClass("ui-helper-hidden");
		}
	});

	$(".davidok95-callback-open").on("click", function() {
		$("#davidok95-callback").dialog("open");

		if (my_ajax_obj.add_button_text_to_message == "Y") {
			$('#davidok95-callback [name=ADD_BUTTON_TEXT]').val($(this).text());
		}

		return false;
	});
}); })(jQuery);

