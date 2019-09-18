{if isset($Sektion->cName)}
    {assign var="cTitel" value=#preferences#|cat:": "|cat:$Sektion->cName}
{else}
    {assign var="cTitel" value=#preferences#}
{/if}
{if isset($cSearch) && $cSearch|strlen  > 0}
    {assign var="cTitel" value=$cSearch}
{/if}

{include file='tpl_inc/seite_header.tpl' cTitel=#preferences# cBeschreibung=#preferencesDesc# cDokuURL=#preferencesURL#}
<div id="content" class="container-fluid">
    <div class="table-responsive">
        <table class="list table">
            <tbody>
            {foreach name=einst from=$Sektionen item=Sektion}
                <tr>
                    <td>{$Sektion->cName}</td>
                    <td>{$Sektion->anz} {#preferences#}</td>
                    <td>
                        <a href="einstellungen.php?kSektion={$Sektion->kEinstellungenSektion}" class="btn btn-primary">{#configure#}</a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>