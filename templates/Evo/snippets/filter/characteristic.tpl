{assign var="is_dropdown" value=false}
{if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
    {assign var="is_dropdown" value=true}
{/if}

<ul {if $is_dropdown}class="dropdown-menu" role="menu" {elseif isset($class)}class="{$class}" {else}class="nav nav-list"{/if}>
    {foreach name=filter from=$Merkmal->oMerkmalWerte_arr item=MerkmalWert}
        {assign var=kMerkmalWert value=$MerkmalWert->kMerkmalWert}
        {if $MerkmalWert->nAktiv}
            <li class="active">
                <a rel="nofollow" href="{$NaviFilter->URL->cAlleMerkmalWerte[$MerkmalWert->kMerkmalWert]}"{if $Merkmal->cTyp === 'BILD'} title="{$MerkmalWert->cWert}"{/if}>
                    <span class="badge pull-right">{$MerkmalWert->nAnzahl}</span>
                        <span class="value">
                        <i class="fa fa-check-square-o text-muted"></i>
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' &&
                        !empty($MerkmalWert->cBildpfadKlein) &&
                        $MerkmalWert->cBildpfadKlein !== $BILD_KEIN_MERKMALWERTBILD_VORHANDEN}
                            <img src="{$MerkmalWert->cBildpfadKlein}" alt="{$MerkmalWert->cWert|escape:'html'}" class="vmiddle" />
                        {/if}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                            <span class="word-break">{$MerkmalWert->cWert|escape:'html'}</span>
                        {/if}
                    </span>
                </a>
            </li>
        {else}
            <li>
                <a rel="nofollow" href="{$MerkmalWert->cURL}"{if $Merkmal->cTyp === 'BILD'} title="{$MerkmalWert->cWert|escape:'html'}"{/if}>
                    <span class="badge pull-right">{$MerkmalWert->nAnzahl}</span>
                    <span class="value">
                        <i class="fa fa-square-o text-muted"></i>
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' &&
                        !empty($MerkmalWert->cBildpfadKlein) &&
                        $MerkmalWert->cBildpfadKlein !== $BILD_KEIN_MERKMALWERTBILD_VORHANDEN}
                            <img src="{$MerkmalWert->cBildpfadKlein}" alt="{$MerkmalWert->cWert|escape:'html'}" class="vmiddle" />
                        {/if}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                            <span class="word-break">{$MerkmalWert->cWert|escape:'html'}</span>
                        {/if}
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
</ul>
