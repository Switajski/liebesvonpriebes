$(document).ready(function () {
	var idListSmarty = $('.codemirror.smarty'),
		idListHTML = $('.codemirror.html'),
		editorsSmarty = [],
		editorsHtml = [];
	idListHTML.each(function (idx, elem) {
		if (elem.id && elem.id.length > 0) {
			editorsSmarty[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
				lineNumbers: true,
				mode:        'htmlmixed',
				extraKeys:   {
					'Ctrl-Space': function (cm) {
						cm.setOption('fullScreen', !cm.getOption('fullScreen'));
					},
					'Esc':        function (cm) {
						if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
					}
				},
			});
		}
	});
	idListSmarty.each(function (idx, elem) {
		if (elem.id && elem.id.length > 0) {
			editorsHtml[idx] = CodeMirror.fromTextArea(document.getElementById(elem.id), {
				lineNumbers: true,
				mode:        'smartymixed',
				extraKeys:   {
					'Ctrl-Space': function (cm) {
						cm.setOption('fullScreen', !cm.getOption('fullScreen'));
					},
					'Esc':        function (cm) {
						if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
					}
				},
			});
		}
	});

});