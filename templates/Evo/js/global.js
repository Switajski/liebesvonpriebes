'use strict';

/**********************************************************************************************************************/
/**********************************************************************************************************************/

$('body').on('click', '.option li', function (e) {
    var i = $(this).parents('.select').attr('id'),
        v = $(this).children().text(),
        o = $(this).attr('id');
    $('#' + i + ' .selected').attr('id', o).text(v);
});

/**
 *  Format file size
 */
function formatSize(size) {
    var fileSize = Math.round(size / 1024),
        suffix = 'KB',
        fileSizeParts;

    if (fileSize > 1000) {
        fileSize = Math.round(fileSize / 1000);
        suffix = 'MB';
    }

    fileSizeParts = fileSize.toString().split('.');
    fileSize = fileSizeParts[0];

    if (fileSizeParts.length > 1) {
        fileSize += '.' + fileSizeParts[1].substr(0, 2);
    }
    fileSize += suffix;

    return fileSize;
}

function getCategoryMenu(categoryId, success) {
    var xx = {};
    var io = $.evo.io();

    io.call('getCategoryMenu', [categoryId], xx, function (error, data) {
        if (error) {
            console.error(data);
        }
        else {
            if (typeof success === 'function') {
                success(xx.response);
            }
        }
    });

    return true;
}

function categoryMenu(rootcategory) {
    if (typeof rootcategory === 'undefined') {
        rootcategory = $('.sidebar-offcanvas .navbar-categories').html();
    }

    $('.sidebar-offcanvas li a.nav-sub').on('click', function(e) {
        var navbar = $('.sidebar-offcanvas .navbar-categories'),
            ref = $(this).data('ref');

        if (ref === 0) {
            $(navbar).html(rootcategory);
            categoryMenu(rootcategory);
        }
        else {
            getCategoryMenu(ref, function(data) {
                $(navbar).html(data);
                categoryMenu(rootcategory);
            });
        }

        return false;
    });
}

function compatibility() {
    var __enforceFocus = $.fn.modal.Constructor.prototype.enforceFocus;
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
        if ($('.modal-body .g-recaptcha').length == 0) {
            __enforceFocus.apply(this, arguments);
        }
    };
}

function regionsToState() {
    if ($('#state').length == 0)
        return;

    var title = $('#state').attr('title');
    if($('#state').attr('required') == 'required'){
        var stateIsRequired = true;
    } else {
        var stateIsRequired = false;
    }

    $('#country').change(function() {
        var result = {};
        var io = $.evo.io();
        var val = $(this).find(':selected').val();

        io.call('getRegionsByCountry', [val], result, function (error, data) {
            if (error) {
                console.error(data);
            }
            else {
                var data = result.response;
                var def = $('#state').val();
                if (data != null && data.length > 0) {
                    if (stateIsRequired){
                        var state = $('<select />').attr({ id: 'state', name: 'bundesland', class: 'required form-control', required: 'required'});
                    } else {
                        var state = $('<select />').attr({ id: 'state', name: 'bundesland', class: 'form-control'});
                    }

                    state.append('<option value="">' + title + '</option>');
                    $(data).each(function(idx, item) {
                        state.append(
                            $('<option></option>').val(item.cCode).html(item.cName)
                                .attr('selected', item.cCode == def || item.cName == def ? 'selected' : false)
                        );
                    });
                    $('#state').replaceWith(state);
                }
                else {
                    if (stateIsRequired) {
                        var state = $('<input />').attr({ type: 'text', id: 'state', name: 'bundesland', class: 'required form-control', placeholder: title, required: 'required' });
                    } else {
                        var state = $('<input />').attr({ type: 'text', id: 'state', name: 'bundesland', class: 'form-control', placeholder: title });
                    }
                    $('#state').replaceWith(state);
                }
            }
        });
        return false;

    }).trigger('change');
}

function loadContent(url)
{
    $.evo.extended().loadContent(url, function() {
        $.evo.extended().register();

        if (typeof $.evo.article === 'function') {
            $.evo.article().onLoad();
            $.evo.article().register();
            addValidationListener();
        }

        $('html,body').animate({
            scrollTop: $('.list-pageinfo').offset().top - $('#evo-main-nav-wrapper').outerHeight() - 10 
        }, 100);
    });
}

function navigation()
{
    var navWrapper = $('#evo-main-nav-wrapper');
    if (navWrapper.hasClass('do-affix')) {
        navWrapper.parent()
            .height(navWrapper.height());
        navWrapper.affix({
            offset: {
                top: navWrapper.offset().top
            }
        });
    }
}

