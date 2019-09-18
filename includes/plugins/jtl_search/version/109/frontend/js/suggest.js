(function ($) {
	$.fn.jtl_search = function (options) {
		var MOVE_DOWN = 40,
			MOVE_LEFT = 37,
			MOVE_RIGHT = 39,
			MOVE_UP = 38,
			KEY_ENTER = 13,
			KEY_CANCEL = 27,
			top = 0,
			left = 0,
			result,
			_left,
			windowWidth = $(window).width(),
			input = $(this);// base

		if (input.length === 0) {
			return;
		}
		// result wrapper
		result = $('<div />').addClass('jtl_search_results dropdown-menu');
		$('body').append(result);
		switch (options.align) {
			default:
			case 'left':
				_left = input.offset().left;
				top = input.offset().top + input.outerHeight();
				left = ((_left + result.width()) > windowWidth) ? 0 : _left;
				break;
			case 'right':
				_left = input.offset().left + input.outerWidth() - result.outerWidth();
				top = input.offset().top + input.outerHeight();
				left = (_left > 0) ? _left : 0;
				break;
			case 'center':
				_left = input.offset().left + input.outerWidth() / 2 - result.outerWidth() / 2;
				top = input.offset().top + input.outerHeight();
				left = ((_left + result.width()) > windowWidth) ? 0 : _left;
				break;
		}

		result.css({
			top:  top,
			left: left
		});

		// clear
		input.unbind();
		input.val('');

		// rebind
		input.keyup(function (event) {
			handle(event.keyCode);
		});

		input.blur(function () {
			hideResults();
		});

		input.focus(function () {
			search();
		});

		/**
		 * @param key
		 */
		function handle(key) {
			if (key >= MOVE_LEFT && key <= MOVE_DOWN) {
				move(key);
			} else if (key == KEY_ENTER || key == KEY_CANCEL) {
				keyevt(key);
			} else {
				search();
			}
		}

		/**
		 * @param key
		 */
		function move(key) {
			if (!hasResults()) {
				return;
			}

			selectNext(key);
		}

		/**
		 * @param key
		 */
		function keyevt(key) {
			switch (key) {
				case KEY_ENTER:
					break;
				case KEY_CANCEL:
					input.trigger('blur');
					break;
			}
		}

		/**
		 * @param text
		 */
		function search(text) {
			if (input.val().length >= 3) {
				request(input.val());
			} else {
				result.hide();
			}
		}

		/**
		 * @param text
		 */
		function request(text) {
			$.ajax({
				type:    'POST',
				url:     options.url + 'suggest.php',
				data:    'k=' + encodeURI(text),
				success: function (data) {
					response(data);
				}
			});
		}

		/**
		 * @param data
		 */
		function response(data) {
			data = $(data);
			if (data.length > 0) {
				data.find('.result_row > a').each(function (idx, item) {
					$(item).click(function () {
						var url = $(item).attr('href'),
							query = $(item).attr('rel'),
							forward = $(item).attr('forward');

						if (!(forward == 1 && url.length > 0)) {
							input.val(query);
							input.closest('form').submit();
						} else {
							$.ajax({
								type:    'POST',
								url:     options.url + 'suggestforward.php',
								data:    'query=' + encodeURI(query),
								success: function (data) {
									window.location.href = url;
								}
							});
						}

						return false;
					});
				});
				result.html(data);
				result.stop().show();
			} else {
				result.html('');
				result.hide();
			}
		}

		/**
		 *
		 */
		function hideResults() {
			setTimeout(function () {
				result.hide();
			}, 250);
		}

		/**
		 * @return int
		 */
		function hasResults() {
			return result.children().length;
		}

		/**
		 * @return int
		 */
		function hasSelection() {
			return getSelected().length;
		}

		/**
		 * @return {*}
		 */
		function getSelected() {
			return result.find('.result_row > a.active:first');
		}

		/**
		 *
		 */
		function selectFirst() {
			var next = result.find('.result_row > a:first').addClass('active');
			input.val(next.attr('rel'));
		}

		/**
		 * @param key
		 */
		function selectNext(key) {
			var last,
				next = null;
			if (!hasSelection()) {
				selectFirst();
			} else {
				last = getSelected();

				switch (key) {
					case MOVE_DOWN:
						next = last.nextAll('a:first');
						break;
					case MOVE_UP:
						next = last.prevAll('a:first');
						break;
					case MOVE_RIGHT:
						next = result.find('.result_row:eq(1) > a:first');
						break;
					case MOVE_LEFT:
						next = result.find('.result_row:eq(0) > a:first');
						break;
				}

				if ($(next).length) {
					last.removeClass('active');
					next.addClass('active');
					input.val(next.attr('rel'));
				}
			}
		}
	};
})(jQuery);
