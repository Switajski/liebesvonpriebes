;(function($) {
   $.clickareas = function(options) {
      var unique = 0;
   
      init();
            
      function init() {
         $(options.editor)
            .resizable()
            .draggable();
      
         $(options.editor).find('#submit').click(function() {
            saveEditor();
            return false;
         });
         
         $(options.editor).find('#remove').click(function() {
            var id = $(options.editor).find('#id').val();
            removeArea(id);
            return false;
         });
         
         $(options.save).click(function() {
            var data = JSON.stringify(options.data);
            xajax_saveBannerAreas(data);
            showInfo('Zonen wurden erfolgreich gespeichert');
            return false;
         });
         
         $(options.add).click(function() {
            addArea();
            return false;
         });
      
         width = $(options.id).find('img').attr('width');
         height = $(options.id).find('img').attr('height');
         if (width == 0 && height == 0)
         {
            $(options.id).find('img').bind("load", function () {
               loadImage();
            });
         } else
             loadImage();
         
         $(options.id).click(function() {
            releaseAreas();
         });
      };
      
      function loadImage() {
          width = $(options.id).find('img').attr('width');
          height = $(options.id).find('img').attr('height');
            
          $(options.id).css({
             'width' : width,
             'height' : height
          });

          $(options.data.oArea_arr).each(function(idx, item) {
             addAreaItem(item, false);
          });
      };
      
      function releaseAreas() {
         $(options.id).find('.area').each(function(idx, area) {
            releaseArea(area);
         });
      };
      
      function getData(id) {
         var a = false;
         $(options.data.oArea_arr).each(function(idx, area) {
            if (area.uid == id)
               a = area;
         });
         return a;
      }
      
      function showEditor(area) {
         var id = $(area).attr('ref');
         
         if (data = getData(id)) {
            $(options.editor).find('#title').val(data.cTitel);
            $(options.editor).find('#desc').val(data.cBeschreibung);
            $(options.editor).find('#url').val(data.cUrl);
            $(options.editor).find('#article').val(data.kArtikel);
            $(options.editor).find('#style').val(data.cStyle);
            $(options.editor).find('#id').val(id);
            
            $(options.editor).find('#article_info').html((data.kArtikel > 0) ?
               '<span class="success">Verknüpft</span>' : '<span class="error">Nicht verknüpft</span>');
            
            $(options.editor).show();
         }
      }
      
      function hideEditor() {
         $(options.editor).hide();
      }
      
      function saveEditor() {
         var saved = false;
         var id = $(options.editor).find('#id').val();
         $(options.data.oArea_arr).each(function(idx, item) {
            if (item.uid == id) {
               saved = true;
               item.cTitel = $(options.editor).find('#title').val();
               item.cBeschreibung = $(options.editor).find('#desc').val();
               item.cUrl = $(options.editor).find('#url').val();
               item.kArtikel = $(options.editor).find('#article').val();
               item.cStyle = $(options.editor).find('#style').val();
            }
         });
         showInfo('Zone wurde gespeichert');
      }
      
      function showInfo(text) {
         $(options.info)
            .text(text)
            .fadeIn()
            .delay(1500)
            .fadeOut();
      }
      
      // area events
      function onSelectedArea(area) {
         showEditor(area);
      }
      
      function onReleaseArea(area) {
         hideEditor();
      }
      
      // base functions
      function selectArea(area) {
         releaseAreas();
         $(area).addClass('selected');
         onSelectedArea(area);
      };
      
      function releaseArea(area) {
         $(area).removeClass('selected');
         onReleaseArea(area);
      };
      
      function selectedArea() {
         $(options.id).find('.area').each(function(idx, area) {
            if (isSelectedArea(area))
               return area;
         });
         return false;
      };
      
      function isSelectedArea(area) {
         return $(area).hasClass('selected');
      };
      
      function removeArea(id) {
         $(options.id).find('.area').each(function(idx, area) {
            if ($(this).attr('ref') == id) {
               $(this).remove();
            }
         });
         
         if (area = getData(id)) {
            options.data.oArea_arr = jQuery.grep(options.data.oArea_arr, function(value) {
               return value != area;
            });
         }
         
         hideEditor();
      }
      
      function addArea() {
         var item = {
            oCoords : {w : 100, h: 100, x : 15, y : 15},
            cBeschreibung : '',
            cTitel : '',
            cUrl : '',
            kImageMap : options.data.kImageMap,
            kImageMapArea : 0,
            oArtikel : null,
            kArtikel : 0,
            cStyle: ''
         };
         options.data.oArea_arr.push(item);
         addAreaItem(item, true);
      }
      
      function addAreaItem(item, select) {
         var id = getUID();

         item.uid = id;
         
         var area = $('<div />')
         .css({
            'width' : item.oCoords.w,
            'height' : item.oCoords.h,
            'left' : item.oCoords.x,
            'top' : item.oCoords.y,
            'opacity' : 0.6
         })
         .attr({
            'class' : 'area',
            'ref' : id
         });
         
         var bResize = false;
         area.resizable({
            handles: 'all',
            containment: 'parent',
            start: function(event, ui) {
               bResize = true;
               selectArea(this);
            },
            stop: function(event, ui) {
               bResize = false;
               var id = $(this).attr('ref');
               if (data = getData(id)) {
                  data.oCoords.w = ui.size.width;
                  data.oCoords.h = ui.size.height;
                  o_height = $(options.id).height();
                  o_width = $(options.id).width();
                  if (ui.size.height > o_height - ui.position.top)
                    data.oCoords.h = o_height - ui.position.top;
                  if (ui.size.width > o_width - ui.position.left)
                    data.oCoords.w = o_width - ui.position.left;
                }
            } 
        });
         
         area.draggable({
            containment: 'parent',
            start: function(event, ui) {
               selectArea(this);
            },
            stop: function(event, ui) {
               var id = $(this).attr('ref');
               if (data = getData(id)) {
                  data.oCoords.x = ui.position.left;
                  data.oCoords.y = ui.position.top;
               }
            }
         });
         
         // fix
         area.css('position', 'absolute');
         
         area.click(function(event) {
            event.stopPropagation();
            if (isSelectedArea(this)) {
               if (!bResize)
                  releaseAreas();
            }
            else {
               selectArea(this);
            }
         });
         
         $(options.id).prepend(area);
         
         if (select) {
            selectArea(area);
         }
      }
      
      function getUID() {
         return ++unique;
      }
   };
})(jQuery);