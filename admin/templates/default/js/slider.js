$(document).ready(function() {
    
    $( "#slides" ).sortable({
        revert: true,
        placeholder: "placeholder",
        items: "li.slide:not(.append)",
        stop: function(event, ui) {
            $.map($(this).find('li.slide:not(.append)'), function(element) {
                ui.item.css('top','0px');
                var id = $(element).attr('id');
                var sort = $(element).index()+1;
                $('li#'+id).children('div.overlay_edit').children('div').children('form').children('input[type=hidden][name=nSort]').val(sort);
                $('li#'+id).children('span').children('span.number').html(sort);
                
                callback = xajax.callback.create();
                callback.onComplete = function(obj) {
                }
                xajax.call('saveSlide', {
                    parameters: [xajax.getFormValues('slide'+id)], 
                    callback: callback, 
                    context: this
                } );
            });
        }
    });

    $('ul#slides').delegate('li.slide:not(.append)','dblclick',bindOverlay);

    $('ul#slides').delegate('li.slide form input.slide_delete','click', function() {
        if(confirm('Wollen Sie den Slide wirklisch Löschen?')) {
            $(this).closest('li.slide').children('div.overlay_edit').children('div').children('form').children('input[name=delete]').val(1);
           
            callback = xajax.callback.create();
            callback.onComplete = function(obj) {
                var slide = obj.context.slide;
                $('li#'+slide.kSlide).remove();
                changeScrollPane(calcScrollPane());
            }
            
            var params = xajax.getFormValues('slide'+ $(this).closest('li.slide').attr('id'));
            
            xajax.call('deleteSlide', {
                parameters: [params.kSlider,params.kSlide], 
                callback: callback, 
                context: this
            });
        }
    });
    
    $('ul#slides').delegate('li.slide form input.cancel','click',function() {
        var id = $(this).closest('li.slide').attr('id');
        $('form#slide'+id).get(0).reset();
        hideOverlay(id);
        hideOverlayEdit(id);
    });  
    
    $('ul#slides').delegate('li.slide form input[name=save]','click',function() {
        var id = $(this).closest('li.slide').attr('id');
        $('li#'+id+' p.ajax_preloader').fadeIn('slow');
        callback = xajax.callback.create();
        callback.onComplete = function(obj) {
            var slide = obj.context.slide;
            var timestamp = new Date().getTime();
            if(slide == undefined) {
                $('li#'+id+' div.status_error').fadeIn('slow').delay(2000).fadeOut('slow');
                $('li#'+id+' p.ajax_preloader').hide();
            } else {
                $('li#'+id+' img').attr('src',slide.cBildAbsolut);
                $('li#'+id+' img').attr('reload',timestamp);
                $('li#'+id+' span.number').html(slide.nSort);
                $('li#'+id+' h4').html(slide.cTitel);
                $('li#'+id+' p:not(.ajax_preloader)').html(slide.cText);
        
                $('form#slide'+id+' input[name=nSort]').val(slide.nSort);
                $('form#slide'+id+' input[name=cTitel]').val(slide.cTitel);
                $('form#slide'+id+' input[name=cText]').val(slide.Text);
                hideOverlay(id);
                hideOverlayEdit(id);
                $('li#'+id+' p.ajax_preloader').hide();
            }

        }
        xajax.call('saveSlide', {
            parameters: [xajax.getFormValues('slide'+id)], 
            callback: callback, 
            context: this
        } );

    });
    
    $('ul#slides').delegate('li.slide form .select_image','click',function() {
        var id = $(this).closest('li.slide').attr('id');
        
        var shop_url = $('.shop_url').html();
        var kcfinder_path = $('.kcfinder_path').html();
        
        window.KCFinder = {
            callBack: function(url) {
                $('li#'+id+' input[name=cBild]').val(url);
                $('li#'+id+' div.status_success').fadeIn('fast').delay(1000).fadeOut('fast');
                kcFinder.close();
            }
        };
        var kcFinder = window.open(kcfinder_path+'browse.php?type=Bilder&lang=de', 'kcfinder_textbox',
            'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
            'resizable=1, scrollbars=0, width=800, height=600,'
            ); 
    });
    
    $('#nAnimationSpeed, #nPauseTime').change(function() {
        var nAnimationSpeed = parseInt($('#nAnimationSpeed').val());
        var nPauseTime = parseInt($('#nPauseTime').val());
        if(nAnimationSpeed > nPauseTime) {
            $('#nAnimationSpeedWarning').show();
            $('#nAnimationSpeed').addClass('nAnimationSpeedWarningBorder');
        } else {
            if($('#nAnimationSpeed').hasClass('nAnimationSpeedWarningBorder')) {
                $('#nAnimationSpeedWarning').hide(); 
                $('#nAnimationSpeed').removeClass('nAnimationSpeedWarningBorder');
            }
        }
    });
    
    $('input[name=append_slide]').click(function() {
        if($('form#slide0 input[name=cBild]').val() != '') {
            $('li#0 p.ajax_preloader').show();
            callback = xajax.callback.create();
            callback.onComplete = function(obj) {
                $('form#slide0 input[name=cTitel]').val('');
                $('form#slide0 textarea').val('');
                $('form#slide0 input[name=cBild]').val('');
                $('form#slide0 input[name=cLink]').val('');
            
                var slide = obj.context.slide;
                $('li#0 p.ajax_preloader').hide();
                if(slide == undefined) {
                    $('li#0 div.status_error').fadeIn('fast').delay(1000).fadeOut('fast');
                } else {
                    addSlide(slide);   
                }
            }
            
            xajax.call('saveSlide', {
                parameters: [xajax.getFormValues('slide'+ $(this).closest('li.slide').attr('id'))], 
                callback: callback, 
                context: this
            } );
        } else {
            alert('Sie müssen ein Bild für den neuen Slide auswählen bevor Sie diesen hinzufügen können!');
        }
    });
    
    $('.random_effects').click(function() {
        if( $('.random_effects').attr('checked')){
            $('select[name=cSelectedEffects]').attr('disabled',true);
            $('select[name=cAvaibleEffects]').attr('disabled',true);
            $('input.select_add').attr('disabled',true);
            $('input.select_remove').attr('disabled',true);
            $('input[type=hidden][name=cEffects]').attr('disabled',true);
            $('select[name=cSelectedEffects]').html('');
        } else {
            $('select[name=cSelectedEffects]').removeAttr('disabled');
            $('select[name=cAvaibleEffects]').removeAttr('disabled');
            $('input.select_add').removeAttr('disabled');
            $('input.select_remove').removeAttr('disabled');
            $('input[type=hidden][name=cEffects]').removeAttr('disabled');
        }
    });
    
    $('form#slider').submit(function() {
        if( $('.random_effects').attr('checked') != true){
            var effects = new Array();
            $.each($('select[name=cSelectedEffects] option'), function(index,value) {
                effects[index] = $(this).val();
            });
            $('input[name=cEffects]').val(effects.join(';'));
        }
    });
    
    $('input.select_add').click(function() {
        $.each($('select[name=cAvaibleEffects]').val(), function(index,value) {
            var exists = false;
            $.each($('select[name=cSelectedEffects] option'), function(element) {
                if($(this).val() == value) {
                    exists = true;
                }
            });
            
            if(exists == false) {
                var html = '<option value="'+value+'">'+value+'</option>';
                $('select[name=cSelectedEffects]').append(html);
            } else {
                alert('Der Eintrag mit den Wert "'+value+'" existiert bereits!');
            }
        });
    });
    
    $('input.select_remove').click(function() {
        $.each($('select[name=cSelectedEffects] option:selected'), function(index,value) {
            $(this).remove();
        });
    });

    $("select[name='nSeitenTyp']").change(function () {
        var selected = $("select[name='nSeitenTyp'] option:selected");
        typeChanged($(selected).val());
    }).change();
     
    $("select[name='cKey']").change(function () {
        var selected = $("select[name='cKey'] option:selected");
        keyChanged($(selected).val());
    }).change();
     
    $('.nl').find('a').each(function() {
        var type = $(this).attr('id');
        $(this).click(function() {
            show_simple_search(type);
        });
    });
     
    init_simple_search(function(type, res) {
        $(".nl input[name='" + type + "_key']").val(res.kKey);
        $(".nl input[name='" + type + "_name']").val(res.cName);
    });

});

