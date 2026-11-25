/* global jQuery, rtb_history */
(function ($) {
	'use strict';
	var opener;
	$(document).on('click', '.rtb-show-details', function () { opener = this; });
	$(document).on('rtb:details-close', '#rtb-details-modal', function () {
		$(this).find('.rtb-notification-history').each(function () { var request = $(this).data('request'); if (request) { request.abort(); } });
		if (opener) { opener.focus(); }
	});
	$(document).on('rtb:details-open', '#rtb-details-modal', function () {
		$(this).find('.rtb-notification-history').each(function () {
			var panel = $(this), state = { cursors: [], page: 0, next: '', generation: 0 };
			panel.data('historyState', state);
			load(panel, state, '', 0, false);
		});
	});
	function button(label, action, disabled) {
		return $('<button>', { type: 'button', 'class': 'button rtb-history-' + action, text: label, disabled: disabled });
	}
	function navigation(panel, state, failed) {
		var nav = $('<div>', { 'class': 'rtb-history-navigation' });
		nav.append(button(rtb_history.previous, 'previous', failed || state.page === 0), button(rtb_history.next, 'next', failed || !state.next), button(rtb_history.refresh, 'refresh', false));
		panel.append(nav);
	}
	function load(panel, state, cursor, page, focus) {
		var request = panel.data('request'), generation = ++state.generation;
		if (request) { request.abort(); }
		panel.attr('aria-busy', 'true').empty().append($('<p>', { role: 'status', 'aria-live': 'polite', text: rtb_history.loading }));
		request = $.ajax({ url: rtb_history.url, method: 'POST', dataType: 'json', cache: false, data: { action: 'rtb_get_notification_history', nonce: rtb_history.nonce, booking_id: panel.attr('data-booking-id'), cursor: cursor } });
		panel.data('request', request);
		request.done(function (response) {
			if (generation !== state.generation || !$.contains(document, panel[0])) { return; }
			if (!response.success || !response.data || !Array.isArray(response.data.rows)) { fail(); return; }
			var data = response.data;
			state.page = page; state.next = data.next; state.cursors[page] = data.current;
			panel.empty();
			if (!data.rows.length) { panel.append($('<p>', { role: 'status', text: rtb_history.empty })); }
			else {
				var table = $('<table>', { 'class': 'widefat striped rtb-history-table' }), head = $('<tr>'), body = $('<tbody>');
				table.append($('<caption>', { 'class': 'screen-reader-text', text: rtb_history.caption }));
				rtb_history.headers.forEach(function (label) { head.append($('<th>', { scope: 'col', text: label })); });
				table.append($('<thead>').append(head));
				data.rows.forEach(function (row) {
					var tr = $('<tr>');
					['date', 'trigger', 'type', 'target', 'outcome'].forEach(function (key, index) {
						var cell = $('<td>', { 'data-label': rtb_history.headers[index] }).append($('<span>', { text: row[key] }));
						if (key === 'outcome') {
							var details = $('<details>').append($('<summary>', { text: rtb_history.details }));
							details.append($('<p>', { text: row.reason }), $('<p>', { text: rtb_history.id + ': ' + row.id }), $('<p>', { text: rtb_history.notification_id + ': ' + row.notification_id }));
							if (row.trigger_identifier) { details.append($('<p>', { text: row.trigger_identifier })); }
							cell.append(details);
						}
						tr.append(cell);
					});
					body.append(tr);
				});
				panel.append(table.append(body));
			}
			panel.append($('<p>', { role: 'status', 'aria-live': 'polite', text: (page * 20 + (data.rows.length ? 1 : 0)) + '–' + (page * 20 + data.rows.length) + ' / ' + data.total }));
			navigation(panel, state, false);
			if (focus) { panel.find('.rtb-history-refresh').trigger('focus'); }
		}).fail(function (xhr, status) { if (status !== 'abort' && generation === state.generation) { fail(); } }).always(function () { if (generation === state.generation) { panel.attr('aria-busy', 'false'); } });
		function fail() { panel.empty().append($('<p>', { role: 'alert', text: rtb_history.error })); navigation(panel, state, true); }
	}
	$(document).on('click', '#rtb-details-modal .rtb-history-navigation button', function () {
		var panel = $(this).closest('.rtb-notification-history'), state = panel.data('historyState');
		if ($(this).hasClass('rtb-history-next')) { load(panel, state, state.next, state.page + 1, true); }
		else if ($(this).hasClass('rtb-history-previous')) { load(panel, state, state.cursors[state.page - 1], state.page - 1, true); }
		else { state.cursors = []; load(panel, state, '', 0, true); }
	});
})(jQuery);
