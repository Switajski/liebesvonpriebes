{if isset($oThemebar_arr) && $oTheme_arr}
    <div id="switcher">
        <div class="switcher hidden-xs" id="themeswitcher-config">
            <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown">
                <i class="fa fa-bars"></i>
            </a>
            <div class="switcher-wrapper">
                <div class="switcher-header">
                    <h2>{$oPlugin->oPluginSprachvariableAssoc_arr.jtl_themebar_template}</h2>
                </div>
                <div class="switcher-content">
                    <ul class="nav">
                        {foreach $oThemebar_arr as $oTheme}
                            {if $oTheme->cName != "Benutzerdefiniert"}
                                <li class="styleswitch {$oTheme->cValue}" rel="{$oTheme->cValue}">
                                    <a href="#{$oTheme->cValue}">{$oTheme->cName} <small class="text-muted">{$oTheme->cDesc}</small></a>
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
    </div>
{/if}