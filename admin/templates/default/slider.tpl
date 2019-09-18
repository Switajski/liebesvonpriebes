{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{include file='tpl_inc/header.tpl'} 
{config_load file="$lang.conf" section="slider"} {include file="tpl_inc/seite_header.tpl" cTitel=#slider# cBeschreibung=#sliderDesc# cDokuURL=#sliderURL#}
<script src="templates/default/js/slider.js" type="text/javascript"></script>
<div id="content">

    {if $cHinweis}
        <p class="box_success">{$cHinweis}</p>
    {/if} 

    {if $cFehler}
        <p class="box_error">{$cFehler}</p>
    {/if} 

    {if $cAction == 'new' || $cAction == 'edit' }
        {include file='tpl_inc/slider_form.tpl'} 
    {elseif $cAction == 'slides'}
        {include file='tpl_inc/slider_slide_form.tpl'} 		
    {else}
        <div id="settings">
            <table class="list">
                <thead>
                    <tr>
                        <th class="tleft" width="55%">Name</th>
                        <th width="20%">Status</th>
                        <th width="25%">Optionen</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach name="slider" from=$oSlider_arr item=oSlider}
                        <tr>
                            <td class="tleft">{$oSlider->cName}</td>
                            <td class="tcenter">
                                {if $oSlider->bAktiv == 1} 
                                    <span class="success">aktiv</span>
                                {else}
                                    <span class="error">inaktiv</span>
                                {/if}
                            </td>
                            <td>
                                <a class="button add" href="slider.php?action=slides&id={$oSlider->kSlider}">Slides</a> 
                                <a class="button edit" href="slider.php?action=edit&id={$oSlider->kSlider}">Bearbeiten</a> 
                                <a class="button remove" href="slider.php?action=delete&id={$oSlider->kSlider}">Entfernen</a>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            {if $oSlider_arr|@count == 0}
                <p class="box_info">{#noDataAvailable#}</p>
            {/if}

            <div class="save_wrapper">
                <a class="button orange" href="slider.php?action=new">Slider hinzuf&uuml;gen</a>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}
