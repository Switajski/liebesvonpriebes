<h1>Nach Artikel suchen</h1>
<fieldset>
	<input type="text" id="article_list_input" value="{if isset($cSearch)}{$cSearch}{/if}" autocomplete="off" />

	<div class="select_wrapper">
		<div class="search">
			<h2>Gefundene Artikel</h2>
			<select multiple="multiple" name="article_list_found">
			</select>
		</div>
		
		<div class="added">
			<h2>Gew&auml;hlte Artikel</h2>
			<select multiple="multiple" name="article_list_selected">
			</select>
		</div>
		
		<div class="clear"></div>
	</div>

	<div class="tcenter">
		<a href="#" class="button add" id="article_list_save">Speichern</a>
		<a href="#" class="button remove" id="article_list_cancel">Abbrechen</a>
	</div>
</fieldset>
