{config_load file="$lang.conf" section="categorycheck"}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#categorycheck# cBeschreibung=#categorycheckDesc# cDokuURL=#categorycheckURL#}

<div id="content" class="container-fluid">
    <div class="systemcheck">
        {if !$passed}
            <div class="alert alert-warning">
                Es wurden Kategorien ohne korrekte Elternkategorie gefunden.
            </div>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th class="col-xs-3 text-center">ID</th>
                    <th class="col-xs-9 text-center">Name</th>
                </tr>
                </thead>
                <tbody>
                {foreach $cateogries as $category}
                    <tr>
                        <td class="text-center">{$category->kKategorie}</td>
                        <td class="text-center">{$category->cName}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {else}
            <div class="alert alert-info">Keine verwaisten Kategorien gefunden.</div>
        {/if}
    </div>
</div>

{include file='tpl_inc/footer.tpl'}