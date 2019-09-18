(function($){
	$.fn.jtl_search = function(options) {
		
		var MOVE_DOWN = 40;
		var MOVE_LEFT = 37;
		var MOVE_RIGHT = 39;
		var MOVE_UP = 38;
		var KEY_ENTER = 13;
		var KEY_CANCEL = 27;
		
		// base
		var input = $(this);
      
      if (input.length == 0)
         return;
		
		// result wrapper		
		var result = $('<div />').addClass('jtl_search_results');
		
		var top = 0;
		var left = 0;
		
		$('body').append(result);

		switch (options.align) {
			default:
			case 'left':
				top = input.offset().top + input.outerHeight();
				left = input.offset().left;
				break;
			case 'right':
				top = input.offset().top + input.outerHeight();
				left = input.offset().left + input.outerWidth() - result.outerWidth();
				break;
			case 'center':
				top = input.offset().top + input.outerHeight();
				left = input.offset().left + input.outerWidth() / 2 - result.outerWidth() / 2;
				break;
		}
		
		result.css({
			top : top,
			left : left
		});
		
		// clear
		input.unbind();
		input.val('');
		
		// rebind
		input.keyup(function(event) {
			handle(event.keyCode);
		});
		
		input.blur(function() {
			hideResults();
		});
		
		input.focus(function() {
			search();
		});
		
		function handle(key) {
			if (key >= MOVE_LEFT && key <= MOVE_DOWN)
				move(key);
			else if (key == KEY_ENTER || key == KEY_CANCEL)
				keyevt(key);
			else {
				search();
			}
		}
		
		function move(key) {
			if (!hasResults())
				return;

			selectNext(key);
		}
		
		function keyevt(key) {
			switch (key) {
				case KEY_ENTER:
					break;
				case KEY_CANCEL:
				{
					input.trigger('blur');
					break;
				}
			}
		}
		
		function search(text) {
			if (input.val().length >= 3)
				request(input.val());
			else
				result.hide();
		}
		
		function request(text) {
			$.ajax({
				type: 'POST',
				url: options.url + 'suggest.php',
				data: 'k=' + encodeURI(text),
				success: function(data) {
					response(data);
				}
			});
		}
		
		function response(data) {
			data = $(data);
			if (data.length > 0) {
				data.find('.result_row > a').each(function(idx, item) {
					$(item).click(function() {
                  var url = $(item).attr('href');
                  var query = $(item).attr('rel');
                  var forward = $(item).attr('forward');
                  
                  if (!(forward == 1 && url.length > 0))
                  {
                     input.val(query);
                     input.closest('form').submit();
                     
                  }
                  else
                  {
                     $.ajax({
                        type: 'POST',
                        url: options.url + 'suggestforward.php',
                        data: 'query=' + encodeURI(query),
                        success: function(data) {
                           window.location.href = url;
                        }
                     });
                  }
                  
                  return false;
					});
				});
				
				result.html(data);
				result.stop().show();
			}
			else {
				result.html('');
				result.hide();
			}
		}
		
		function hideResults() {
			setTimeout(function() {
				result.hide();
			} , 250);
		}
		
		function hasResults() {
			return result.children().length;
		}
		
		function hasSelection() {
			return getSelected().length;
		}
		
		function getSelected() {
			return result.find('.result_row > a.active:first');
		}
		
		function selectFirst() {
			var next = result.find('.result_row > a:first').addClass('active');
         input.val(next.attr('rel'));
		}
		
		function selectNext(key) {
			if (!hasSelection())
				selectFirst();
			else {
				var last = getSelected();
				var next = null;
				
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
