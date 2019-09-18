{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="login"}
{config_load file="$lang.conf" section="shopupdate"}

<script type="text/javascript">
{literal}
$(document).ready(function(){
	$("input.field:first").focus();
});
{/literal}
</script>

<div id="login_frame">
	<div id="login_wrapper">
		<div id="login_logo"></div>
	   
		{if isset($cFehler) && $cFehler}
			<p class="box_error">{$cFehler}</p>
			
			<script type="text/javascript">
			{literal}
			$(document).ready(function(){
				$("#login_wrapper").effect("shake", { times: 2 }, 50);
			});
			{/literal}
			</script>
		{/if}
	   
		<div id="login_outer">			
			<form method="post" action="index.php">
				<input id="benutzer" type="hidden" name="adminlogin" value="1" />
			{if isset($uri) && $uri|count_characters > 0}
				<input type="hidden" name="uri" value="{$uri}" />
			{/if}
				{if isset($code_adminlogin) && $code_adminlogin}<input type="hidden" name="md5" value="{$code_adminlogin->codemd5}" id="captcha_md5">{/if}
				<p> 
					<label>{#username#}<br />
					<input type="text" name="benutzer" id="user_login" value="" size="20" tabindex="10" /></label>
				</p> 
				<p> 
					<label>{#password#}<br />
					<input type="password" name="passwort" id="user_pass" value="" size="20" tabindex="20" /></label>
				</p>
				{if isset($code_adminlogin) && $code_adminlogin}
					<p>
						<label>{#code#}
							<div class="captcha">
								<img src="{$code_adminlogin->codeURL}" alt="{#code#}" id="captcha" />
							</div>
							<a href="index.php" class="captcha">{#reloadCaptcha#}</a>
						</label>
					</p>
					<p>
						<label>{#enterCode#}<br />
						<input type="text" name="captcha" tabindex="30" id="captcha_text"></label>
					</p>
				{/if}
				<p class="tcenter"> 
					<input type="submit" value="Anmelden" tabindex="100" class="button orange" /> 
				</p> 
			</form>  
		</div>
	</div>
</div>
{include file='tpl_inc/footer.tpl'}