function addValidationListener() {
    var forms  = $('form.evo-validate'),
        inputs = $('form.evo-validate input,form.evo-validate select,form.evo-validate textarea'),
        selects = $('form.evo-validate select'),
        $body  = $('body');

    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('invalid', function (event) {
            event.preventDefault();
            $(event.target).closest('.form-group').find('div.form-error-msg').remove();
            $(event.target).closest('.form-group').addClass('has-error').append('<div class="form-error-msg text-danger"><i class="fa fa-warning"></i> ' + event.target.validationMessage + '</div>');

            if (!$body.data('doScrolling')) {
                var $firstError = $(event.target).closest('.form-group.has-error');
                if ($firstError.length > 0) {
                    $body.data('doScrolling', true);
                    var $nav        = $('#evo-main-nav-wrapper.do-affix'),
                        fixedOffset = $nav.length > 0 ? $nav.outerHeight() : 0,
                        vpHeight    = $(window).height(),
                        scrollTop   = $(window).scrollTop();
                    if ($firstError.offset().top > (scrollTop + vpHeight) || $firstError.offset().top < scrollTop) {
                        $('html, body').animate(
                            {
                                scrollTop: $firstError.offset().top - fixedOffset - parseInt($firstError.css('margin-top'))
                            },
                            {
                                done: function () {
                                    $body.data('doScrolling', false);
                                }
                            }, 300
                        );
                    }
                }
            }
        }, true);
    }

    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('blur', function (event) {
            checkInputError(event);
        }, true);
    }
    for (var i = 0; i < selects.length; i++) {
        selects[i].addEventListener('change', function (event) {
            checkInputError(event);
        }, true);
    }

}

function checkInputError(event)
{
    var $target = $(event.target);
    $target.closest('.form-group').find('div.form-error-msg').remove();

    if ($target.data('must-equal-to') !== undefined) {
        var $equalsTo = $($target.data('must-equal-to'));
        if ($equalsTo.length === 1) {
            var theOther = $equalsTo[0];
            if (theOther.value !== '' && theOther.value !== event.target.value && event.target.value !== '') {
                event.target.setCustomValidity($target.data('custom-message') !== undefined ? $target.data('custom-message') : event.target.validationMessage);
            } else {
                event.target.setCustomValidity('');
            }
        }
    }

    if (event.target.validity.valid) {
        $target.closest('.form-group').removeClass('has-error');
    } else {
        $target.closest('.form-group').addClass('has-error').append('<div class="form-error-msg text-danger"><i class="fa fa-exclamation-triangle"></i> ' + event.target.validationMessage + '</div>');
    }
}


function captcha_filled() {
    $('.g-recaptcha').closest('.form-group').find('div.form-error-msg').remove();
}

function isTouchCapable() {
    return 'ontouchstart' in window || (window.DocumentTouch && document instanceof window.DocumentTouch);
}

$(window).load(function(){
    navigation();
});

