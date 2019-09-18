/**
 * @param str
 * @returns {string}
 */
function urldecode(str) {
	return decodeURIComponent((str + '').replace(/\+/g, '%20'));
}

jQuery(document).ready(function ($) {
	var saveButton = $('#tpl-configurator-export'),
	    submitButton = $('#tpl-configurator-submit');
	submitButton.click(function (e) {
		submitButton.toggleClass('disabled');
		e.preventDefault();
		var data = $('#tpl-configurator-edit-form').serialize().split('&'),
			bit,
			option,
			optionName,
			value,
			i,
			varsObject = {};
		for (i = 0; i < data.length; i++) {
			bit = data[i].split('=');
			option = bit[0];
			value = bit[1];
			if (option.indexOf('color-') !== -1) {
				if (value.length === 3 || value.length === 6) {
					value = '#' + value;
				} else {
					value = urldecode(value);
				}
			} else if (option.indexOf('px-') !== -1) {
				option = option.replace('px-', '');
				value = value + 'px';
			} else if (option.indexOf('ms-') !== -1) {
				option = option.replace('ms-', '');
				value = value + 'ms';
			} else {
				value = urldecode(value);
			}
			if (option.indexOf('input-') === 0) {
				optionName = '@' + option.substr(6);
				varsObject[optionName] = value;
			}
		}
		varsObject['@fa-font-path'] = '"/templates/Evo/fonts/"';
		less.modifyVars(varsObject).then(function () {
			submitButton.toggleClass('disabled');
			$('body').removeClass('loading');
		}, function () {
			$('body').removeClass('loading');
		});
	});

	saveButton.click(function (e) {
		e.preventDefault();
		saveButton.toggleClass('disabled');
		saveButton.find('i').removeClass('fa-save').addClass('fa-spin fa-spinner');
		var form = $('#tpl-configurator-edit-form'),
			url  = form.attr('action'),
			data = form.serialize(),
			request = $.ajax({
				url:     url,
				type:    'POST',
				data:    data,
				success: function (a) {
					if (typeof a.ok !== 'undefined') {
						var type = (a.ok === true) ? 'success' : 'danger',
						    msg = '<div class="alert alert-' + type + '">' + a.msg + '</div>';
						$('#tpl-config-msg-placeholder').html(msg);
					}
				},
				error: function () {
					$('#tpl-config-msg-placeholder').html('<div class="alert alert-danger">An unknown error occured.</div>');
				},
				complete: function () {
					saveButton.toggleClass('disabled');
					saveButton.find('i').addClass('fa-save').removeClass('fa-spin fa-spinner');
				}
			});
	});

	$('.colorpicker-element').colorpicker({component: '.colorpicker-component'});

	//$('.colorpicker-activator').ColorPicker({
	//	onSubmit:     function (hsb, hex, rgb, el) {
	//		var target = $(el).data('target');
	//		target.val('#' + hex);
	//		$(el).ColorPickerHide();
	//	},
	//	onBeforeShow: function () {
	//		$(this).ColorPickerSetColor(this.value);
	//	}
	//}).bind('keyup', function () {
	//	$(this).ColorPickerSetColor(this.value);
	//});
	//submitButton.click();
});