/**
 * JTL sidebar.js v1.0.0
 * http://www.jtl-software.com
 *
 * Copyright 2014, JTL Software
 * http://www.jtl-software.com
 * @author: Pascal Kleefeld <pascal.kleefeld@jtl-software.com>
 */

(function ($) {

	$.sidebarIsOpen = function () {
		$('.st-menu').css('visibility', 'visible');
		$('main.st-pusher').addClass('no-trans').addClass('expand');
		$("#navicon").removeClass('menu-icon').addClass('cover-icon');
		$('#menu > li > a').each(function () {
			$(this).removeClass('menu-link-small');
		});
		$('.avatar').removeClass('avatar-small');
		$('.menu-head').removeClass('menu-head-small');
		$('.st-menu').removeClass('st-menu-small');
		$('#menu > li > ul').removeClass('sub-menu-small');
		$('.user-menu').show();
	};

	$.sidebarIsClosed = function () {
		$("#navicon").addClass('menu-icon').removeClass('cover-icon');
		$('.st-pusher').addClass('covered');
		$('#menu > li > a').each(function () {
			$(this).addClass('menu-link-small');
		});
		$('.avatar').addClass('avatar-small');
		$('.menu-head').addClass('menu-head-small');
		$('.st-menu').addClass('st-menu-small');
		$('#menu > li > ul').addClass('sub-menu-small');
		$('.user-menu').hide();
	};

	$.openSidebar = function () {
		$('#navicon').removeClass('close-menu');
		$('.st-pusher').removeClass('no-trans').removeClass('expand').addClass('covered');
		$('#menu > li > a').each(function () {
			$(this).addClass('menu-link-small');
		});
		$('#menu > li > a > button').each(function () {
			$(this).removeClass('collapsed');
		});
		$('#menu ul').each(function () {
			$(this).hide();
			$(this).removeAttr('style');
		});
		$('.avatar').addClass('avatar-small');
		$('.menu-head').addClass('menu-head-small');
		$('.st-menu').addClass('st-menu-small');
		$('#menu > li > ul').addClass('sub-menu-small');
		$('.user-menu').hide();
		$('.st-pusher').css('position', 'static');
		$.cookie('sidebar_open', '0');
	};

	$.closeSidebar = function () {
		$('#jtl-body').addClass('st-effect-2');
		$('#navicon').addClass('cover-icon');
		$('.st-pusher').removeClass('no-trans').removeClass('covered').addClass('expand');
		$('.st-menu').css('visibility', 'visible');
		$('#menu > li > a').each(function () {
			$(this).removeClass('menu-link-small');
		});
		$('.avatar').removeClass('avatar-small');
		$('.menu-head').removeClass('menu-head-small');
		$('.st-menu').removeClass('st-menu-small');
		$('#menu > li > ul').removeClass('sub-menu-small');
		$('.st-pusher').css('position', 'relative');
		$('.user-menu').show();
		$.cookie('sidebar_open', '1');
	};

	$.addIconClassesToLinks = function () {
		var i = 1;
		$('#menu > li > a > span').each(function () {
			$(this).addClass('menu-link-' + i);
			i++;
		});

		var i = 1;
		$('#topmenu > li > a > span.link-icon').each(function () {
			$(this).addClass('menu-link-' + i);
			i++;
		});

		var i = 1;
		$('#menu > li').each(function () {
			$(this).addClass('menu-item-' + i);
			i++;
		});

		var i = 1;
		$('#topmenu > li').each(function () {
			$(this).addClass('topmenu-item-' + i);
			i++;
		});
	};

	$.switchNavi = function () {
		$('main.st-pusher').addClass('trans').removeClass('covered').addClass('expand');
		if ($.cookie('sidebar_open') == 1) {
			$.openSidebar();
		} else if ($.cookie('sidebar_open') == 0) {
			$.closeSidebar();
		}
	};

	$.loadSavedMenuStatus = function () {
		var menuItemOpened = $.cookie('sidebar_menu_open');
		if ($.cookie('sidebar_open') == 1) {
			$('#menu .' + menuItemOpened).find('ul').show();
			$('.' + menuItemOpened).find('.collapse-menu').addClass('collapsed');
		}

		if ($.cookie('sidebar_open') == 1) {
			$.sidebarIsOpen();
		} else if ($.cookie('sidebar_open') == 0) {
			$.sidebarIsClosed();
		}
	};

	$.checkTopMenuStatus = function () {
		if ($.cookie('menu_top') == 1) {
			$('#check-menus').attr('checked', true);
			$('#menu_wrapper').remove();
			$('main.st-pusher').removeClass('expand').removeClass('covered');
		} else {
			$('#check-menus').attr('checked', false);
			$('#header').remove();
		}
	};

	$.switchMenus = function () {
		var topMenu = $('#check-menus').is(':checked');
		if (topMenu == true) {
			$.cookie('menu_top', '1');
		} else {
			$.cookie('menu_top', '0');
		}
		//location.reload();		
	};

	$.checkFixMenuStatus = function () {
		if ($.cookie('menu_fixed') == 1) {
			$('#fix-menus').attr('checked', true);
			$('aside.top-bar').addClass('fixed');
			$('nav#menu_wrapper').addClass('fixed');
			if ($.cookie('menu_top') == 1) {
				$('#content-wrapper').addClass('p-top-with-menu');
			} else {
				$('#content-wrapper').addClass('p-top');
			}
			$('#header').addClass('top-menu-fixed');
		} else {
			$('#fix-menus').attr('checked', false);
		}
	};

	$.fixMenu = function () {
		var fixMenu = $('#fix-menus').is(':checked');
		console.log(fixMenu);
		if (fixMenu == true) {
			$.cookie('menu_fixed', '1');
		} else {
			$.cookie('menu_fixed', '0');
		}
		location.reload();
	}

})(jQuery);

