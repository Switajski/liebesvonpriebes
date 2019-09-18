{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="passwortaendern"}
{include file='tpl_inc/seite_header.tpl' cTitel=#resetPassword# cBeschreibung=#passwordResetDesc# cDokuURL=#resetPasswordURL#}
<div id="content" class="container-fluid">
    <div id="settings">
        <form name="login" method="post" action="passwort_aendern.php">
            {$jtl_token}
            <input type="hidden" name="zuruecksetzen" value="1" />
            <div class="item">
                <div class="name">
                    <label for="benutzer" class="left">{#username#}</label>
                </div>
                <div class="for">
                    <input class="form-control" type="text" name="benutzer" id="benutzer" tabindex="1" />
                </div>
            </div>
            <div class="item">
                <div class="name">
                    <label for="password" class="left">{#oldPassword#}</label>
                </div>
                <div class="for">
                    <input class="form-control" type="password" name="password" id="password" tabindex="2" />
                </div>
            </div>
            <div class="item">
                <div class="name">
                    <label for="neuespasswort" class="left">{#newPassword#}</label>
                </div>
                <div class="for">
                    <input class="form-control" type="password" name="neuespasswort" id="neuespasswort" tabindex="3" />
                </div>
            </div>
            <div class="item">
                <div class="name">
                    <label for="neuespasswort2" class="left">{#retypePassword#}</label>
                </div>
                <div class="for">
                    <input class="form-control" type="password" name="neuespasswort2" id="neuespasswort2" tabindex="4" />
                </div>
            </div>
            <div class="save_wrapper">
                <button type="submit" value="{#resetPassword#}" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> {#resetPassword#}</button>
            </div>
        </form>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}