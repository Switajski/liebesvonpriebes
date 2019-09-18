$.fn.lazy = function(threshold, callback) {

	var $w = $(window),
			th = threshold || 0,
			retina = window.devicePixelRatio > 1,
			attrib = retina? "data-src-retina" : "data-src",
			images = this,
			loaded;

	this.one("lazy", function() {
		var source = this.getAttribute(attrib);
		source = source || this.getAttribute("data-src");
		if (source) {
			this.setAttribute("src", source);
			if (typeof callback === "function") callback.call(this);
		}
	});

	function lazy() {
		var inview = images.filter(function() {
			var $e = $(this);
			var wt = $w.scrollTop(),
					wb = wt + $w.height(),
					et = $e.offset().top,
					eb = et + $e.height();

			return eb >= wt - th && et <= wb + th;
		});

		loaded = inview.trigger("lazy");
		images = images.not(loaded);
	}

	$w.on("scroll.lazy resize.lazy lookup.lazy", lazy);

	lazy();

	return this;

};