function typeChanged(type) {
    $('.custom').hide();
    $('#type' + type).show();
	  
    if (type != 2) {
        $('select[name="cKey"]').val('');
        $('.nl .key').hide();
        $('.nl input[type="text"], .nl input[type="hidden"]').each(function() {
            $(this).val('');
        });
    }
}

function keyChanged(key) {
    $('.key[id!="key'+key+'"]').find('input').each(function() {
        $(this).val('');
    });

    $('.key').hide();
    $('#key' + key).show();
}
	
function hideOverlayEdit(id) {
    $('li#'+id).children('div.overlay_edit').fadeOut('fast'); 
}

function hideOverlay(id) {
    $('li#'+id).removeClass('active');
    $('li#'+id).addClass('inactive');
    $('li#'+id).children('div.overlay').fadeOut('fast');
    hideOverlayEdit(id);
}

function addSlide(slide) {
    var li = $(document.createElement('li'));
    li.attr('id',slide.kSlide);
    li.addClass('slide');
    li.addClass('inactive');
    li.attr('context',slide.kSlider);
    
    var span = $(document.createElement('span'));
    span.addClass('caption');
    span.addClass('item'+slide.kSlide);
    span.text('Slide #');
    
    var span_number = $(document.createElement('span'));
    span_number.addClass('number');
    span_number.html(slide.nSort);
    span.append(span_number);
    li.append(span);
    
    var img = $(document.createElement('img'));
    img.attr('alt','Slidegrafik');
    img.attr('src',slide.cBildAbsolut);
    li.append(img);
    
    var div_overlay = $(document.createElement('div'));
    div_overlay.addClass('overlay');
    div_overlay.hide();
    li.append(div_overlay);
    
    var div_overlay_edit = $(document.createElement('div'));
    div_overlay_edit.addClass('overlay_edit');
    div_overlay_edit.hide();
    
    var div = $(document.createElement('div'));
    var form = $(document.createElement('form'));
    form.attr('id','slide'+slide.kSlide);
    form.attr('enctype','multipart/form-data');
    
    var input_delete = $(document.createElement('input'));
    input_delete.attr('type','hidden');
    input_delete.attr('name','delete');
    input_delete.val('0');
    form.append(input_delete);
    
    var input_kSlide = $(document.createElement('input'));
    input_kSlide.attr('type','hidden');
    input_kSlide.attr('name','kSlide');
    input_kSlide.val(slide.kSlide);
    form.append(input_kSlide);
    
    var input_kSlider = $(document.createElement('input'));
    input_kSlider.attr('type','hidden');
    input_kSlider.attr('name','kSlider');
    input_kSlider.val(slide.kSlider);
    form.append(input_kSlider);
    
    var input_nSort = $(document.createElement('input'));
    input_nSort.attr('type','hidden');
    input_nSort.attr('name','nSort');
    input_nSort.val(slide.nSort);
    form.append(input_nSort);

    var input_cBild = $(document.createElement('input'));
    input_cBild.attr('type','hidden');
    input_cBild.attr('name','cBild');
    input_cBild.val(slide.cBild);
    form.append(input_cBild);
    
    var fieldset = $(document.createElement('fieldset'));

    var input_select_image = $(document.createElement('input'));
    input_select_image.attr('type','button');
    input_select_image.addClass('add select_image button');
    input_select_image.val('Bild auswählen');
    fieldset.append(input_select_image)
    form.append(fieldset);

    var fieldset = $(document.createElement('fieldset'));

    var label_cTitel = $(document.createElement('label'));
    label_cTitel.attr('for','cTitel');
    label_cTitel.html('Titel');
    fieldset.append(label_cTitel);

    var input_cTitel = $(document.createElement('input'));
    input_cTitel.attr('type','text');
    input_cTitel.attr('name','cTitel');
    input_cTitel.val(slide.cTitel);
    fieldset.append(input_cTitel);
    
    form.append(fieldset);
    
    var fieldset = $(document.createElement('fieldset'));
    
    var label_cText = $(document.createElement('label'));
    label_cText.attr('for','cText');
    label_cText.html('Text');
    fieldset.append(label_cText);
    
    var textarea = $(document.createElement('textarea'));
    textarea.attr('name','cText');
    textarea.html(slide.cText);
    fieldset.append(textarea);
    
    form.append(fieldset);
    
    var fieldset = $(document.createElement('fieldset'));
    
    var label_cLink = $(document.createElement('label'));
    label_cLink.attr('for','cLink');
    label_cLink.html('Link');
    fieldset.append(label_cLink);
    
    var input_cLink = $(document.createElement('input'));
    input_cLink.attr('type','text');
    input_cLink.attr('name','cLink');
    input_cLink.val(slide.cLink);
    fieldset.append(input_cLink);
    
    form.append(fieldset);

    var p_ajax_preloader = $(document.createElement('p'));
    p_ajax_preloader.addClass('ajax_preloader');
    p_ajax_preloader.text('Wird gespeichert...');
    form.append(p_ajax_preloader);
    
    var div_buttons = $(document.createElement('div'));
    div_buttons.addClass('right');
    div_buttons.addClass('buttons');
    
    var input_button_cancel = $(document.createElement('input'));
    input_button_cancel.attr('type','button');
    input_button_cancel.addClass('button');
    input_button_cancel.addClass('blue');
    input_button_cancel.addClass('cancel');
    input_button_cancel.val('Abbrechen');
    div_buttons.append(input_button_cancel);
    
    var input_button_delete = $(document.createElement('input'));
    input_button_delete.attr('type','button');
    input_button_delete.addClass('button');
    input_button_delete.addClass('blue');
    input_button_delete.addClass('slide_delete');
    input_button_delete.val('Löschen');
    div_buttons.append(input_button_delete);
    
    var input_button_save = $(document.createElement('input'));
    input_button_save.attr('type','button');
    input_button_save.addClass('button');
    input_button_save.addClass('blue');
    input_button_save.attr('name','save');
    input_button_save.val('Speichern');
    div_buttons.append(input_button_save);
    
    form.append(div_buttons);
    div.append(form);
    div_overlay_edit.append(div);
    li.append(div_overlay_edit);
    
    var h4 = $(document.createElement('h4'));
    h4.text(slide.cTitel);
    li.append(h4);
    
    var p = $(document.createElement('p'));
    p.text(slide.cText);
    li.append(p);
    
    $('li#0').before(li);
}

function bindOverlay() {
    if($(this).children('div.overlay_edit').css('display') == 'none') {
        $(this).children('div.overlay').fadeIn('fast');
        $(this).children('div.overlay_edit').fadeIn('fast');
    }  
}

