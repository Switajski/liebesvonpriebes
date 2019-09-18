{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if $sectionPersonal === true}
    {if $showAvatar === true}
        <script type="text/javascript">
        {literal}
            $(document).ready(function() {
                var useAvatar = $('#useAvatar');
                if (useAvatar.val() === 'U') {
                    useAvatar[0].form.enctype = 'multipart/form-data';
                }
                useAvatar.bind('change', function() {
                    var useGravatarDetails = $('#useGravatarDetails');
                    var useUploadDetails   = $('#useUploadDetails');
                    switch ($(this).val()) {
                        case 'G':
                            useGravatarDetails.css('display', 'table-cell');
                            useUploadDetails.hide();
                            break;
                        case 'U':
                            this.form.enctype = 'multipart/form-data';
                            useUploadDetails.css('display', 'table-cell');
                            useGravatarDetails.hide();
                            break;
                        default:
                            useGravatarDetails.hide();
                            useUploadDetails.hide();
                    }
                });
            });
        {/literal}
        </script>
    {/if}
    {if $showVita === true}
        <script type="text/javascript">
        {literal}
            $(document).ready(function() {
                $('#selectVitaLang').change(function () {
                    var iso = $('#selectVitaLang option:selected').val();
                    $('.iso_wrapper').hide();
                    $('#isoVita_' + iso).show();

                    return false;
                });
            });
        {/literal}
        </script>
    {/if}
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Pers&ouml;nliche Angaben</h3>
        </div>
        <div class="panel-body">
            {if $showAvatar === true}
                <div class="item">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="useAvatar">Benutzerbild</label>
                        </span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="useAvatar" name="extAttribs[useAvatar]">
                                <option value="N">Nein</option>
                                <option value="G"{if $attribValues.useAvatar->cAttribValue == 'G'} selected="selected"{/if}>Gravatar benutzen</option>
                                <option value="U"{if $attribValues.useAvatar->cAttribValue == 'U'} selected="selected"{/if}>Bild hochladen</option>
                            </select>
                        </span>
                        <div id="useGravatarDetails"{if !isset($attribValues.useAvatar) || $attribValues.useAvatar->cAttribValue !== 'G'} class="hidden-soft"{/if}>
                            <span class="input-group-addon">
                                <label for="useGravatarEmail">Abweichende E-Mail Adresse</label>
                            </span>
                            <span class="input-group-wrap">
                                <input id="useGravatarEmail" class="form-control" type="text" name="extAttribs[useGravatarEmail]" value="{if isset($attribValues.useGravatarEmail)}{$attribValues.useGravatarEmail->cAttribValue}{/if}" />
                            </span>
                            <span class="input-group-wrap dropdown avatar">
                                <img src="{gravatarImage email=$gravatarEmail}" title="{$oAccount->cMail}" class="img-circle" />
                            </span>
                        </div>
                        <div id="useUploadDetails"{if !isset($attribValues.useAvatar) || $attribValues.useAvatar->cAttribValue !== 'U'} class="hidden-soft"{else}{if isset($cError_arr.useAvatarUpload)} class="error"{/if}{/if}>
                            <span class="input-group-addon">
                                <label for="useAvatarUpload">Bild</label>
                            </span>
                            <div class="input-group-wrap">
                                <div class="multi_input">
                                    <input id="useAvatarUpload" class="form-control-upload" name="extAttribs[useAvatarUpload]" type="file" maxlength="2097152" accept="image/*" />
                                </div>
                            </div>
                            <span class="input-group-wrap dropdown avatar">
                                <input type="hidden" name="extAttribs[useAvatarUpload]" value="{$attribValues.useAvatarUpload->cAttribValue}" />
                                {if isset($uploadImage)}
                                    <img src="{$uploadImage}" title="{if !empty($attribValues.useAvatarUpload->cAttribValue)}{$attribValues.useAvatarUpload->cAttribValue}{else}{$oAccount->cMail}{/if}" class="img-circle" />
                                {/if}
                            </span>
                        </div>
                        {if isset($cError_arr.useAvatarUpload)}
                            <span class="input-group-addon error" title="Bitte ein Bild angeben"><i class="fa fa-exclamation-triangle"></i></span>
                        {/if}
                    </div>
                </div>
            {/if}
            {if $showGPlus === true}
                <div class="item">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="useGPlus">Link zum Google+ Profil</label>
                        </span>
                        <span class="input-group-wrap">
                            <input id="useGPlus" class="form-control" type="text" name="extAttribs[useGPlus]" value="{if isset($attribValues.useGPlus)}{$attribValues.useGPlus->cAttribValue}{/if}" />
                        </span>
                    </div>
                </div>
            {/if}
            {if $showVita === true}
                <div class="item">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="useVita">Vita</label>
                        </span>
                        <div class="input-group-wrap">
                            <select class="form-control" id="selectVitaLang">
                                {foreach name=sprachen from=$sprachen item=sprache}
                                    <option value="{$sprache->cISO}"{if $sprache->cShopStandard === 'Y'} selected="selected"{/if}>{$sprache->cNameDeutsch} {if $sprache->cShopStandard === 'Y'}(Standard){/if}</option>
                                {/foreach}
                            </select>
                            {foreach name=sprachen from=$sprachen item=sprache}
                                {assign var="cISO" value=$sprache->cISO}
                                {assign var="useVita_ISO" value="useVita_"|cat:$cISO}
                                <div id="isoVita_{$cISO}" class="iso_wrapper{if $sprache->cShopStandard != 'Y'} hidden-soft{/if}">
                                    <textarea class="form-control ckeditor" id="useVita_{$cISO}" name="extAttribs[useVita_{$cISO}]" rows="10" cols="40">{if !empty($attribValues.$useVita_ISO->cAttribText)}{$attribValues.$useVita_ISO->cAttribText}{else}{$attribValues.$useVita_ISO->cAttribValue}{/if}</textarea>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/if}