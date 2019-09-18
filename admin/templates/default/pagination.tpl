{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: pagination.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}

{if $oBlaetterNavi->nAktiv == 1}
    <div class="block clearall">
        <div class="pages tleft">
        <span class="pageinfo">Eintrag: <strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} von {$oBlaetterNavi->nAnzahl}</span>
        {if $oBlaetterNavi->nAktuelleSeite == 1}
            &laquo;
        {else}
            <a class="back" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nVoherige}&{$cParams}">&laquo;</a>
        {/if}
        
        {if $oBlaetterNavi->nAnfang != 0}
            <a class="page" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nAnfang}&{$cParams}">{$oBlaetterNavi->nAnfang}</a> ...
        {/if}
        {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
                <a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="{$cUrl}?s{$cSite}={$Blatt}&{$cParams}">{$Blatt}</a> 
        {/foreach}
        
        {if $oBlaetterNavi->nEnde != 0}
            ... <a class="page" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nEnde}&{$cParams}">{$oBlaetterNavi->nEnde}</a>
        {/if}
        
        {if $oBlaetterNavi->nAktuelleSeite == $oBlaetterNavi->nSeiten}
            &raquo;
        {else}
            <a class="next" href="{$cUrl}?s{$cSite}={$oBlaetterNavi->nNaechste}&{$cParams}">&raquo;</a>
        {/if}
        </div>
    </div>
{/if}