$(document).ready(function () {
    $('.collapse-non-validate')
        .on('hidden.bs.collapse', function(e) {
            $(e.target)
                .addClass('hidden')
                .find('fieldset, .form-control')
                .attr('disabled', true);
            e.stopPropagation();
        })
        .on('show.bs.collapse', function(e) {
            $(e.target)
                .removeClass('hidden')
                .attr('disabled', false);
            e.stopPropagation();
        }).on('shown.bs.collapse', function(e) {
            $(e.target)
                .find('fieldset, .form-control')
                .filter(function (i, e) {
                    return $(e).closest('.collapse-non-validate.collapse').hasClass('in');
                })
                .attr('disabled', false);
            e.stopPropagation();
        });
    $('.collapse-non-validate.collapse.in')
        .removeClass('hidden')
        .find('fieldset, .form-control')
        .attr('disabled', false);
    $('.collapse-non-validate.collapse:not(.in)')
        .addClass('hidden')
        .find('fieldset, .form-control')
        .attr('disabled', true);

    $('#complete-order-button').click(function () {
        var commentField = $('#comment'),
            commentFieldHidden = $('#comment-hidden');
        if (commentField && commentFieldHidden) {
            commentFieldHidden.val(commentField.val());
        }
    });

    $(document).on('click', '.footnote-vat a, .versand, .popup', function(e) {
        var url = e.currentTarget.href;
        url += (url.indexOf('?') === -1) ? '?isAjax=true' : '&isAjax=true';
        eModal.ajax({
            size: 'lg',
            url: url,
            title: typeof e.currentTarget.title !== 'undefined' ? e.currentTarget.title : '',
            keyboard: true,
            tabindex: -1
        });
        e.stopPropagation();
        return false;
    });

    $(document).on('click', '.pagination-ajax li:not(.active) a', function(e) {
        var url = $(this).attr('href');
        history.pushState(null, null, url);
        loadContent(url);
        return e.preventDefault();
    });
    
    if ($('.pagination-ajax').length > 0) {
        window.addEventListener('popstate', function(e) {
            loadContent(document.location.href);
        }, false);
    }

    $('.dropdown .dropdown-menu.keepopen').on('click touchstart', function(e) {
        e.stopPropagation();
    });

    if (typeof $.fn.jtl_search === 'undefined') {
        var productSearch = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote:         {
                url:      'io.php?io={"name":"suggestions", "params":["%QUERY"]}',
                wildcard: '%QUERY'
            }
        });

        $('input[name="qs"]').typeahead(
            {
                highlight: true
            },
            {
                name:      'product-search',
                display:   'keyword',
                source:    productSearch,
                templates: {
                    suggestion: function (e) {
                        return e.suggestion;
                    }
                }
            }
        );
    }

    var citySuggestion = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('keyword'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote:         {
            url:      'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).closest('fieldset').find('.country_input').val() + '", "' + $(this).closest('fieldset').find('.postcode_input').val() + '"]}',
            wildcard: '%QUERY'
        },
        dataType: "json"
    });
    $('.city_input').focusin(function () {
        citySuggestion.remote.url = 'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).closest('fieldset').find('.country_input').val() + '", "' + $(this).closest('fieldset').find('.postcode_input').val() + '"]}';
    });
    $('.postcode_input').change(function () {
        citySuggestion.remote.url = 'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).closest('fieldset').find('.country_input').val() + '", "' + $(this).val() + '"]}';
    });
    $('.country_input').change(function () {
        citySuggestion.remote.url = 'io.php?io={"name":"getCitiesByZip", "params":["%QUERY", "' + $(this).val() + '", "' + $(this).closest('fieldset').find('.postcode_input').val() + '"]}';
    });

    $('.city_input').typeahead(
        {
            hint: true,
            minLength: 0
        },
        {
            name:   'cities',
            source: citySuggestion
        }
    );

    $('.btn-offcanvas').click(function() {
        $('body').click();
    });

    if ("ontouchstart" in document.documentElement) {
        $('.variations .swatches .variation').on('mouseover', function() {
            $(this).trigger('click');
        });
    }
    
    /*
     * activate category parents of active child
     
    var child = $('section.box-categories .nav-panel li.active');
    if (child.length > 0) {
        //$(child).parents('.nav-panel li').addClass('active');
        $(child).parents('.nav-panel li').each(function(i, item) {
           $(item).find('ul.nav').show();
        });
    }
     */

    /*
     * show subcategory on caret click
     */
    $('section.box-categories .nav-panel li a').on('click', function(e) {
        if ($(e.target).hasClass("nav-toggle")) {
            $(e.delegateTarget)
                .parent('li')
                .find('> ul.nav').toggle();
            return false;
        }
    });

    /*
     * show linkgroup on caret click
     */
    $('section.box-linkgroup .nav-panel li a').on('click', function(e) {
        if ($(e.target).hasClass("nav-toggle")) {
            $(e.delegateTarget)
                .parent('li')
                .find('> ul.nav').toggle();
            return false;
        }
    });

    /*
     * Banner
     */
    var bannerLink = $('.banner > a');
    bannerLink.popover({
        placement: 'auto bottom',
        html:      true,
        trigger:   'hover',
        container: 'body',
        content:   function () {
            return $(this).children('.area-desc').html()
        }
    });

    bannerLink.mouseenter(function () {
        $(this).animate({
            borderWidth: 10,
            opacity:     0
        }, 900, function () {
            $(this).css({opacity: 1, borderWidth: 0});
        });
    });

    $('.banner').mouseenter(function () {
        $(this).children('a').animate({
            borderWidth: 10,
            opacity:     0
        }, 900, function () {
            $(this).css({opacity: 1, borderWidth: 0});
        });
    });

    $('.banner > a[href=""]').click(function () {
        return false;
    });

    /*
     * set bootstrap viewport
     */
    (function($, document, window, viewport){ 
        var $body = $('body');

        $(window).resize(
            viewport.changed(function() {
                $body.attr('data-viewport', viewport.current());
            })
        );
        $body.attr('data-viewport', viewport.current());
        $body.attr('data-touchcapable', isTouchCapable() ? 'true' : 'false');
    })(jQuery, document, window, ResponsiveBootstrapToolkit);

    categoryMenu();
    regionsToState();
    compatibility();
    addValidationListener();
});