$(document).ready(function () {

	// if the cookie never was set we set the cookie to 0
	if (typeof $.cookie('sidebar_open') === 'undefined') {
		$.cookie('sidebar_open', '1')
	}

	// add classes to each menu link for adding icons before each link
	$.addIconClassesToLinks();

	// load the saved status of the menu
	$.loadSavedMenuStatus();

	// check if the menu should be on top
	$.checkTopMenuStatus();

	// check if the menus should be fix
	$.checkFixMenuStatus();

	// open or close the side bar
	$("#navicon").on('click', function () {
		$(this).toggleClass('menu-icon cover-icon');
		$.switchNavi();
	});

	// set classes and save cookies for each drop down menu
	$('#menu > li > a').on('click', function () {
		var submenu = $(this).parent().find('ul');
		var currentClass = submenu.attr('class');
		var menuItem = $(this).parent().attr('class').replace("topmenu", "").replace("topfirst", "").replace("  ", "");
		var thisMenuItem = $(this).find('span').attr('class').replace("link", "item");

		$.cookie('sidebar_menu_open', menuItem);

		$('#menu ul').each(function () {
			$(this).slideUp();
		});

		$('#menu li a .collapse-menu').each(function () {
			$(this).removeClass('collapsed');
		});

		if (submenu.is(':visible')) {
			submenu.slideUp();
			if (menuItem == thisMenuItem) {
				$.removeCookie('sidebar_menu_open');
			}
		} else {
			$(this).find('.collapse-menu').addClass('collapsed');
			submenu.slideDown();
		}
	});

	// Opens the configuration menu in the right top bar	
	$('.top-right-menu .options').toggle(function () {
		$(this).parent().find('.options-layer').show();
		$(this).addClass('active');
	}, function () {
		$(this).parent().find('.options-layer').hide();
		$(this).removeClass('active');
	});

	$('#check-menus').on('change', function () {
		$.switchMenus();
	});

	$('#fix-menus').on('change', function () {
		$.fixMenu();
	});

});// document ready
