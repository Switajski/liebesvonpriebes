{block name="header"}
    {include file='layout/header.tpl'}
{/block}

{block name="content"}
    <div id="maintenance-notice" class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-wrench"></i> {lang key="maintainance" section="global"}</h3>
        </div>
        <div class="panel-body">
            {* include file="snippets/extension.tpl" *}
            {lang key="maintenanceModeActive" section="global"}
        </div>
    </div>
{/block}

{block name="footer"}
    {include file='layout/footer.tpl'}
{/block}