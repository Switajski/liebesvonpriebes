var iNettuts = {
    jQuery : $,
    settings : {
        columns : '.column',
        widgetSelector: '.widget',
        moveSelector: '.widget-head',
        optionSelector: '.widget-head p',
        contentSelector: '.widget-content',
        deactivatedSelector: '#column4',
        widgetDefault : {
            movable: true,
            removable: true,
            collapsible: true
        },
        widgetIndividual : {
            deactivated : {
                movable: true,
                removable: false,
                collapsible: false
            }
        }
    },

    init : function () {
        this.addWidgetControls();
        this.makeSortable();
    },
    
    getWidgetSettings : function (id) {
        var $ = this.jQuery,
            settings = this.settings;
        return (id&&settings.widgetIndividual[id]) ? $.extend({},settings.widgetDefault,settings.widgetIndividual[id]) : settings.widgetDefault;
    },
    
    addWidgetControls : function () {
        var iNettuts = this,
            $ = this.jQuery,
            settings = this.settings;
            
        $(settings.widgetSelector, $(settings.columns)).each(function () {
            var thisWidgetSettings = iNettuts.getWidgetSettings(this.id);
            if (thisWidgetSettings.collapsible) {
                var collapse = $('<a href="#" class="collapse"></a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).appendTo($(settings.optionSelector,this));
                
                if ($(collapse).parents(settings.widgetSelector).find(settings.contentSelector).is(':hidden'))
                    $(collapse).css({backgroundPosition: '-38px 0'});
                
                $(collapse).bind('click', function() {
                    id = $(this).parent().parent().parent().attr('ref');
                    if ($(collapse).parents(settings.widgetSelector).find(settings.contentSelector).is(':hidden'))
                    {
                        xajax_expandWidgetAjax(id, 1);
                        $(collapse).css({backgroundPosition: ''})
                        $(collapse).parents(settings.widgetSelector).find(settings.contentSelector).slideDown('fast');
                    }
                    else
                    {
                        xajax_expandWidgetAjax(id, 0);
                        $(collapse).css({backgroundPosition: '-38px 0'})
                        $(collapse).parents(settings.widgetSelector).find(settings.contentSelector).slideUp('fast');
                    }
                    return false;
                });
            }
            
            if (thisWidgetSettings.removable) {
                $('<a href="#" class="remove"></a>').mousedown(function (e) {
                    e.stopPropagation();    
                }).click(function () {
                    title = $(this).parents(settings.moveSelector).find('h3').text();
                    if(confirm('Widget ' + title + ' wirklich entfernen?')) {
                        wContainer = $(this).parents(settings.widgetSelector);
                        id = $(wContainer).attr('ref');
                        xajax_closeWidgetAjax(id);
                        $(wContainer).slideUp('fast');
                    }
                    return false;
                }).appendTo($(settings.optionSelector, this));
            }
        });
    },
    
    makeSortable : function () {
        var iNettuts = this,
            $ = this.jQuery,
            settings = this.settings,
            $sortableItems = (function () {
                var notSortable = '';
                $(settings.widgetSelector,$(settings.columns)).each(function (i) {
                    if (!iNettuts.getWidgetSettings(this.id).movable) {
                        if(!this.id) {
                            this.id = 'widget-no-id-' + i;
                        }
                        notSortable += '#' + this.id + ',';
                    }
                });
                return $(settings.columns + ' > li').not(notSortable);
            })();
        
        $sortableItems.find(settings.moveSelector).css({
            cursor: 'move'
        }).mousedown(function (e) {
            $sortableItems.css({width:''});
            $(this).parent().css({
                width: $(this).parent().width() + 'px'
            });
            // $(this).parent().parent().attr('id', '');
        }).mouseup(function () {
            if(!$(this).parent().hasClass('dragging')) {
                $(this).parent().css({width:''});
            } else {
                $(settings.columns).sortable('disable');
            }
        });

        $(settings.columns).sortable({
            items: $sortableItems,
            connectWith: $(settings.columns),
            handle: settings.moveSelector,
            placeholder: 'widget-placeholder',
            forcePlaceholderSize: true,
            revert: true,
            delay: 100,
            opacity: 0.8,
            tolerance: 'intersect',
            containment: 'document',
            start: function (e,ui) {
                $(ui.helper).addClass('dragging');
            },
            stop: function (e,ui) {
                $(ui.item).css({width:''}).removeClass('dragging');
                $(settings.columns).sortable('enable');
                
                id = $(ui.item).attr('ref');
                container = $(ui.item).parent().attr('id');
                
                $('#' + container + ' .widget').each(function(idx, item) {
                    id = $(this).attr('ref');
                    xajax_setWidgetPositionAjax(id, container, idx);
                    // console.log('Widget: ' + id + ', Container: ' + container + ', Position: ' + idx);
                });
            }
        });
    }
};

iNettuts.init();