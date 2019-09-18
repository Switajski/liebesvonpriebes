{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='agbwrb'}
<style type="text/css">
    .cr {
        color: #f0f0f0;
        padding: 15px;
        position: absolute;
        text-align: center;
        width: 200px;
    }

    .cr-sticky {
        position: absolute;
    }

    .cr-top {
        top: 25px;
    }

    .cr-right {
        right: -50px;
    }

    .cr-top.cr-left,
    .cr-bottom.cr-right {
        transform: rotate(-45deg);
    }

    .cr-top.cr-right,
    .cr-bottom.cr-left {
        transform: rotate(45deg);
    }

    .cr-premium {
        background-color: #efcc86;
        color: #313131;
    }

    #plugin-header {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 15.5em;
        background-color: {$pp->getHeaderColor()};
        overflow: hidden;
        color: #fff;
    }

    #plugin-header-wrap {
        margin-bottom: 15.5em;
    }

    #plugin-header-wrap .row {
        width: 100%;
    }

    #plugin-header h1, #plugin-header h4 {
        color: #fff;
    }

    .plugin-agws_ts_features #plugin-header h1, .plugin-agws_ts_features #plugin-header h4 {
        color: #000;
    }

    h1.plugin-title {
        margin-top: 0;
    }

    #plugin-main {
        margin-top: 5em;
    }

    .form-inline {
        display: inline;
    }

    ul .fake-list-style-image {
        position: absolute;
        left: -1.5em;
        top: 3px;
    }

    ul.advantages, ul.howtos {
        padding-left: 1em;
    }

    ul.advantages li, ul.howtos li {
        padding-bottom: 0.5em;
        position: relative;
    }

    .col-card{
        margin-bottom:16px;
    }
    .col-card .col-card-content {
        padding: 16px;
        background-color: #fff;
        position: relative;
        -webkit-box-shadow: 0 2px 2px 0 rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .2), 0 1px 5px 0 rgba(0, 0, 0, .12);
        box-shadow: 0 2px 2px 0 rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .2), 0 1px 5px 0 rgba(0, 0, 0, .12);
    }

    .col-card .col-card-content:after, .col-card .col-card-content:before {
        content: " ";
        display: table;
    }

    .col-card .col-card-content:after{
        clear:both;
    }
    .col-card .col-card-content:after, .col-card .col-card-content:before{
        content:" ";
        display:table;
    }
    .col-card .col-card-content:after {
        clear:both;
    }
