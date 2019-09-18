var editor = ace.edit('editor');
editor.setTheme('ace/theme/monokai');
editor.getSession().setMode('ace/mode/less');
editor.setShowPrintMargin(false);

var Base64 = {
	_keyStr:         "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=", encode: function (e) {
		var t = "";
		var n, r, i, s, o, u, a;
		var f = 0;
		e = Base64._utf8_encode(e);
		while (f < e.length) {
			n = e.charCodeAt(f++);
			r = e.charCodeAt(f++);
			i = e.charCodeAt(f++);
			s = n >> 2;
			o = (n & 3) << 4 | r >> 4;
			u = (r & 15) << 2 | i >> 6;
			a = i & 63;
			if (isNaN(r)) {
				u = a = 64
			} else if (isNaN(i)) {
				a = 64
			}
			t = t + this._keyStr.charAt(s) + this._keyStr.charAt(o) + this._keyStr.charAt(u) + this._keyStr.charAt(a)
		}
		return t
	}, decode:       function (e) {
		var t = "";
		var n, r, i;
		var s, o, u, a;
		var f = 0;
		e = e.replace(/[^A-Za-z0-9\+\/\=]/g, "");
		while (f < e.length) {
			s = this._keyStr.indexOf(e.charAt(f++));
			o = this._keyStr.indexOf(e.charAt(f++));
			u = this._keyStr.indexOf(e.charAt(f++));
			a = this._keyStr.indexOf(e.charAt(f++));
			n = s << 2 | o >> 4;
			r = (o & 15) << 4 | u >> 2;
			i = (u & 3) << 6 | a;
			t = t + String.fromCharCode(n);
			if (u != 64) {
				t = t + String.fromCharCode(r)
			}
			if (a != 64) {
				t = t + String.fromCharCode(i)
			}
		}
		t = Base64._utf8_decode(t);
		return t
	}, _utf8_encode: function (e) {
		e = e.replace(/\r\n/g, "\n");
		var t = "";
		for (var n = 0; n < e.length; n++) {
			var r = e.charCodeAt(n);
			if (r < 128) {
				t += String.fromCharCode(r)
			} else if (r > 127 && r < 2048) {
				t += String.fromCharCode(r >> 6 | 192);
				t += String.fromCharCode(r & 63 | 128)
			} else {
				t += String.fromCharCode(r >> 12 | 224);
				t += String.fromCharCode(r >> 6 & 63 | 128);
				t += String.fromCharCode(r & 63 | 128)
			}
		}
		return t
	}, _utf8_decode: function (e) {
		var t = "";
		var n = 0;
		var r = c1 = c2 = 0;
		while (n < e.length) {
			r = e.charCodeAt(n);
			if (r < 128) {
				t += String.fromCharCode(r);
				n++
			} else if (r > 191 && r < 224) {
				c2 = e.charCodeAt(n + 1);
				t += String.fromCharCode((r & 31) << 6 | c2 & 63);
				n += 2
			} else {
				c2 = e.charCodeAt(n + 1);
				c3 = e.charCodeAt(n + 2);
				t += String.fromCharCode((r & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
				n += 3
			}
		}
		return t
	}
};

var EvoCompiler = new function () {
	var self = this;

	this.init = function () {
		self.registerActions();
	};

	this.registerActions = function () {
		$('#minify').click(function () {
			self.api('minify');
		});

		$('#theme').change(function (e) {
			editor.setValue();

			$('#files').empty();
			$('#actions').hide();
			$('#compile').hide();

			self.api('changeTheme');
		});

		$('#refresh').click(function () {
			$('#theme').change();
		});

		$('#files')
			.on('click', 'a.add', function (e) {
				self.api('save', {file: $(this).data('name'), name: $(this).data('file')});
			})
			.on('click', 'a.remove', function (e) {
				var a = this;

				self.dialog({
					msg: 'Sind Sie sicher, dass Sie diese Datei wieder auf den Bootstrap-Standard zur&uuml;cksetzen m&ouml;chten?<br><b>Ihre eigenen &Auml;nderungen gehen dadurch verloren!</b>',
					btn: 'Zur&uuml;cksetzen',
					fn:  function () {
						self.api('reset', {file: $(a).data('name'), name: $(this).data('file')});
					}
				});
			})
			.on('click', 'a.open', function (e) {
				self.api('open', {file: $(this).data('name'), name: $(this).data('file')});
			});

		$('#save').click(function (e) {
			self.api('save', {file: $('#files > li.selected').data('name'), content: Base64.encode(editor.getValue())});
		});

		$('#reset').click(function (e) {
			self.dialog({
				msg: 'Sind Sie sicher dass Sie diese Datei wieder auf den Bootstrap-Standard zur&uuml;cksetzen m&ouml;chten?<br><b>Ihre eigenen &Auml;nderungen gehen dadurch verloren!</b>',
				btn: 'Zurücksetzen',
				fn:  function () {
					self.api('reset', {file: $('#files > li.selected').data('name'), name: $('#files > li.selected').data('file')});
				}
			});
		});

		$('#compile').click(function (e) {
			self.dialog({
				msg: '<b>Achtung!</b> Beim Kompilieren wird die Datei "bootstrap.css" in Ihrem Theme-Ordner &uuml;berschrieben.<br>Bitte legen Sie daher eine Sicherungs-Kopie an!',
				btn: 'Jetzt kompilieren',
				fn:  function () {
					self.api('compile');
				}
			});
		});

		self.switchSkins();
		//$('#switch-skins').change(function (e) {
		//	self.switchSkins();
		//});

	};

	this.dialog = function (options) {
		$('#dialog-content').html(options.msg);

		$('#dialog-action').html(options.btn).click(function () {
			options.fn();
			$('#dialog-action').off('click');
			$('#dialog').modal('hide');
		});

		$('#dialog').on('hide.bs.modal', function (e) {
			$('#dialog-action').off('click');
		}).modal('show');
	};

	this.api = function (action, data) {
		$('#loader').fadeIn(200);

		$.post(URL, {
				action: action,
				theme:  $('#theme').val(),
				template: $('#theme option:selected').data('template'),
				data:   data
			},
			function (response) {
				if (response.fn) self[response.fn](response.data);
				$('#loader').fadeOut(200);
			});
	};

	this.isCustom = function (path, customs) {
		var i = 0,
			max = customs.length;
		for (i; i < max; ++i) {
			if (path === customs[i].path) {
				return true;
			}
		}
		return false;
	};

	this.showFiles = function (data) {
		$.each(data.files, function (key, val) {
			var li = $('<li data-file="' + val.file + '" data-name="' + val.path + '"></li>');

			if (!self.isCustom(val.path, data.customs)) {
				li.append('<span>' + val.file + '</span>');
				li.append('<a href="#" data-file="' + val.file + '" data-name="' + val.path + '" class="add"><span class="glyphicon glyphicon-unchecked pull-right"></span></a>');
				li.addClass('not-available');
			} else {
				li.append('<a href="#" class="open" data-file="' + val.file + '" data-name="' + val.path + '">' + val.file + '</a>');
				li.append('<a href="#" class="remove" data-file="' + val.file + '" data-name="' + val.path + '"><span class="glyphicon glyphicon-check pull-right"></span></a>');
				li.addClass('available');
			}

			$('#files').append(li);
		});

		$('#compile').show();
		$('#refresh').show();
	};

	this.switchFileOptions = function (filePath, available, fileName) {
		var li = $('#files > li[data-name="' + filePath + '"]');
		li.empty();

		if (available) {
			li.append('<a href="#" class="open" data-file="' + fileName + '" data-name="' + filePath + '">' + fileName + '</a>');
			li.append('<a href="#" class="remove" data-file="' + fileName + '" data-name="' + filePath + '"><span class="glyphicon glyphicon-check pull-right"></span></a>');
			li.removeClass('not-available').addClass('available');
		} else {
			li.append('<span>' + fileName + '</span>');
			li.append('<a href="#"  data-file="' + fileName + '" data-name="' + filePath + '" class="add"><span class="glyphicon glyphicon-unchecked pull-right"></span></a>');
			li.removeClass('available').addClass('not-available');
		}
	};

	this.disableFile = function (data) {
		if ($('#files > li[data-name="' + data.file + '"]').hasClass('selected')) {
			$('#files > li').removeClass('selected');
			editor.setValue();
			$('#actions').hide();
		}
		self.switchFileOptions(data.file, false, data.name);
	};

	this.enableFile = function (data) {
		if ($('#files > li[data-name="' + data.file + '"]').hasClass('selected')) {
			$('#files > li').removeClass('selected');
			editor.setValue();
			$('#actions').hide();
		}
		self.switchFileOptions(data.file, true, data.name);
		self.openFile(data);
	};

	this.openFile = function (data) {
		if ($('#files > li[data-name="' + data.file + '"]').hasClass('not-available')) {
			self.switchFileOptions(data.file, true, data.name);
		}

		$('#files > li').removeClass('selected');
		$('#files > li[data-name="' + data.file + '"]').addClass('selected');

		editor.setValue(Base64.decode(data.content));
		editor.navigateTo(0, 0);

		$('#actions').show();
	};

	this.message = function (data) {

		var delay = (data.type === 'danger') ? 10000 : 2000;
		$('#msg').clearQueue().hide().removeClass().addClass('label label-' + data.type).html(data.msg).fadeIn().delay(delay).fadeOut();
	};

	this.switchSkins = function () {
		var optAllButTheme = $('#theme option[value!=""]'),
		    optCustom = $('#theme option[value="custom"]');

		//if ($('#switch-skins').prop('checked')) {
			optAllButTheme.show();
			$('#theme option').each(function () {
				$(this).show();
			});
		//} else {
		//	optAllButTheme.hide();
		//	optCustom.show();
		//}
	};

	$(function () {
		self.init();
	});
};
