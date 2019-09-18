<div class="widget-custom-data">
	<script type="text/javascript">
		{literal}
		$(function() {
			$('#select_kampagne').change(function() {
				var kKampagne = $('#select_kampagne option:selected').val();
				window.location = 'index.php?kKampagne=' + kKampagne;
			});
		});
		{/literal}
	</script>
	<select name="kKampagne" id="select_kampagne" class="form-control">
	{foreach from=$oKampagne_arr item=oKampagne}
		<option value="{$oKampagne->kKampagne}" {if $oKampagne->kKampagne == $kKampagne}selected="selected"{/if}>{$oKampagne->cName}</option>
	{/foreach}
	</select>
    <table class="table">
        <thead>
            <tr>
            <th class="tleft">Statistik</th>
            <th class="tcenter">{$oKampagneStat_arr[$cType_arr.0].cDatum}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$oKampagneDef_arr item=oKampagneDef}
                {assign var=kKampagneDef value=$oKampagneDef->kKampagneDef}
                <tr>
                    <td class="tleft">{$oKampagneDef->cName}</td>
                    <td class="tcenter">{$oKampagneStat_arr[$cType_arr.0][$kKampagneDef]}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>