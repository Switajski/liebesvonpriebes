/**
 * jtl_themebar js
 *
 * @package     jtl_themebar
 * @version     101
 * @createdAt   21.06.2016
 * @author      JTL-Software-GmbH
 */

var viewportswidth = {
    fh: 1920,
    hd: 1680,
    ll: 1280,
    la: 1024,
    ta: 768,
    ml: 425,
    mm: 375,
    ms: 320
};

var viewportsheight = {
    fh: 1080,
    hd: 1050,
    ll: 1024,
    la: 768,
    ta: 640,
    ml: 500,
    mm: 480,
    ms: 480
};

var template =
    '<div class ="hidden-xs" id="device-header">' +
        '<div class="viewport-selection">' +
            '<a href="#ms" data-viewport="ms" data-label="Mobil S - 320px" class="ms"><span></span></a>' +
            '<a href="#mm" data-viewport="mm" data-label="Mobil M - 375px" class="mm"><span></span></a>' +
            '<a href="#ml" data-viewport="ml" data-label="Mobil L - 425px" class="ml"><span></span></a>' +
            '<a href="#ta" data-viewport="ta" data-label="Tablet - 768px" class="ta"><span></span></a>' +
            '<a href="#la" data-viewport="la" data-label="Laptop - 1024px" class="la"><span></span></a>' +
            '<a href="#ll" data-viewport="ll" data-label="Laptop L - 1280px" class="ll"><span></span></a>' +
            '<a href="#hd" data-viewport="hd" data-label="HD - 1680px" class="hd"><span></span></a>' +
            '<a href="#fh" data-viewport="fh" data-label="Full HD - 1920px" class="fh"><span></span></a>' +
        '</div>' +
    '</div>' +
    '<div id="device-content">' +
        '<iframe frameborder="0" class="viewport hidden-xs"></iframe>' +
    '</div>'
;
	
$('body', '.switcher .switcher-wrapper').on('click', function(e) {
	e.stopPropagation();
});

$('.switcher').on('show.bs.dropdown', function () {
	showBackdrop();
}).on('hide.bs.dropdown', function () {
	hideBackdrop();
});

var showBackdrop = function() {
	$('<div class="switcher-backdrop fade in" />')
		.appendTo('body');
};

var hideBackdrop = function() {
	$('.switcher-backdrop').remove();
};

var initThemebar = function () {
	var context = $('iframe')[0].contentWindow.document;
	$('.styleswitch', context).click(function () {
		var newStyle = this.getAttribute("rel");
		switchStyle(newStyle);
		return false;
	});
	
	if (lastStyle = $.cookie('style')) {
		switchStyle(lastStyle);
	}
};

var switchStyle = function(styleName) {
	var context = $('iframe')[0].contentWindow.document;
	var style = $('link[data-theme="'+styleName+'"]', context);
	
	var _switch = function() {
		$('link[data-theme]', context)
			.prop('disabled', true);

		$('link[data-theme="'+styleName+'"]', context)
			.prop('disabled', false);

		$.cookie('style', styleName);
		
		$('#switcher li.styleswitch', context)
			.removeClass('active');
			
		$('#switcher li.styleswitch[rel="'+styleName+'"]', context)
			.addClass('active');
	}
	
	if (!styleLoaded(style.attr('href'))) {
		style
			.load(function() {
				_switch();
			})
			.prop('disabled', false);
	}
	else {
		_switch();
	}
};

var styleLoaded = function(url) {
	var context = $('iframe')[0].contentWindow.document;
	
	if (url.indexOf(location.origin) == -1) {
		url = location.origin + '/' + url;
	}
	
	for(var i = 0; i < context.styleSheets.length; i++) {
		if (context.styleSheets[i].href == url) {
			return true;
		}
	}
	
	return false;
};

var getRequestParams = function() {
	var params={};
	window.location.search
	  .replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
		params[key] = value;
	  }
	);
	return params;
}

var isMobileDevice = function() {
	var userAgent = navigator.userAgent.toLowerCase();
	return /android|webos|iphone|ipad|ipod|blackberry|opera mini|Windows Phone|iemobile|WPDesktop|XBLWP7/i.test(userAgent);
}

var resizeFrame = function(width, height) {
	$('#device-content')
		.width(width)
		.height(height);
}

var responsiveView = function(callback){
	var mobile = isMobileDevice(),
		params = getRequestParams(),
		href = window.location.href,
		injected = window.location !== window.parent.location;

	if (injected || mobile || (params.themebar != 'undefined' && parseInt(params.themebar) == 0)) {
		return;
	}
	
	var content = $('html');
	content.find('body').addClass('frame');

	$('body')
		.html(template);
		
	$(function() {
		$('.backstretch').remove();
	});

	var $iframe = $('iframe');
	
	resizeFrame(viewportswidth.ll, viewportsheight.ll);

	href += '?iframe';

	$iframe
		.attr('src', href)
		.on('load', function(){
			//var href = $(this).get(0).contentWindow.window.location.href;
			//history.pushState(null, null, href);

			if (typeof callback == "function") {
				callback();
			}
		});

	$(window).bind('popstate', function(event) {
		//$iframe.load(window.location.href);
	});

	$('#device-header .viewport-selection > a').mouseover(function() { $('#device-header .viewport-selection > a[data-viewport="ms"]').find("span").text($(this).data('label')) });
	$('#device-header .viewport-selection > a').mouseout(function() { $('#device-header .viewport-selection > a[data-viewport="ms"]').find("span").text("") });

	$('#device-header .viewport-selection > a')
		.click(function() {
			var vp = $(this).data('viewport');
			var width = viewportswidth[vp];
			var height = viewportsheight[vp];
			resizeFrame(width, height);
			return false;
		});
};

responsiveView(function() {
	initThemebar();
});