{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="skrill"}
{config_load file="$lang.conf" section="einstellungen"}

{assign var=preferences value=#preferences#}
{include file='tpl_inc/seite_header.tpl' cTitel="Skrill "|cat:$preferences}
<div id="content" class="container-fluid">
    {if $actionError != null}
        <div class="alert alert-danger">
            {if $actionError == 1}{#mbEmailValidationError#}
            {elseif $actionError == 2}{#mbSecretWordVeloctiyCheckExceeded#}
            {elseif $actionError == 3}{#mbSecretWordValidationError#}
            {elseif $actionError == 99}{#nofopenError#}
            {/if}
        </div>
    {/if}

    {if $showEmailInput}
        <div class="panel panel-default">
            <div class="panel-body">
                <p>{#mbIntro#}</p>
                <p class="center" style="text-align: center">
                    <img src="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}/gfx/skrill_intro.jpg" alt="Skrill" />
                </p>
            </div>
            <div class="panel-footer">
                {if $actionError != 99}
                    <form method="post" action="">
                        {$jtl_token}
                        <div class="input-group" style="margin-bottom: 0;">
                                <label class="input-group-addon" for="email">{#mbEmailAddress#}:</label>
                            <input type="text" name="email" class="form-control" id="email" value="{if isset($smarty.post.email)}{$smarty.post.email}{/if}" />
                            <span class="input-group-btn">
                                <input class="btn btn-primary" type="submit" name="actionValidateEmail" value="{#mbValidateEmail#}" />
                            </span>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
    {else}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#mbHeaderEmail#}</h3>
            </div>
            <div class="panel-body">
                <p>{#mbEmailValidationSuccess#|sprintf:$email:$customerId}</p>
            </div>
            <div class="panel-footer">
                <form method="post" action="">
                    {$jtl_token}
                    <button class="btn btn-danger" type="submit" name="actionDelete" value="{#mbDelete#}"><i class="fa fa-trash"></i> {#mbDelete#}</button>
                </form>
            </div>
        </div>
        {*<div class="panel panel-default">*}
            {*<div class="panel-heading">*}
                {*<h3 class="panel-title">{#mbHeaderActivation#}</h3>*}
            {*</div>*}
            {*{if $showActivationButton}*}
                {*<div class="panel-body">*}
                    {*<p>{#mbActivationText#} {#mbActivationDescription#}</p>*}
                {*</div>*}
                {*<div class="panel-footer">*}
                    {*<form method="post" action="">*}
                        {*{$jtl_token}*}
                        {*<input class="btn btn-primary" type="submit" name="actionActivate" value="{#mbActivate#}" />*}
                    {*</form>*}
                {*</div>*}
            {*{else}*}
                {*<div class="panel-body">*}
                    {*<p>{#mbActivationRequestText#|sprintf:$activationRequest} {#mbActivationDescription#}</p>*}
                {*</div>*}
            {*{/if}*}
        {*</div>*}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#mbSecretWord#}</h3>
            </div>
            {if $showSecretWordValidation}
                <div class="panel-body">
                    <form method="post" action="">
                        {$jtl_token}
                        <span class="input-group">
                            <span class="input-group-addon">
                                <label for="secretWord">{#mbSecretWord#}:</label>
                            </span>
                            <input class="form-control" type="text" name="secretWord" id="secretWord" value="{if isset($smarty.post.secretWord)}{$smarty.post.secretWord}{/if}" />
                            <span class="input-group-btn">
                                <input class="btn btn-primary" type="submit" name="actionValidateSecretWord" value="{#mbValidateSecretWord#}" />
                            </span>
                        </span>
                    </form>
                </div>
            {else}
                <div class="panel-body">
                    <p>{#mbSecretWordValidationSuccess#}</p>
                </div>
                <div class="panel-footer">
                    <form method="post" action="">
                        {$jtl_token}
                        <button class="btn btn-danger" type="submit" name="actionDeleteSecretWord" value="{#mbDelete#}"><i class="fa fa-trash"></i> {#mbDelete#}</button>
                    </form>
                </div>
            {/if}
        </div>
    {/if}

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">{#mbHeaderSupport#}</h3>
        </div>
        <div class="panel-body">
            {#mbSupportText#}
        </div>
    </div>

</div>

{include file='tpl_inc/footer.tpl'}