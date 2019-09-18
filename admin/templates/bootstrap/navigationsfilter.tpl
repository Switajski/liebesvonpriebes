{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="navigationsfilter"}
{include file='tpl_inc/seite_header.tpl' cTitel=#navigationsfilter# cBeschreibung=#navigationsfilterDesc#
         cDokuURL=#navigationsfilterUrl#}

<script>
    var bManuell = false;

    $(function()
    {
        $('#einstellen').submit(validateFormData);
        $('#btn-add-range').click(function() { addPriceRange(); });
        $('.btn-remove-range').click(removePriceRange);

        selectCheck(document.getElementById('preisspannenfilter_anzeige_berechnung'));

        {foreach $oPreisspannenfilter_arr as $i => $oPreisspanne}
            addPriceRange({$oPreisspanne->nVon}, {$oPreisspanne->nBis});
        {/foreach}
    });

    function addPriceRange(nVon, nBis)
    {
        var n = Math.floor(Math.random() * 1000000);

        nVon = nVon || 0;
        nBis = nBis || 0;

        $('#price-rows').append(
            '<div class="price-row">' +
                '<button type="button" class="btn-remove-range btn btn-danger btn-sm">' +
                    '<i class="fa fa-trash"></i></button> ' +
                '<label for="nVon_' + n + '">{#navigationsfilterFrom#}:</label> ' +
                '<input id="nVon_' + n + '" class="form-control" name="nVon[]" type="text" value="' + nVon + '"> ' +
                '<label for="nBis_' + n + '">{#navigationsfilterTo#}:</label> ' +
                '<input id="nBis_' + n + '" class="form-control" name="nBis[]" type="text" value="' + nBis + '">' +
            '</div>'
        );

        $('.btn-remove-range').off('click').click(removePriceRange);
    }

    function removePriceRange()
    {
        $(this).parent().remove();
    }

    function selectCheck(selectBox)
    {
        if (selectBox.selectedIndex === 1) {
            $('#Werte').show();
            bManuell = true;
        } else if (selectBox.selectedIndex === 0) {
            $('#Werte').hide();
            bManuell = false;
        }
    }

    function validateFormData(e)
    {
        if (bManuell === true) {
            var cFehler = '',
                $priceRows = $('.price-row'),
                lastUpperBound = 0,
                $errorAlert = $('#ranges-error-alert');

            $errorAlert.hide();

            $priceRows
                .sort(function(a, b) {
                    var aVon = parseFloat($(a).find('[id^=nVon_]').val());
                    var bVon = parseFloat($(b).find('[id^=nVon_]').val());
                    return aVon < bVon ? -1 : +1;
                })
                .each(function(i, row) {
                    $('#price-rows').append(row);
                });

            $priceRows.each(function(i, row) {
                var $row  = $(row),
                    $nVon = $row.find('[id^=nVon_]'),
                    $nBis = $row.find('[id^=nBis_]'),
                    nVon  = $nVon.val(),
                    nBis  = $nBis.val(),
                    fVon  = parseFloat(nVon),
                    fBis  = parseFloat(nBis);

                $row.removeClass('has-error');

                if(nVon === '' || nBis === '') {
                    cFehler += 'Ein oder mehrere Felder sind nicht gesetzt.<br>';
                    $row.addClass('has-error');
                } else if(fVon >= fBis) {
                    cFehler += 'Die Preisspanne ' + fVon + ' bis ' + fBis + ' ist ung&uuml;tig.<br>';
                    $row.addClass('has-error');
                } else if(fVon < lastUpperBound) {
                    cFehler += 'Die Preisspanne ' + fVon + ' bis ' + fBis + ' &uuml;berschneidet sich mit anderen.<br>';
                    $row.addClass('has-error');
                }

                lastUpperBound = fBis;
            });

            if(cFehler !== '') {
                $errorAlert.html(cFehler).show();
                e.preventDefault();
            }
        }
    }
</script>

<div id="content" class="container-fluid">
    <form name="einstellen" method="post" id="einstellen">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1"/>
        <div id="settings">
            {assign var=open value=false}
            {foreach name=conf from=$oConfig_arr item=oConfig}
                {if $oConfig->cConf === 'Y'}
                    <div class="item input-group">
                        <span class="input-group-addon">
                            <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                        </span>
                        {if $oConfig->cInputTyp === 'selectbox'}
                            <span class="input-group-wrap">
                                <select id="{$oConfig->cWertName}" name="{$oConfig->cWertName}"
                                        class="form-control combo"
                                        {if $oConfig->cWertName === 'preisspannenfilter_anzeige_berechnung'}
                                            onChange="selectCheck(this);"
                                        {/if}>
                                    {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                        <option value="{$wert->cWert}"
                                                {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>
                                            {$wert->cName}
                                        </option>
                                    {/foreach}
                                </select>
                            </span>
                        {elseif $oConfig->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$oConfig->cWertName}"
                                   id="{$oConfig->cWertName}"
                                   value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}"
                                   tabindex="1">
                        {else}
                            <input class="form-control" type="text" name="{$oConfig->cWertName}"
                                   id="{$oConfig->cWertName}"
                                   value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}"
                                   tabindex="1">
                        {/if}
                        <span class="input-group-addon">
                            {if $oConfig->cBeschreibung}
                                {getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}
                            {/if}
                        </span>
                        {if $oConfig->cWertName === 'preisspannenfilter_anzeige_berechnung'}
                    </div>
                    <div id="Werte" style="display: {if $oConfig->gesetzterWert === 'M'}block{else}none{/if};"
                         class="form-inline">
                        <div id="ranges-error-alert" class="alert alert-danger" style="display: none;"></div>
                        <div id="price-rows"></div>
                        <button type="button" class="btn btn-info btn-sm" id="btn-add-range">
                            <i class="fa fa-plus"></i>
                        </button>
                    </div>
                    <div class="item input-group">
                        {/if}
                    </div>
                {else}
                    {if $oConfig->cName}
                        {if $open}
                    </div>
                </div>
                        {/if}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            {$oConfig->cName}
                            <span class="pull-right">{getHelpDesc cID=$oConfig->kEinstellungenConf}</span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        {assign var=open value=true}
                    {/if}
                {/if}
            {/foreach}
            {if $open}
                    </div>
                </div>
            {/if}
        </div>
        <p class="submit">
            <button name="speichern" class="btn btn-primary" type="submit" value="{#navigationsfilterSave#}">
                <i class="fa fa-save"></i> {#navigationsfilterSave#}
            </button>
        </p>
    </form>
</div>

{include file='tpl_inc/footer.tpl'}