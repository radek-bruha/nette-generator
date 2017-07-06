$(document).ready(function() {
	$.nette.ext('all', {
		init: function() {
			this.spinner = this.createSpinner();
			this.spinner.appendTo('html');
		},
		before: function(xhr, settings) {
			if(!settings.nette) return;
			var question = settings.nette.el.data('confirm');
			if(question) return confirm(question);
		},
		start: function() {
			this.spinner.show(0);
		},
		complete: function() {
			this.spinner.hide(0);
			$('form#frm-control div.where input[formnovalidate]:not(:last)').after('<br/>');
			$('form#frm-control div.order input[formnovalidate]:not(:last)').after('<br/>');
		}
	}, {
		createSpinner: function() {
			return $('<div>', { class: 'ajax-loading', css: { display: 'none' } });
		}
	});

	$.nette.init();

	$('form#frm-control').on('keydown', function(e) {
		if(e.which === 13) {
			e.preventDefault();
			$('input[name=_submit]').click();
		}
	});

	$('div.modal-window input:not([readonly]):enabled:visible:first').focus();

	$(":file").filestyle({ buttonName: 'btn-primary', buttonBefore: true });
	$('.input-group.date.date-full').datetimepicker({ locale: 'cs', minDate: '1/1/1800', maxDate: '1/1/2200', useCurrent: true, format: 'YYYY-MM-DD HH:mm:ss', sideBySide: true });
	$('.input-group.date.date-only').datetimepicker({ locale: 'cs', minDate: '1/1/1800', maxDate: '1/1/2200', useCurrent: true, format: 'YYYY-MM-DD' });
	$('.input-group.date.time-only').datetimepicker({ locale: 'cs', minDate: '1/1/1800', maxDate: '1/1/2200', useCurrent: true, format: 'HH:mm:ss' });
});