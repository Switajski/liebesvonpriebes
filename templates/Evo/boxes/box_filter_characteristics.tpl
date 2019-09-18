{if $BoxenEinstellungen.navigationsfilter.merkmalfilter_verwenden === 'box'}
    {if isset($Suchergebnisse) && $Suchergebnisse->MerkmalFilter|@count > 0}
        {foreach name=merkmalfilter from=$Suchergebnisse->MerkmalFilter item=Merkmal}
            {assign var=kMerkmal value=$Merkmal->kMerkmal}
            <section class="panel panel-default box box-filter-characteristics">
                {if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
                    <div class="panel-heading dropdown">
                        <h5 class="panel-title">
                            {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && !empty($Merkmal->cBildpfadKlein) && $Merkmal->cBildpfadKlein !== $BILD_KEIN_MERKMALBILD_VORHANDEN}
                                <img src="{$Merkmal->cBildpfadKlein}" alt="" class="vmiddle" />
                            {/if}
                            {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                                &nbsp;{$Merkmal->cName}
                            {/if}
                        </h5>
                    </div>
                    <div class="panel-body dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                             {lang key="selectFilter" section="global"}&nbsp; <span class="fa fa-caret-down"></span>
                        </a>
                        {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal class="dropdown-menu"}
                    </div>
                {else}
                    <div class="panel-heading">
                        <h5 class="panel-title">
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'T' && !empty($Merkmal->cBildpfadKlein) && $Merkmal->cBildpfadKlein !== $BILD_KEIN_MERKMALBILD_VORHANDEN}
                            <img src="{$Merkmal->cBildpfadKlein}" alt="" class="vmiddle" />
                        {/if}
                        {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als !== 'B'}
                            &nbsp;{$Merkmal->cName}
                        {/if}
                        </h5>
                    </div>
                    <div class="box-body">
                        {include file='snippets/filter/characteristic.tpl' Merkmal=$Merkmal}
                    </div>
                {/if}
            </section>
        {/foreach}
    {/if}
{/if}