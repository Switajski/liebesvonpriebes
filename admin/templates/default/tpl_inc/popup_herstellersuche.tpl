<h1>Nach Hersteller suchen</h1>
<fieldset>
	<input type="text" id="manufacturer_list_input" value="{$cSearch}" autocomplete="off" />

	<div class="select_wrapper">
		<div class="search">
			<h2>Gefundene Hersteller</h2>
			<select multiple="multiple" name="manufacturer_list_found">
			</select>
		</div>
		
		<div class="added">
			<h2>Gew&auml;hlte Hersteller</h2>
			<select multiple="multiple" name="manufacturer_list_selected">
			</select>
		</div>
		
		<div class="clear"></div>
	</div>

	<div class="tcenter">
		<a href="#" class="button add" id="manufacturer_list_save">Speichern</a>
		<a href="#" class="button remove" id="manufacturer_list_cancel">Abbrechen</a>
	</div>
</fieldset>