</style>
<div id="content">
    {if $pp === null || $pp->getPluginID() === null}
        <div class="alert alert-danger">Plugin konnte nicht gefunden werden.</div>
    {else}
        {assign var=ld value=$pp->getLongDescription()}
        {assign var=sd value=$pp->getShortDescription()}
        <div id="plugin-header-wrap" class="plugin-{$pp->getPluginID()}">
            <div class="" id="plugin-header">
                <div class="row" id="plugin-main">
                    <div class="col-md-2">
                        <img class="center-block jtl-certification-logo" height="92" alt="" src="{$pp->getCertifcationLogo()}"/>
                    </div>
                    <div class="col-md-10">
                        <h1 class="plugin-title">{$pp->getTitle()}</h1>
                        <h4 class="plugin-author">{$pp->getAuthor()}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 text-right">
                        {if $pp->getExists()}
                            {if !$pp->getIsInstalled()}
                                <form method="post" action="pluginverwaltung.php" class="form-inline">
                                    {$jtl_token}
                                    <input type="hidden" name="installieren" value="1"/>
                                    <input type="hidden" name="pluginverwaltung_uebersicht" value="1"/>
                                    <input type="hidden" name="cVerzeichnis[]" value="{$pp->getPluginID()}"/>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Jetzt dieses Plugin installieren
                                    </button>
                                </form>
                            {elseif !$pp->getIsActivated()}
                                <form method="post" action="pluginverwaltung.php" class="form-inline">
                                    {$jtl_token}
                                    <input type="hidden" name="aktivieren" value="1"/>
                                    <input type="hidden" name="pluginverwaltung_uebersicht" value="1"/>
                                    <input type="hidden" name="kPlugin[]" value="{$pp->getKPlugin()}"/>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Jetzt dieses Plugin aktivieren
                                    </button>
                                </form>
                            {else}
                                <button class="btn btn-default disabled">bereits aktiviert</button>
                            {/if}
                        {else}
                            <a class="btn btn-default" href="{$pp->getDownloadLink()}" target="_blank"><i class="fa fa-external-link"></i> Plugin herunterladen</a>
                        {/if}
                    </div>
                </div>
                <div class="cr cr-top cr-right cr-sticky cr-premium">Premium-Plugin</div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8" id="plugin-main-description">
                <h1>{$sd->title|utf8_decode}</h1>
                <p class="plugin-short-description">
                    {$sd->html|utf8_decode}
                </p>
                <h2>{$ld->title|utf8_decode}</h2>
                <p class="plugin-description">
                    {$ld->html|utf8_decode}
                </p>
                <hr>
                <div class="row" id="plugin-screenshots">
                    {foreach from=$pp->getScreenShots() item=screenShot}
                        <a href="#" data-toggle="modal" data-target="#screenshot-{$screenShot@iteration}">
                            <img class="img-responsive col-md-4" src="{$screenShot->preview}" />
                        </a>
                        <div class="modal fade" id="screenshot-{$screenShot@iteration}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title">Screenshot {$screenShot@iteration}</h4>
                                    </div>
                                    <div class="modal-body">
                                        <img class="img-responsive" src="{$screenShot->preview}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="col-md-4 col-card" id="plugin-author-meta">
                {assign var=sp value=$pp->getServicePartner()}
                {if $sp !== null}
                    <div class="col-card-content">
                        <p class="centered">
                            <img src="{$sp->cLogoPfad}" style="max-width: 100px" alt="{$sp->cFirma}" />
                        </p>
                        <hr>
                        <h4>{$sp->cFirma}</h4>
                        <p>
                            <span class="sp-street">{$sp->cStrasse}</span><br>
                            <span class="sp-plz">{$sp->cPLZ} {$sp->cOrt}</span><br>
                            <span class="sp-address-additional">{$sp->cAdresszusatz}</span>
                        </p>
                        <div class="vspacer-top"></div>
                        <p>
                            <span class="muted"><span class="sp-mail">{$sp->cMail}</span></span>
                        </p>
                        <p class="sp-www">
                            <a href="{$sp->cWWW}" class="muted" target="_blank"><i class="fa fa-external-link"></i> {$sp->cWWW}</a>
                        </p>
                        {if $sp->marketPlaceURL !== null}
                            <hr>
                            <p class="centered sp-details">
                                <a href="{$sp->marketPlaceURL}" target="_blank" class="btn btn-default"><i class="fa fa-external-link"></i> Servicepartner-Details</a><br>
                            </p>
                        {/if}
                        {if $pp->hasCertifcates()}
                            <hr>
                            <h4>Zertifikate</h4>
                            <div class="row" id="sp-certificates">
                                {assign var=isOpen value=false}
                                {foreach from=$sp->oZertifizierungen_arr item=cert}
                                    {if $cert@iteration is odd}
                                        {if $isOpen}</div>{/if}
                                        <div class="media">
                                        {assign var=isOpen value=true}
                                    {/if}
                                    <div class="col-md-6">
                                        <img src="{$cert}" alt="" style="" class="certification-icon img-responsive media-left" />
                                    </div>
                                {/foreach}
                            {/if}
                        </div>
                    </div>
                {else}
                    <span class="alert alert-info">Servicepartner konnte nicht gefunden werden.</span>
                {/if}
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6" id="plugin-info-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Ihre Vorteile</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="advantages list-unstyled">
                            {foreach from=$pp->getAdvantages() item=advantage}
                                <li class="advantage"><i class="fa fa-check fake-list-style-image"></i> {$advantage|utf8_decode}</li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6" id="plugin-info-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">So funktioniert's</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="howtos list-unstyled">
                            {foreach from=$pp->getHowTos() item=howTo}
                                <li class="howto"><i class="fa fa-check fake-list-style-image"></i> {$howTo|utf8_decode}
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <div class="panel-footer">
                        <span class="btn-group">
                            {if $pp->getExists()}
                                {if !$pp->getIsInstalled()}
                                    <form method="post" action="pluginverwaltung.php" class="form-inline">
                                        {$jtl_token}
                                        <input type="hidden" name="installieren" value="1"/>
                                        <input type="hidden" name="pluginverwaltung_uebersicht" value="1"/>
                                        <input type="hidden" name="cVerzeichnis[]" value="{$pp->getPluginID()}"/>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Plugin installieren</button>
                                    </form>
                                {elseif !$pp->getIsActivated()}
                                    <form method="post" action="pluginverwaltung.php" class="form-inline">
                                        {$jtl_token}
                                        <input type="hidden" name="aktivieren" value="1"/>
                                        <input type="hidden" name="pluginverwaltung_uebersicht" value="1"/>
                                        <input type="hidden" name="kPlugin[]" value="{$pp->getKPlugin()}"/>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Plugin aktivieren</button>
                                    </form>
                                {else}
                                    <button class="btn btn-default disabled">bereits aktiviert</button>
                                {/if}
                            {else}
                                <a class="btn btn-default" href="{$pp->getDownloadLink()}" target="_blank"><i class="fa fa-external-link"></i> Plugin herunterladen</a>
                            {/if}
                            {foreach from=$pp->getButtons() item=btn}
                                <a{if $btn->external === true} target="_blank"{/if} class="{$btn->class}" href="{$btn->link}" title="{$btn->caption}">
                                    {if !empty($btn->fa)} <i class="fa fa-{$btn->fa}"></i> {/if}{$btn->caption}
                                </a>
                            {/foreach}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            {foreach $pp->getBadges() as $badge}
                <div class="col-md-3"><img src="{$badge}" alt="" height="92"/></div>
            {/foreach}
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}