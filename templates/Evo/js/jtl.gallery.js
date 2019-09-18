/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function () {
    'use strict';

    var GalleryClass = function (element, options) {
        this.init(element, options);
    };
        
    GalleryClass.DEFAULTS = {
        items: [],
        size: 'md',
        fullscreen: false,
        template: {
            inline: '<div class="image-gallery has-thumbs">' +
            '<ul class="image-container v-wrap carousel vertical slide"></ul>' +
                '<div class="thumbs">' +
                    '<button name="btnGalleryPre" type="button" class="btnGalleryPre slick-up"></button>' +
                    '<div class="thumbs-box">' +
                        '<ul class="image-thumbs carousel vertical"></ul>' +
                    '</div>' +
                    '<button  name="btnGalleryNext" type="button" class="btnGalleryNext stick-down"></button>' +
                '</div>' +
            '</div>',
            fullscreen: '<div class="fullscreen image-gallery has-thumbs">' +
            '<ul class="image-container box-expanded"></ul>' +
            '<ul class="image-thumbs carousel vertical"></ul>' +
            '</div>',
            indicator: '<div class="pswp-indicator nivo-controlNav carousel-indicators bottom17"></div>'
        }
    };

    GalleryClass.prototype = {
        constructor: GalleryClass,
        element: null,
        ident: '_',
        index: 0,
        stack: [],

        init: function(element, options) {
            var items = [];

            this.stack   = [];
            this.index   = 0;
            this.element = element;
            this.options =
                $.extend({}, GalleryClass.DEFAULTS, options);


            if (this.options.items.length > 0) {
                items = this.options.items;
            } else {
                $(this.element).find('img').each(function (i, item) {
                    var image = $(item).data('list');
                    items.push(image);
                });
            }

            this.setItems(items);
            this.render();
        },
        
        getIdent: function() {
            return this.ident;
        },

        setItems: function(items, scope) {
            var self = this;
            this.ident = scope || this.ident;
            this.clearStack();
            $(items).each(function (i, item) {
                self.getStack().push(item);
            });
        },

        render: function(scope) {
            var self = this,
                template,
                index,
                item,
                src,
                alt,
                itemClass,
                image,
                thumb,
                pswpElement,
                items,
                pswpItems,
                options,
                gallery,
                indicator,
                step,
                p,
                i;
            this.ident = scope || this.ident;

            $(this.element)
                .removeClass('hidden')
                .hide();

            template = $(this.options.fullscreen ?
                this.options.template.fullscreen : this.options.template.inline);

            self.showThumbs(false);
            $(this.element).empty();

            for (i = 0; i < self.getStack().length; i++) {
                item = self.getStack()[i];
                src = item[self.options.size].src;
                alt = item[self.options.size].alt;

                itemClass = this.options.fullscreen ? 'item box-expanded' : 'item v-box';

                image = $('<li />').addClass(itemClass).data('src', src).append(
                    $('<img />').attr('src', src).attr('alt', alt)
                        .addClass('img-responsive-width')
                );

                $(template).find('.image-container')
                    .append(image);

                thumb = $('<li />').addClass('item')
                                   .append(
                    $('<img />')
                        .attr('src', item.xs.src)
                        .attr('alt', alt)
                        .attr('width', item.xs.size.width)
                        .attr('height', item.xs.size.height)
                );
                thumb = thumb.append($('<meta>').attr('itemprop', 'image').attr('content', item.lg.src));
                $(template).find('.image-thumbs')
                    .append(thumb);
            }

            $(this.element).append($(template));

            if (ResponsiveBootstrapToolkit.current() === 'xs') {
                $(this.element).find('.image-container').swipeleft(function (e) {
                    self.next();
                });
                $(this.element).find('.image-container').swiperight(function (e) {
                    self.prev();
                });
                $(this.element).find('.thumbs').swipeleft(function (e) {
                    var el = GalleryClass.prototype;
                    el.scrollTo(-2);
                });
                $(this.element).find('.thumbs').swiperight(function (e) {
                    var el = GalleryClass.prototype;
                    el.scrollTo(2);
                });
            }

            $(this.element).find('.image-thumbs > li').on('click', function (e) {
                self.activate(
                    $(this).index()
                );
            });

            if (!this.options.fullscreen) {
                $(this.element).find('.image-container li')
                    .addClass('action')
                    .on('click', function () {
                    index = $(this).index();

                    /*
                    $('#gallery-modal').modal('show');
                    $('#gallery-modal').on('shown.bs.modal', function (e) {
                        var height = $(this).find('.modal-body').height();
                        var items = jQuery.extend(true, [], self.getStack());
                        var gallery = $(this).find('.modal-body').gallery({
                            size: 'lg',
                            items: items,
                            fullscreen: true
                        });
                        gallery.activate(index);
                    });
                    $('#gallery-modal').on('hidden.bs.modal', function (e) {
                        $(this).find('.modal-body').empty();
                    });
                    */

                    pswpElement = document.querySelectorAll('.pswp')[0];
                    items = jQuery.extend(true, [], self.getStack());
                    pswpItems = [];

                    for (i = 0; i < items.length; i++) {
                        p = items[i]['lg'];
                        pswpItems.push({ src: p.src, w: p.size.width, h: p.size.height });
                    }

                    options = {
                        index: index,
                        mouseUsed: true,
                        closeOnScroll: false,
                        history: false
                    };

                    gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, pswpItems, options);
                    gallery.init();
                    if (pswpItems.length > 1) {
                        indicator = $(self.options.template.indicator);
                        $(gallery.container).parent().addClass('theme-default');
                        $(gallery.container).parent().append(indicator);
                        
                        for (i = 0; i < pswpItems.length; i++) {
                            indicator.append($('<a />')
                                .attr('src', '#')
                                .attr('rel', i)
                                .addClass(gallery.getCurrentIndex() == i ? 'nivo-control active' : 'nivo-control')
                                .click(function (e) {
                                    gallery.goTo(parseInt($(this).attr('rel')));
                                })
                            );
                        }
                        gallery.listen('beforeChange', function () {
                            $(this.container).parent().find('a.nivo-control').removeClass('active');
                            $(this.container).parent().find('a.nivo-control[rel="' + this.getCurrentIndex() + '"]').addClass('active');
                        });
                    }

                    return false;
                });
            }

            $(this.element).show();

            this.adjust();
            $(window).resize(function () {
                self.adjust();
            });

            this.activate(0);
            this.showThumbs(this.itemCount() > 1);
            
            this.adjust();

            GalleryClass.prototype.element = this.element;
            $(this.element).find('button[name="btnGalleryPre"]').click(function() {
                var el = GalleryClass.prototype;
                if ($(el.element).find('.image-thumbs').css('position') === 'absolute') {//V
                    step = Math.ceil($(el.element).find('.thumbs-box').outerHeight(false) / $(el.element).find('.image-thumbs li').outerHeight(true));
                } else {//H
                    step = Math.ceil($(el.element).find('.thumbs-box').outerWidth(false) / $(el.element).find('.image-thumbs li').outerWidth(true));
                }
                el.scrollTo(-step);//step = Schrittweite
            });
            $(this.element).find('button[name="btnGalleryNext"]').click(function() {
                var el = GalleryClass.prototype;
                if ($(el.element).find('.image-thumbs').css('position') === 'absolute') {//V
                    step = Math.ceil($(el.element).find('.thumbs-box').outerHeight(false) / $(el.element).find('.image-thumbs li').outerHeight(true));
                } else {//H
                    step = Math.ceil($(el.element).find('.thumbs-box').outerWidth(false) / $(el.element).find('.image-thumbs li').outerWidth(true));
                }
                el.scrollTo(step);//step = Schrittweite
            });
        },

        scrollTo: function(step) {
            var next,
                img_w,
                img_h,
                rect,
                listElem;
            if ($(this.element).find('.image-thumbs').css('position') === 'absolute') { //V
                listElem = $(this.element).find('.image-thumbs li');
                rect = listElem[0].getBoundingClientRect();
                if (rect.height) {
                    // "width" is available for IE9+
                    img_h = rect.height + ((parseInt(listElem.css('margin-bottom').replace('px', '')) + parseInt(listElem.css('margin-top').replace('px', ''))) / 2);
                } else {
                    // Calculate width for IE8 and below
                    img_h = (rect.bottom - rect.top) + (listElem.outerHeight(true) - (rect.bottom - rect.top))/2 + listElem.outerHeight(true)- (rect.bottom - rect.top);
                }
                if ($(this.element).find('.image-thumbs').outerHeight(false) - $(this.element).find('.thumbs-box').scrollTop() - (img_h * step) >  (img_h * step) ) {
                    next = img_h * step;
                } else {
                    next = Math.floor(( $(this.element).find('.image-thumbs').outerHeight(false) - $(this.element).find('.thumbs-box').scrollTop() - (img_h * step) ) / img_h) * img_h;
                }
                $(this.element).find('.thumbs-box').animate({scrollTop: '+=' + next}, 'slow');
            } else { //H
                img_w = $(this.element).find('.image-thumbs li').outerWidth(true);
                if ($(this.element).find('.image-thumbs').outerWidth(false) - $(this.element).find('.thumbs-box').scrollLeft() + (img_w * step) >  (img_w * step*-1) ) {
                    next = img_w * step;
                } else {
                    next = Math.floor(( $(this.element).find('.image-thumbs').outerWidth(false) - $(this.element).find('.thumbs-box').scrollLeft() - (img_w * step * -1) ) / img_w) * img_w * -1;
                }
                
                $(this.element).find('.thumbs-box').animate({scrollLeft: '-=' + next}, 'slow');
            }
        },
        
        showThumbs: function(show) {
            if (show) {
                $(this.element).find('.image-gallery').addClass('has-thumbs');
            } else {
                $(this.element).find('.image-gallery').removeClass('has-thumbs');
            }
        },

        adjust: function() {
            var height   = this.calcHeights(),
                imgCount = this.itemCount(),
                primary_h,
                listElem,
                main_img_w,
                main_img_h,
                img_w,
                img_w_outer,
                img_h_outer,
                img_h,
                h,
                prevElem,
                nextElem,
                btn_left,
                btn_top,
                w,
                rect,
                imagesPerView, //amount of images visible at each gallery "page"
                ulw;
            if (imgCount > 0) {
                primary_h   = Math.max($('.product-gallery').height(), $('.product-info').height());
                listElem    = $(this.element).find('.image-thumbs li');
                main_img_w  = $(this.element).find('.thumbs').width();
                main_img_h  = primary_h;
                img_w       = listElem.outerWidth(true);
                img_w_outer = (listElem.outerWidth(true) - listElem.outerWidth(false));
                prevElem    = $(this.element).find('button[name="btnGalleryPre"]');
                nextElem    = $(this.element).find('button[name="btnGalleryNext"]');
                if ($(this.element).find('.image-thumbs').css('position') === 'absolute') { //V
                    primary_h -= 40;//wegen up / down Buttons
                    $(this.element).find('.thumbs-box').scrollLeft(0);
                    rect = listElem[0].getBoundingClientRect();
                    img_h_outer = ((parseInt(listElem.css('margin-bottom').replace('px', '')) + parseInt(listElem.css('margin-top').replace('px', ''))) / 2);
                    img_h = (rect.height) ? (rect.height + img_h_outer) : (rect.bottom - rect.top + img_h_outer);
                    imagesPerView = Math.floor(primary_h / img_h);

                    $(this.element).find('.thumbs-box').css({'height': Math.ceil((imagesPerView * img_h) - img_h_outer) + 'px', 'width' : (img_w - img_w_outer) + 'px', 'overflow' : 'hidden'});
                    $(this.element).find('.thumbs').css({'position': 'absolute'});

                    btn_left = parseInt((img_w - img_w_outer) / 2 - prevElem.outerHeight(true) / 2);

                    prevElem.removeClass('btn-gallery-left').addClass('btn-gallery-up').css({'top': '', 'left': btn_left + 'px'});
                    nextElem.removeClass('btn-gallery-right').addClass('btn-gallery-down').css({'top': '', 'left': btn_left + 'px'});

                    //Show / Hide Navi Button
                    if ($(this.element).find('.image-thumbs').height() <= $(this.element).find('.thumbs').height()) {
                        prevElem.hide();
                        nextElem.hide();
                    } else {
                        prevElem.show();
                        nextElem.show();
                        $(this.element).find('.thumbs-box').css({'margin': '22px 0'});
                    }
                } else { //H
                    $(this.element).find('.thumbs-box').scrollTop(0);

                    img_h = $(this.element).find('.image-thumbs li').outerHeight(true);
                    h = ResponsiveBootstrapToolkit.current() === 'xs' ? img_h : parseInt((primary_h - main_img_h) / img_h) * img_h;
                    h = (h === 0) ? img_h : h;
                    w = parseInt( (main_img_w / img_w) ) * (img_w);
                    ulw = Math.ceil(imgCount / parseInt(h / img_h)) * img_w;

                    if ((h / img_h)*(w / img_w) > imgCount) {
                        h = Math.ceil(imgCount / (w / img_w)) * img_h;
                        ulw = w;
                    }

                    $(this.element).find('.thumbs-box').css({'height': h + 'px', 'width' : (w - img_w_outer) + 'px', 'overflow' : 'hidden'});
                    $(this.element).find('.image-thumbs').css({'width' : ulw + 'px'});
                    $(this.element).find('.thumbs').css({'position': 'relative'});
                    
                    btn_top = parseInt((h - prevElem.height())/2);
                    prevElem.removeClass('btn-gallery-up').addClass('btn-gallery-left').css({'left' : '', 'top':btn_top + 'px'});
                    nextElem.removeClass('btn-gallery-down').addClass('btn-gallery-right').css({'left':'', 'top':btn_top + 'px'});
                    
                    //Show / Hidden Navi Button
                    if ($(this.element).find('.image-thumbs').width() <= $(this.element).find('.thumbs').width()) {
                        prevElem.hide();
                        nextElem.hide();
                    } else {
                        prevElem.show();
                        nextElem.show();
                        $(this.element).find('.thumbs-box').css({'margin': 'auto'});
                    }
                }
            } else {
                $(this.element).find('.thumbs').hide();
            }

            if (this.options.fullscreen || ResponsiveBootstrapToolkit.current() === 'xs') {
                // var height = $('#gallery-modal').find('.modal-body .image-gallery').prop('scrollHeight');
                // this.setMaxHeight(height);
                this.resetMaxHeight();
            } else {
                this.setMaxHeight(height, img_h);
            }
        },
        
        calcHeights: function() {
            var images = this.getStack(),
                newMaxHeight = $(this.element).find('ul.image-container')
                .outerHeight(false),
                i,
                size;

            for (i = 0; i < images.length; i++) {
                size = images[i][this.options.size].size;
                if (size.height > newMaxHeight) {
                    newMaxHeight = size.height;
                }
            }

            return newMaxHeight;
        },

        setMaxHeight: function(height, thumb_height) {
            $(this.element).find('.image-gallery')
                .css('max-height', height+thumb_height);
            $(this.element).find('ul.image-container > li img')
                .css('max-height', height);
            $(this.element).find('ul.image-container')
                .css('height', height);
        },

        resetMaxHeight: function () {
            $(this.element).find('.image-gallery, ul.image-container > li img')
                .css('max-height', '');
            $(this.element).find('ul.image-container')
                .css('height', '');
        },

        itemCount: function() {
            return this.getStack().length;
        },

        clearStack: function(scope) {
            var s = scope || this.ident;
            if (typeof this.stack[s] !== 'undefined') {
                this.stack[s] = [];
            }
        },

        getStack: function(scope) {
            var s = scope || this.ident;
            if (typeof this.stack[s] === 'undefined') {
                this.stack[s] = [];
            }
            return this.stack[s];
        },

        getStacks: function() {
            return this.stack;
        },

        activate: function(index) {
            $(this.element).find('ul.image-container > li, ul.image-thumbs > li').each(function (index, item) {
                $(item).removeClass('active');
            });

            $(this.element).find('ul.image-container > li:eq(' + index + '), ul.image-thumbs > li:eq(' + index + ')')
                .addClass('active');

            this.index = index;
        },

        next: function() {
            var idx = (this.index + 1) >= this.itemCount() ? 0 : this.index + 1;
            this.activate(idx);
        },

        prev: function() {
            var idx = (this.index - 1) < 0 ? this.itemCount() - 1 : this.index - 1;
            this.activate(idx);
        }
    };

    // PLUGIN DEFINITION
    // =================

    $.fn.gallery = function(option) {
        if (this.length === 0) {
            return this;
        } else if (this.length > 1) {
            this.each(function () {
                $(this).gallery(option);
            });
            return this;
        }

        return new GalleryClass(this, option);
    };

})(jQuery);
