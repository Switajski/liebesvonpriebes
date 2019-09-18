{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: navigationsfilter.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="navigationsfilter"}

<script type="text/javascript">
var bManuell = false;

function selectCheck(selectBox)
{ldelim}
	if(selectBox.selectedIndex == 1)
	{ldelim}
	
		// Laden falls vorhanden
		{if isset($oPreisspannenfilter_arr) && $oPreisspannenfilter_arr|@count > 0}
			{assign var=i value=1}
			{foreach name=werte from=$oPreisspannenfilter_arr item=oPreisspannenfilter}				
				document.getElementById("nVon_{$smarty.foreach.werte.iteration-$i}").value = {$oPreisspannenfilter->nVon}
				document.getElementById("nBis_{$smarty.foreach.werte.iteration-$i}").value = {$oPreisspannenfilter->nBis}
			{/foreach}
		{/if}
		
		document.getElementById("Werte").style.display = "block";
        bManuell = true;
	{rdelim}
	else if(selectBox.selectedIndex == 0)
	{ldelim}
		document.getElementById("Werte").style.display = "none";
        bManuell = false;
	{rdelim}
{rdelim}

// Plausibilit�tspr�fung
function speicherDaten()
{ldelim}
	if(bManuell == true)
	{ldelim}	
		var bCheck = true;
		var cFehler = "";
		
		// Resetten
		for(var j=0; j<10; j++)
		{ldelim}
			document.getElementById("nVon_" + j).style.background = "#FFFFFF";
			document.getElementById("nBis_" + j).style.background = "#FFFFFF";
		{rdelim}
				
		for(var i=0; i<10; i++)
		{ldelim}
			if(i > 0) // Zeilen >= 2
			{ldelim}
				if(document.getElementById("nVon_" + i).value.length > 0)
				{ldelim}
				
					// Wenn das Feld "nVon" gesetzt wurde, muss auch "nBis" gesetzt werden
					if(document.getElementById("nBis_" + i).value.length > 0)
					{ldelim}
					
						// Wenn beide Felder gesetzt wurde, muss der Wert von "nVon" < sein als "nBis"						
						if(parseFloat(document.getElementById("nVon_" + i).value) < parseFloat(document.getElementById("nBis_" + i).value))
						{ldelim}
						
							// Wenn beide Felder gesetzt wurden, "nVon" < ist als "nBis", dann muss "nVon" aus der Iteration > sein als "nBis" von der Iteration -1 
							if(parseFloat(document.getElementById("nVon_" + i).value) < parseFloat(document.getElementById("nBis_" + (i - 1)).value))
							{ldelim}
								bCheck = false;
								cFehler += "Fehler: Das Feld \"Von\" muss gr&ouml;&szlig;er oder gleich sein, als das Feld \"Bis\" von der voherigen Zeile.";
								document.getElementById("nVon_" + i).style.background = "#FFE4E1";
							{rdelim}
						{rdelim}
						else
						{ldelim}
							bCheck = false;
							cFehler += "Fehler: Das Feld \"Von\" muss kleiner sein als \"Bis\".";
							document.getElementById("nVon_" + i).style.background = "#FFE4E1";
							document.getElementById("nBis_" + i).style.background = "#FFE4E1";
						{rdelim}
					{rdelim}
					else
					{ldelim}
						bCheck = false;
						cFehler += "Fehler: Wenn \"Von\" gesetzt wurd, muss auch \"Bis\" gesetzt werden.";
						document.getElementById("nBis_" + i).style.background = "#FFE4E1";
					{rdelim}
				{rdelim}
			{rdelim}
			else // Erster Durchlauf, Zeile 1
			{ldelim}				
				if(document.getElementById("nVon_" + i).value.length > 0 && document.getElementById("nBis_" + i).value.length > 0)
				{ldelim}
					if(parseFloat(document.getElementById("nVon_" + i).value) >= parseFloat(document.getElementById("nBis_" + i).value))
					{ldelim}
						bCheck = false;
						cFehler = "Fehler: Das Feld \"Von\" muss kleiner sein als \"Bis\".";
						document.getElementById("nVon_" + i).style.background = "#FFE4E1";
						document.getElementById("nBis_" + i).style.background = "#FFE4E1";
					{rdelim}
				{rdelim}
				else
				{ldelim}
					bCheck = false;
					cFehler = "Fehler: Keiner der beiden Felder darf leer sein.";
					document.getElementById("nVon_" + i).style.background = "#FFE4E1";
					document.getElementById("nBis_" + i).style.background = "#FFE4E1";
				{rdelim}
			{rdelim}
		{rdelim}
		
		if(!bCheck)
		{ldelim}
			//document.getElementById("Werte").innerHTML += cFehler;
			alert(cFehler);
		{rdelim}
		else // Alles O.K. -> Form abschicken
		{ldelim}
			document.einstellen.submit();
		{rdelim}
	{rdelim}
	else
	{ldelim}
		document.einstellen.submit();
	{rdelim}
{rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#navigationsfilter# cBeschreibung=#navigationsfilterDesc# cDokuURL=#navigationsfilterUrl#}
<div id="content">   
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
   
   <div class="container">
      <form name="einstellen" method="post" action="navigationsfilter.php" id="einstellen">
      <input type="hidden" name="{$session_name}" value="{$session_id}">
      <input type="hidden" name="speichern" value="1">
      <div id="settings">
         {foreach name=conf from=$oConfig_arr item=oConfig}
            {if $oConfig->cConf == "Y"}
               <div class="item">
                  <div class="name">
                     <label for="{$oConfig->cWertName}">
                        {$oConfig->cName} <span class="sid">{$oConfig->kEinstellungenConf} &raquo;</span>
                     </label>
                  </div>
                  <div class="for">
                     {if $oConfig->cInputTyp=="selectbox"}
                        <select name="{$oConfig->cWertName}" class="combo"{if $oConfig->cWertName == "preisspannenfilter_anzeige_berechnung"} id="preisspannenfilter_anzeige_berechnung" onChange="javascript:selectCheck(this);"{/if}> 
                           {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                              <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                           {/foreach}
                        </select>
                        {if $oConfig->cWertName == "preisspannenfilter_anzeige_berechnung"}
                           <div id="Werte" style="display: {if $oConfig->gesetzterWert == 'M'}block;{else}none;{/if}">
                               {section name="werte" start=0 loop=10 step=1}
                               <p id="zeile_{$smarty.section.werte.index}">{#navigationsfilterFrom#}: <input name="nVon[]" type="text" id="nVon_{$smarty.section.werte.index}" value="{if isset($oPreisspannenfilter_arr[$smarty.section.werte.index]->nVon)}{$oPreisspannenfilter_arr[$smarty.section.werte.index]->nVon}{/if}"> - {#navigationsfilterTo#}: <input name="nBis[]" type="text" id="nBis_{$smarty.section.werte.index}" value="{if isset($oPreisspannenfilter_arr[$smarty.section.werte.index]->nBis)}{$oPreisspannenfilter_arr[$smarty.section.werte.index]->nBis}{/if}"></p>
                               {/section}
                           </div> 
                        {/if} 
                     {else}
                        <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
                     {/if}
                     
                     {if $oConfig->cBeschreibung}
                        <div class="help" ref="{$oConfig->kEinstellungenConf}" title="{$oConfig->cBeschreibung}"></div>
                     {/if}
                     
                  </div>
               </div>
            {else}
               {if $oConfig->cName}
                  <div class="category">
                     {$oConfig->cName}
                     <div class="right">
                        <p class="sid">{$oConfig->kEinstellungenConf}</p>
                     </div>
                  </div>
               {/if}
            {/if}
         {/foreach}
      </div>
      
      <p class="submit"><input name="speichern" class="button orange" type="button" value="{#navigationsfilterSave#}" onclick="javascript:speicherDaten();"></p>
      </form>
   </div>		
</div>

<script type="text/javascript">
   selectCheck(document.getElementById('preisspannenfilter_anzeige_berechnung'));
</script>

{include file='tpl_inc/footer.tpl'}
