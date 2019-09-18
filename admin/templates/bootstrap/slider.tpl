{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='slider'}
{include file='tpl_inc/seite_header.tpl' cTitel=#slider# cBeschreibung=#sliderDesc# cDokuURL=#sliderURL#}

<script src="{$currentTemplateDir}js/slider.js" type="text/javascript"></script>
<div id="content" class="container-fluid">
    {if $cAction === 'new' || $cAction === 'edit' }
        {include file='tpl_inc/slider_form.tpl'}
    {elseif $cAction === 'slides'}
        {include file='tpl_inc/slider_slide_form.tpl'}
    {else}
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#slider#}</h3>
                </div>
                {if $oSlider_arr|@count == 0}
                    <div class="panel-body">
                        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                    </div>
                {else}
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="tleft" width="50%">Name</th>
                            <th width="20%">Status</th>
                            <th width="30%">Optionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name="slider" from=$oSlider_arr item=oSlider}
                            <tr>
                                <td class="tleft">{$oSlider->cName}</td>
                                <td class="tcenter">
                                    <h4 class="label-wrap">
                                    {if $oSlider->bAktiv == 1}
                                        <span class="label label-success">aktiv</span>
                                    {else}
                                        <span class="label label-danger">inaktiv</span>
                                    {/if}
                                    </h4>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-default add" href="slider.php?action=slides&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="Slides"><i class="fa fa-image"></i></a>
                                        <a class="btn btn-default" href="slider.php?action=edit&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                        <a class="btn btn-danger" href="slider.php?action=delete&id={$oSlider->kSlider}&token={$smarty.session.jtl_token}" title="L&ouml;schen"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                {/if}
                <div class="panel-footer">
                    <a class="btn btn-primary" href="slider.php?action=new&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> Slider hinzuf&uuml;gen</a>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}