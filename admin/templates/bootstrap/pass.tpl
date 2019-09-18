{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="login"}
{config_load file="$lang.conf" section="shopupdate"}

<script type="text/javascript">
    {literal}
    $(document).ready(function () {
        $("input.field:first").focus();
    });
    {/literal}
</script>

<div class="vertical-center">
    <div class="container">
        <div id="login_wrapper">
            <div id="login_logo">
                <img src="{$currentTemplateDir}gfx/shop-login.png" alt="JTL-Shop" />
            </div>
            {if isset($cFehler) && $cFehler !== ''}
                <div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i> {$cFehler}</div>
            {/if}
            {if isset($cHinweis) && $cHinweis !== ''}
                <div class="alert alert-info" role="alert"><i class="fa fa-info-circle"></i> {$cHinweis}</div>
            {/if}
            <div id="login_outer" class="panel panel-default">
                <div class="panel-body">
                    <form method="post" action="pass.php" class="form-horizontal" role="form">
                        {$jtl_token}
                        {if $step === 'prepare'}
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="text" tabindex="10" size="20" value="" id="admin_user_mail" name="mail" placeholder="E-Mail-Adresse" class="form-control" />
                            </div>
                        {elseif $step === 'confirm'}
                            <input type="hidden" name="fpwh" value="{$fpwh}" />
                            <input type="hidden" name="fpm" value="{$fpm}" />
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                                <input type="password" tabindex="10" size="20" value="" id="user_pw" name="pw_new" placeholder="Neues Passwort" class="form-control" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-unlock"></i></span>
                                <input type="password" tabindex="10" size="20" value="" id="user_pw_confirm" name="pw_new_confirm" placeholder="Neues Passwort best&auml;tigen" class="form-control" />
                            </div>
                        {/if}
                        <p class="tcenter">
                            <button type="submit" value="Passwort zur&uuml;cksetzen" tabindex="100" class="btn btn-primary btn-block btn-lg">Passwort zur&uuml;cksetzen</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}