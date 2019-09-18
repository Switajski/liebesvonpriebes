<script type="text/javascript">
    function confirmDelete() {ldelim}
        return confirm('M\u00f6chten Sie den Link wirklich l\u00f6schen?\nBitte beachten Sie, dass auch untergeordnete Links gel\u00f6scht werden!');
    {rdelim}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#links# cBeschreibung=#linksDesc# cDokuURL=#linksUrl#}
<div id="content" class="container-fluid">
    <div class="block container2">
        <form action="links.php" method="post">
            {$jtl_token}
            <button class="btn btn-primary add" name="neuelinkgruppe" value="1"><i class="fa fa-share"></i> {#newLinkGroup#}</button>
        </form>
    </div>
    <div class="panel-group accordion" id="accordion2" role="tablist" aria-multiselectable="true">
        {foreach name=linkgruppen from=$linkgruppen item=linkgruppe}
            {assign var=lgName value='linkgroup-'|cat:$linkgruppe->kLinkgruppe}
            <div class="panel panel-default">
                <div class="panel-heading accordion-heading">
                    <h3 class="panel-title" id="heading-{$lgName}">
                        <span class="pull-left">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse{$lgName}">
                                <span class="accordion-toggle-icon"><i class="fa fa-plus"></i></span> {$linkgruppe->cName} ({#linkGroupTemplatename#}: {$linkgruppe->cTemplatename})
                            </a>
                        </span>
                    </h3>
                    <form method="post" action="links.php">
                        {$jtl_token}
                        <span class="btn-group pull-right">
                            <button name="kLinkgruppe" value="{$linkgruppe->kLinkgruppe}" class="btn btn-primary" title="{#modify#}"><i class="fa fa-edit"></i></button>
                            <button name="addlink" value="{$linkgruppe->kLinkgruppe}" class="btn btn-default add" title="{#addLink#}">{#addLink#}</button>
                            <button name="delconfirmlinkgruppe" value="{$linkgruppe->kLinkgruppe}" class="btn btn-danger" title="{#linkGroup#} {#delete#}"><i class="fa fa-trash"></i></button>
                        </span>
                    </form>
                </div>
                <div id="collapse{$lgName}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{$lgName}">
                    {if $linkgruppe->links|count > 0}
                        <table class="table">
                            {include file="tpl_inc/links_uebersicht_item.tpl" list=$linkgruppe->links id=$linkgruppe->kLinkgruppe}
                        </table>
                    {else}
                        <p class="alert alert-info" style="margin:10px;"><i class="fa fa-info-circle"></i> {#noData#}</p>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>{* /accordion *}
    <div class="block container2">
        <form action="links.php" method="post">
            {$jtl_token}
            <button class="btn btn-primary add" name="neuelinkgruppe" value="1"><i class="fa fa-share"></i> {#newLinkGroup#}</button>
        </form>
    </div>
</div>