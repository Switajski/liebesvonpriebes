/*
 *  get article list by search key
 */

jQuery.fn.center = function () {
	this.css('position', 'absolute');
	this.css('top', ( $(window).height() - this.height() ) / 2 + $(window).scrollTop() + 'px');
	this.css('left', ( $(window).width() - this.width() ) / 2 + $(window).scrollLeft() + 'px');
	return this;
}

jQuery.fn.set_search = function(type, assign) {
	this.click(function() {
		$('#ajax_list_picker.' + type).center().fadeIn(850);
		$('#' + type + '_list_input').focus().val('');
		// empty list views
		set_selected_list(type, $(assign).val());
		$('select[name="' + type + '_list_found"]').empty();
		// set event handler
		if (!$(this).hasClass('init')) {
			$('#' + type + '_list_input').keyup(function() {
				search_list(type, $('#' + type + '_list_input').val());
			});
			$('#' + type + '_list_save').click(function() {
				// save
				var list = '';
				$('select[name="' + type + '_list_selected"] option').each(function(i) {
					list += $(this).val() + ';';
				});
				$(assign).val(list);
				$('#' + type + '_list_cancel').trigger('click');
				return false;
			});
			$('#' + type + '_list_cancel').click(function() {
				// cancel
				$('#ajax_list_picker.' + type).fadeOut(500);
				return false;
			});
			// mark as initialized
			$(this).addClass('init');
		}
		return false;
	});
}

function set_selected_list(type, list) {
	// selected articles  
	myCallback = xajax.callback.create();
	myCallback.onComplete = function(obj) {
		// remove last result set
		$('select[name="' + type + '_list_selected"]').empty();
		// selected list
		$.each(obj.context.selected_arr, function(k, v) {
			$('select[name="' + type + '_list_selected"]').append(
				$('<option></option>').val(v.cBase).html(v.cBase + ' | ' + v.cName).dblclick(function() {
					$(this).remove();
				})
			);
		});
	}
   
   var cb = get_list_callback(type, 1);
   if (cb)
      xajax.call(cb, { parameters: [list], callback: myCallback, context: this } );
}

function search_list(type, search) {
	myCallback = xajax.callback.create();
	myCallback.onComplete = function(obj) {
		// remove last result set
		$('select[name="' + type + '_list_found"]').empty();
		// search list
		$.each(obj.context.search_arr, function(k, v) {
			$('select[name="' + type + '_list_found"]').append(
				$('<option></option>').val(v.cBase).html(v.cBase + ' | ' + v.cName).dblclick(function() {
					// selected list
					$('select[name="' + type + '_list_selected"]').append(
						$('<option></option>').val(v.cBase).html(v.cBase + ' | ' + v.cName).dblclick(function() {
							$(this).remove();
						})
					);
				})
			);
		});
	}
   
   var cb = get_list_callback(type, 0);
   if (cb)
      xajax.call(cb, { parameters: [search, type], callback: myCallback, context: this } );
	return false;
}

function get_list_callback(type, id) {
   switch (type)
   {
      case 'article':
         return (id == 0) ? 'getArticleList' :
                            'getArticleListFromString';

      case 'manufacturer':
         return (id == 0) ? 'getManufacturerList' :
                            'getManufacturerListFromString';
                           
      case 'categories':
         return (id == 0) ? 'getCategoryList' :
                            'getCategoryListFromString';
                            
      case 'tag':
         return (id == 0) ? 'getTagList' :
                            'getTagListFromString';
                            
      case 'attribute':
         return (id == 0) ? 'getAttributeList' :
                            'getAttributeListFromString';
   }
   return false;
}

function find_swf(movieName)
{
   if (navigator.appName.indexOf("Microsoft")!= -1)
      return window["ie_" + movieName];
   else
      return document[movieName];
}

/*
 *  single search browser
 */
function init_simple_search(callback) {
   $('.single_search_browser').find('input').keyup(function(){
      var search = $(this).val();
      var type = $('.single_search_browser').attr('type');
      
      $('.single_search_browser').find('select').empty();
      simple_search_list(type, search, function(result) {
         $(result).each(function(k, v) {
            $('.single_search_browser').find('select').append(
               $('<option></option>').attr('primary', v.kPrimary).attr('url', v.cUrl).val(v.cBase).html(v.cName).dblclick(function() {
                  $('.single_search_browser').find('.button.add').trigger('click');
               })
            );
         });
      });
   });
   
   $('.single_search_browser').find('.button.remove').click(function(){
      $('.single_search_browser').fadeOut(850);
   });
   
   $('.single_search_browser').find('.button.add').click(function() {
      // callback
      
      var res = {'kPrimary' : 0, 'kKey' : 0, 'cName' : '', 'cUrl' : ''};
      var type = $('.single_search_browser').attr('type');
      var selected = $('.single_search_browser').find('select option:selected');
      
      res.kKey = $(selected).val();
      res.cName = $(selected).html();
      res.kPrimary = $(selected).attr('primary');
      res.cUrl = $(selected).attr('url');
      
      if (typeof callback == 'function')
         callback(type, res);
      
      $('.single_search_browser').find('.button.remove').trigger('click');
   });
}

function show_simple_search(type) {
   $('.single_search_browser').attr('type', type);
   $('.single_search_browser').center().fadeIn(850);
   $('.single_search_browser').find('select').empty();
   $('.single_search_browser').find('input').val('').focus();
}
 
function simple_search_list(type, search, callback) {
	myCallback = xajax.callback.create();
	myCallback.onComplete = function(obj) {
      callback(obj.context.search_arr);
	}
   
   var cb = get_list_callback(type, 0);
   
   if (cb)
      xajax.call(cb, { parameters: [search, type], callback: myCallback, context: this } );
	return false;
}

function banners_datepicker()
{
    if ($('#vDatum') && $('#bDatum'))
    {
        $('#vDatum').datepicker();
        $('#bDatum').datepicker();
    }
}

/*
 *  document ready
 */
$(document).ready(function() {
   $('#show_article_list').set_search('article', '#assign_article_list');
   $('#show_manufacturer_list').set_search('manufacturer', '#assign_manufacturer_list');
   $('#show_categories_list').set_search('categories', '#assign_categories_list');
   banners_datepicker();
});