{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shopzuruecksetzen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopzuruecksetzen"}
{include file="tpl_inc/seite_header.tpl" cTitel=#shopReset# cBeschreibung=#shopResetDesc# cDokuURL=#shopResetURL#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	 
	 <form name="login" method="post" action="shopzuruecksetzen.php">
	 <input type="hidden" name="zuruecksetzen" value="1" />
	 
	 <div class="category">{#areas#}</div>
	 <div id="settings">
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="artikel" tabindex="3" id="Artikel" /> <label for="Artikel">Artikel, Kategorien, Merkmale löschen (Komplettübertragung aus JTL-Wawi füllt diese Daten wieder auf)</label>
		  </div>
	 </div>
		  
	 <div class="category">Shopinhalte</div>
	 <div id="settings">
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="news" tabindex="4" id="News" /> <label for="News">News löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="bestseller" tabindex="5" id="Bestseller" /> <label for="Bestseller">Bestseller löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="besucherstatistiken" tabindex="6" id="Besucherstatistiken" /> <label for="Besucherstatistiken">Besucherstatistiken löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="preisverlaeufe" tabindex="8" id="Preisverlaufe" /> <label for="Preisverlaufe">Preisverläufe löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="umfragen" tabindex="9" id="Umfragen" /> <label for="Umfragen">Umfragen löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="verfuegbarkeitsbenachrichtigungen" tabindex="10" id="Verfugbarkeitsbenachrichtigungen" /> <label for="Verfugbarkeitsbenachrichtigungen">Verfügbarkeitsbenachrichtigungen löschen</label>
		  </div>
		  
	 </div>

	 <div class="category">Benutzergenerierte Inhalte</div>
	 <div id="settings">
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="suchanfragen" tabindex="11" id="Suchanfragen" /> <label for="Suchanfragen">Suchanfragen löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="tags" tabindex="12" id="Tags" /> <label for="Tags">Tags löschen</label>
		  </div>
		  
		  <div class="item">
				<input type="checkbox" name="cOption_arr[]" value="bewertungen" tabindex="13" id="Bewertungen" /> <label for="Bewertungen">Bewertungen löschen</label>
		  </div>
	 </div>
	 <div class="save_wrapper">
		  <input type="submit" value="{#shopReset#}" class="button orange" />
	 </div>
	 </form>
</div>
{include file='tpl_inc/footer.tpl'}