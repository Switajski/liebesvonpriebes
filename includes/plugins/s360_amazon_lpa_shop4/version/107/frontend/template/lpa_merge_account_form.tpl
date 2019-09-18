<div class="col-xs-12">
    <div class="alert alert-info">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_merge_reason}<br />
        {$oPlugin->oPluginSprachvariableAssoc_arr.lpa_merge_description}</div>
</div>

<form name="verify_account" method="post" action="{$lpa_merge_url_localized}">
    {$jtl_token}
    <div class="col-xs-12">
        <input type="hidden" name="verification_code" value="{$lpa_login_verification_code}" />
        <input type="hidden" name="amazon_id" value="{$lpa_login_amazon_id}" />
        <input type="password" class="form-control" name="password" />
    </div>

        
    <div class="col-xs-6 padded-lg-bottom padded-lg-top">
        {if $lpa_login_for_checkout} 
            <a href="bestellvorgang.php" rel="nofollow" class="btn btn-default" onclick="lpa_logout();">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_merge_otherpaymethod}</a>
        {else}
            <a href="{$URL_SHOP}" class="btn btn-default" onclick="lpa_logout();" rel="nofollow">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_general_back}</a>
        {/if}
    </div>
    
    <div class="col-xs-6 padded-lg-bottom padded-lg-top">
        <input type="submit" class="btn btn-primary submit submit_once pull-right" value="{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_general_next}" />
    </div>

    
</form>
<div class="col-xs-12">
    <a href="pass.php?" rel="nofollow" target="_blank">{lang key="forgotPassword" section="global"}</a>
</div>