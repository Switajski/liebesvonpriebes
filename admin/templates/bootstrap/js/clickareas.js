;(function($) {
   $.clickareas = function(options) {
       window.options = options;
      $(window).on("load", function() {
         var unique = 0;
         var factor = ($('#clickarea').prop("naturalWidth") / $('#clickarea').prop("width")).toFixed(2);
         init();

         function init() {
            $(options.editor)
               .resizable()
               .draggable();

            $(options.editor).find('#remove').click(function() {
               var id = $(options.editor).find('#id').val();
               removeArea(id);
               return false;
            });

            $(options.save).click(function() {
               saveEditor();
               var data = JSON.stringify(options.data);
               ioCall('saveBannerAreas', [data]);
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
               $(options.editor).find('#article_id').val(data.kArtikel);
               $(options.editor).find('#article_name').val(data.oArtikel ? data.oArtikel.cName : '');
               $(options.editor).find('#style').val(data.cStyle);
               $(options.editor).find('#id').val(id);

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

                  if(!item.oArtikel) {
                      item.oArtikel = { cName: '' };
                  }

                  item.cTitel = $(options.editor).find('#title').val();
                  item.cBeschreibung = $(options.editor).find('#desc').val();
                  item.cUrl = $(options.editor).find('#url').val();
                  item.kArtikel = $(options.editor).find('#article_id').val();
                  item.oArtikel.cName = $(options.editor).find('#article_name').val();
                  item.cStyle = $(options.editor).find('#style').val();
               }
            });
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
            saveEditor();
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
               oCoords : {w : Math.round(100*factor), h: Math.round(100*factor), x : Math.round(15*factor), y : Math.round(15*factor)},
               cBeschreibung : '',
               cTitel : '',
               cUrl : '',
               kImageMap : options.data.kImageMap,
               kImageMapArea : 0,
               oArtikel : null,
               kArtikel : 0,
               cStyle : ''
            };
            options.data.oArea_arr.push(item);
            addAreaItem(item, true);
         }

         function addAreaItem(item, select) {
            var id = getUID();
            item.uid = id;
            var area = $('<div />')
            .css({
                'width' : Math.round(item.oCoords.w/factor),
                'height' : Math.round(item.oCoords.h/factor),
                'left' : Math.round(item.oCoords.x/factor),
                'top' :Math.round( item.oCoords.y/factor),
                'opacity' : 0.6,
                'z-index' : item.uid
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
                     data.oCoords.w = Math.round(ui.size.width*factor);
                     data.oCoords.h = Math.round(ui.size.height*factor);
                     o_height = Math.round($(options.id).height()*factor);
                     o_width = Math.round($(options.id).width()*factor);
                     if (ui.size.height > o_height - ui.position.top)
                       data.oCoords.h = Math.round((o_height - ui.position.top)*factor);
                     if (ui.size.width > o_width - ui.position.left)
                       data.oCoords.w = Math.round((o_width - ui.position.left)*factor);
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
                     data.oCoords.x = Math.round(ui.position.left*factor);
                     data.oCoords.y = Math.round(ui.position.top*factor);
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
      });
   };
})(jQuery);