{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="banner"}

{include file="tpl_inc/seite_header.tpl" cTitel=#banner# cBeschreibung=#bannerDesc# cDokuURL=#bannerURL#}

<div id="content">
	{if $cHinweis}
		<p class="box_success">{$cHinweis}</p>
	{/if}

	{if $cFehler}
		<p class="box_error">{$cFehler}</p>
        {if isset($cPlausi_arr.vDatum)}
            <p class="box_error">{if $cPlausi_arr.vDatum == 1}Konnte Ihre Eingabe f&uuml;r das 'Aktiv von Datum' nicht verarbeiten{/if}</p>
        {/if}
        {if isset($cPlausi_arr.bDatum)}
            <p class="box_error">
            {if $cPlausi_arr.bDatum == 1}
                Konnte Ihre Eingabe f&uuml;r das 'Aktiv bis Datum' nicht verarbeiten
            {elseif $cPlausi_arr.bDatum == 2}
                Das Datum bis wann ein Banner aktiv ist muss gr&ouml;&szlig;er sein als das 'Aktiv von Datum'
            {/if}
            </p>
        {/if}
	{/if}

   {if $cAction == 'edit' || $cAction == 'new'}
   <script>
   {literal}
   $(document).ready(function() {
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
      // reset keys
      $('.key[id!="key'+key+'"]').find('input').each(function() {
         $(this).val('');
      });

      $('.key').hide();
      $('#key' + key).show();
   }

   {/literal}
   </script>

   <div id="settings">
      <form action="" method="post" enctype="multipart/form-data">
         <input type="hidden" name="action" value="{$cAction}" />
         {if $cAction == 'edit'}
            <input type="hidden" name="kImageMap" value="{$oBanner->kImageMap}" />
         {/if}

         <div class="category first">
            Allgemein
         </div>

         <div class="item">
            <div class="name"><label for="cName">Interner Name *</label></div>
            <div class="for"><input type="text" name="cName" id="cName" value="{if isset($cName)}{$cName}{elseif isset($oBanner->cTitel)}{$oBanner->cTitel}{/if}" /></div>
         </div>

         <div class="item">
            <div class="name"><label for="cName">Banner *</label></div>
            <div class="for">
               <input type="file" name="oFile" />
            </div>
         </div>

         <div class="item">
            <div class="name"><label for="cName">&raquo; vorhandene Datei w&auml;hlen</label></div>
            <div class="for">
               {if $cBannerFile_arr|@count > 0}
               <select name="cPath">
                  <option value="">Banner w&auml;hlen</option>
                  {foreach from=$cBannerFile_arr item=cBannerFile}
                     <option value="{$cBannerFile}" {if (isset($oBanner->cBildPfad) && $cBannerFile == $oBanner->cBildPfad) || (isset($oBanner->cBild) && $cBannerFile == $oBanner->cBild)}selected="selected"{/if}>{$cBannerFile}</option>
                  {/foreach}
               </select>
               {else}
                  Kein Banner im Ordner <strong>{$cBannerLocation}</strong> vorhanden
               {/if}
            </div>
         </div>

        <div class="item">
            <div class="name">
               <label for="vDatum">Aktiv von</label>
            </div>
            <div class="for">
               <input type="text" name="vDatum" id="vDatum" value="{if isset($vDatum) && $vDatum > 0}{$vDatum|date_format:"%d.%m.%Y"}{elseif isset($oBanner->vDatum) && $oBanner->vDatum > 0}{$oBanner->vDatum|date_format:"%d.%m.%Y"}{/if}" />
            </div>
         </div>

         <div class="item">
            <div class="name">
               <label for="bDatum">Aktiv bis</label>
            </div>
            <div class="for">
               <input type="text" name="bDatum" id="bDatum" value="{if isset($bDatum) && $bDatum > 0}{$bDatum|date_format:"%d.%m.%Y"}{elseif isset($oBanner->bDatum) && $oBanner->bDatum > 0}{$oBanner->bDatum|date_format:"%d.%m.%Y"}{/if}" />
            </div>
         </div>

         {* extensionpoint begin *}

         <div class="category">
            Anzeigeoptionen
         </div>

         <div class="item">
            <div class="name">
               <label for="kSprache">Sprache</label>
            </div>
            <div class="for">
               <select name="kSprache">
               <option value="0">Alle</option>
               {foreach from=$oSprachen_arr item=oSprache}
                  <option value="{$oSprache->kSprache}" {if isset($kSprache) && $kSprache == $oSprache->kSprache}selected="selected"{elseif $oExtension->kSprache == $oSprache->kSprache}selected="selected"{/if}>{$oSprache->cNameDeutsch}</option>
               {/foreach}
               </select>
            </div>
         </div>

         <div class="item">
            <div class="name">
               <label for="kKundengruppe">Kundengruppe</label>
            </div>
            <div class="for">
               <select name="kKundengruppe">
               <option value="0">Alle</option>
               {foreach from=$oKundengruppe_arr item=oKundengruppe}
                  <option value="{$oKundengruppe->getKundengruppe()}" {if isset($kKundengruppe) && $kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected" {elseif $oExtension->kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected"{/if}>{$oKundengruppe->getName()}</option>
               {/foreach}
               </select>
            </div>
         </div>

         <div class="item">
            <div class="name">
               <label for="nSeitenTyp">Seitentyp</label>
            </div>
            <div class="for">
               <select name="nSeitenTyp">
                   {if isset($nSeitenTyp) && intval($nSeitenTyp) > 0}
                        {include file="tpl_inc/seiten_liste.tpl" nPage=$nSeitenTyp}
                    {else}
                    {include file="tpl_inc/seiten_liste.tpl" nPage=$oExtension->nSeite}
                  {/if}
               </select>
            </div>
         </div>

         <div id="type2" class="custom">
            <div class="item">
               <div class="name">
                  <label for="cKey">&raquo; Filter</label>
               </div>
               <div class="for">
                  <div>
                     <select name="cKey">
                        <option value="" {if isset($oExtension->cKey) && $oExtension->cKey == ""}selected="selected"{/if}>Kein Filter</option>
                        <option value="kTag" {if isset($cKey) && $cKey == "kTag"}selected="selected"{elseif isset($oExtension->cKey) && $oExtension->cKey == "kTag"}selected="selected"{/if}>Tag</option>
                        <option value="kMerkmalWert" {if isset($cKey) && $cKey == "kMerkmalWert"}selected="selected"{elseif isset($oExtension->cKey) && $oExtension->cKey == "kMerkmalWert"}selected="selected"{/if}>Merkmal</option>
                        <option value="kKategorie" {if isset($cKey) && $cKey == "kKategorie"}selected="selected"{elseif isset($oExtension->cKey) && $oExtension->cKey == "kKategorie"}selected="selected"{/if}>Kategorie</option>
                        <option value="kHersteller" {if isset($cKey) && $cKey == "kHersteller"}selected="selected"{elseif isset($oExtension->cKey) && $oExtension->cKey == "kHersteller"}selected="selected"{/if}>Hersteller</option>
                        <option value="cSuche" {if isset($cKey) && $cKey == "cSuche"}selected="selected"{elseif isset($oExtension->cKey) && $oExtension->cKey == "cSuche"}selected="selected"{/if}>Suchbegriff</option>
                     </select>
                  </div>

                  <div class="nl">
                     <div id="keykTag" class="key">
                        <input type="hidden" name="tag_key" value="{if (isset($cKey) && $cKey == "kTag") || (isset($oExtension->cKey) && $oExtension->cKey == "kTag")}{$oExtension->cValue}{/if}" />
                        <input type="text" name="tag_name" disabled="disabled" value="{if (isset($cKey) && $cKey == "kTag") || (isset($oExtension->cKey) && $oExtension->cKey == "kTag")}{if $tag_key != ""}{$tag_key}{elseif $oExtension->cValue != ""}{$oExtension->cValue}{else}Kein Tag ausgew&auml;hlt{/if}{/if}" />
                        <a href="#" class="button edit" id="tag">Tag suchen</a>
                     </div>
                     <div id="keykMerkmalWert" class="key">
                        <input type="hidden" name="attribute_key" value="{if (isset($cKey) && $cKey == "kMerkmalWert") || (isset($oExtension->cKey) && $oExtension->cKey == "kMerkmalWert")}{$oExtension->cValue}{/if}" />
                        <input type="text" name="attribute_name" disabled="disabled" value="{if (isset($cKey) && $cKey == "kMerkmalWert") || (isset($oExtension->cKey) && $oExtension->cKey == "kMerkmalWert")}{if $attribute_key != ""}{$attribute_key}{elseif $oExtension->cValue != ""}{$oExtension->cValue}{else}Kein Merkmal ausgew&auml;hlt{/if}{/if}" />
                        <a href="#" class="button edit" id="attribute">Merkmal suchen</a>
                     </div>
                     <div id="keykKategorie" class="key">
                        <input type="hidden" name="categories_key" value="{if (isset($cKey) && $cKey == "kKategorie") || (isset($oExtension->cKey) && $oExtension->cKey == "kKategorie")}{$oExtension->cValue}{/if}" />
                        <input type="text" name="categories_name" disabled="disabled" value="{if (isset($cKey) && $cKey == "kKategorie") || (isset($oExtension->cKey) && $oExtension->cKey == "kKategorie")}{if $categories_key != ""}{$categories_key}{elseif $oExtension->cValue != ""}{$oExtension->cValue}{else}Keine Kategorie ausgew&auml;hlt{/if}{/if}" />
                        <a href="#" class="button edit" id="categories">Kategorie suchen</a>
                     </div>
                     <div id="keykHersteller" class="key">
                        <input type="hidden" name="manufacturer_key" value="{if (isset($cKey) && $cKey == "kHersteller") || (isset($oExtension->cKey) && $oExtension->cKey == "kHersteller")}{$oExtension->cValue}{/if}" />
                        <input type="text" name="manufacturer_name" disabled="disabled" value="{if (isset($cKey) && $cKey == "kHersteller") || (isset($oExtension->cKey) && $oExtension->cKey == "kHersteller")}{if $manufacturer_key != ""}{$manufacturer_key}{elseif $oExtension->cValue != ""}{$oExtension->cValue}{else}Kein Hersteller ausgew&auml;hlt{/if}{/if}" />
                        <a href="#" class="button edit" id="manufacturer">Hersteller suchen</a>
                     </div>
                     <div id="keycSuche" class="key">
                        <input type="text" name="keycSuche" value="{if (isset($cKey) &&  $cKey == "cSuche") || $oExtension->cKey == "cSuche"}{if $keycSuche != ''}{$keycSuche}{else}{$oExtension->cValue}{/if}{/if}" />
                     </div>
                  </div>
               </div>
            </div>
         </div>

         {include file="tpl_inc/single_search_browser.tpl"}

         {* extensionpoint end *}

         <div class="save_wrapper">
            <input type="submit" class="button orange" value="Banner speichern" />
         </div>

      </form>
   </div>
   {elseif $cAction == 'area'}
      <link rel="stylesheet" href="{$URL_SHOP}/{$PFAD_ADMIN}/{$currentTemplateDir}css/clickareas.css" type="text/css" media="screen" />
      <script type="text/javascript" src="{$URL_SHOP}/includes/libs/flashchart/js/json/json2.js"></script>
      <script type="text/javascript" src="{$URL_SHOP}/{$PFAD_ADMIN}/{$currentTemplateDir}js/clickareas.js"></script>
      <script type="text/javascript">
      $(function() {ldelim}
         $.clickareas({ldelim}
            'id' : '#area_wrapper',
            'editor' : '#area_editor',
            'save' : '#area_save',
            'add' : '#area_new',
            'info' : '#area_info',
            'data' : {$oBanner|json_encode}
         {rdelim});
      {rdelim});
      </script>

      <script>
      {literal}
      $(document).ready(function() {
         $('#article_browser').click(function() {
            show_simple_search('article');
            return false;
         });

         init_simple_search(function(type, res) {
            $('#article').val(res.kPrimary);
            $('#article_info').html((res.kPrimary > 0) ?
               '<span class="success">Verkn&uuml;pft</span>' : '<span class="error">Nicht verkn&uuml;pft</span>');
         });

         $('#article_unlink').click(function() {
            $('#article').val(0);
            $('#article_info').html('<span class="error">Nicht verkn&uuml;pft</span>');
            return false;
         });
      });
      {/literal}
      </script>

      <div class="category clearall">
         <div class="left">Zonen</div>
         <div class="right" id="area_info"></div>
      </div>

      {include file="tpl_inc/single_search_browser.tpl"}

      <div id="area_container">

         <div id="area_editor">

            <div class="category first">
               Einstellungen
            </div>

            <div id="settings">

               <div class="item">
                  <div class="name">
                     <label for="title">
                        Titel
                     </label>
                  </div>
                  <div class="for">
                     <input type="text" id="title" name="title" />
                  </div>
               </div>

               <div class="item">
                  <div class="name">
                     <label for="desc">
                        Beschreibung
                     </label>
                  </div>
                  <div class="for">
                     <textarea id="desc" name="desc"></textarea>
                  </div>
               </div>

               <div class="item">
                  <div class="name">
                     <label for="url">
                        Url
                     </label>
                  </div>
                  <div class="for">
                     <input type="text" id="url" name="url" />
                  </div>
               </div>

               <div class="item">
                  <div class="name">
                     <label for="style">
                        CSS-Klasse
                     </label>
                  </div>
                  <div class="for">
                     <input type="text" id="style" name="style" />
                  </div>
               </div>

               <div class="item">
                  <div class="name">
                     <label for="article">
                        Artikel
                     </label>
                  </div>
                  <div class="for">
                     <p style="margin-bottom: 5px" id="article_info"></p>
                     <input type="hidden" name="article" id="article" value="{$oBanner->kArtikel}" />
                     <a href="#" class="button edit" id="article_browser">Artikel w&auml;hlen</a>
                     <a href="#" class="button edit" id="article_unlink">L&ouml;sen</a>
                  </div>
               </div>

               <div class="save_wrapper">
                  <input type="hidden" name="id" id="id" />
                  <button type="button" class="button blue" id="submit">speichern</button>
                  <button type="button" class="button blue" id="remove">l&ouml;schen</button>
               </div>

            </div>
         </div>

         <div id="area_wrapper">
            <img src="{$oBanner->cBildPfad}" title="" id="clickarea" />
         </div>
      </div>

      <div class="save_wrapper">
         <a class="button orange" href="#" id="area_new">Neue Zone</a>
         <a class="button orange" href="#" id="area_save">Zonen speichern</a>
      </div>
   {else}

         <div id="settings">
            <table class="list">
               <thead>
                  <tr>
                     <th class="tleft" width="60%">Name</th>
                     <th width="20%">Status</th>
                     <th width="20%">Optionen</th>
                  </tr>
               </thead>
               <tbody>
                  {foreach name="banner" from=$oBanner_arr item=oBanner}
                  <tr>
                     <td class="tleft">
                        {$oBanner->cTitel}
                     </td>
                     <td class="tcenter">
                        <span class="success">aktiv</span>
                     </td>
                     <td class="tcenter">
                        <a class="button add" href="banner.php?action=area&id={$oBanner->kImageMap}">verlinken</a>
                        <a class="button edit" href="banner.php?action=edit&id={$oBanner->kImageMap}">bearbeiten</a>
                        <a class="button remove" href="banner.php?action=delete&id={$oBanner->kImageMap}">entfernen</a>
                     </td>
                  </tr>
                  {/foreach}
               </tbody>
            </table>

            {if $oBanner_arr|@count == 0}
               <p class="box_info">{#noDataAvailable#}</p>
            {/if}

            <div class="save_wrapper">
               <a class="button orange" href="banner.php?action=new">Banner hinzuf&uuml;gen</a>
            </div>
         </div>
   {/if}
</div>
{include file='tpl_inc/footer.tpl'}