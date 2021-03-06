{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if $Einstellungen.kunden.kundenregistrierung_vcardupload === 'Y'}
    <div id="vcard-upload" class="clearfix">
        <div class="dropdown pull-right">
            <a href="#" class="dropdown-toggle btn btn-default btn-xs" title="{lang key="uploadVCard" section="account data"}" data-toggle="dropdown"><i class="fa fa-file-text-o"></i><span class="hidden-xs">&nbsp;{lang key="uploadVCard" section="account data"}&nbsp;</span><span class="caret"></span></a>
            <div class="dropdown-menu keepopen">
                <div class="panel">
                    <div class="panel-body">
                        <form class="form" enctype="multipart/form-data" method="post" action="{get_static_route id=$id}{if isset($checkout)}?checkout={$checkout}{/if}">
                            {$jtl_token}
                            <fieldset>
                                <div class="form-group"><input class="file-upload file-loading" required type="file" accept="text/vcard" name="vcard"></div>
                                <div class="form-group"><button class="btn btn-default btn-block" type="submit"><i class="fa fa-file-text-o"></i>&nbsp;{lang key="uploadVCard" section="account data"}</button></div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}