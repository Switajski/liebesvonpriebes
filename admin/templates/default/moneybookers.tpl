{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	admin page for JTL-Shop 3

	http://www.jtl-software.de

	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="moneybookers"}
{config_load file="$lang.conf" section="einstellungen"}

{assign var=preferences value=#preferences#}
{include file="tpl_inc/seite_header.tpl" cTitel="Moneybookers `$preferences`"}
<div id="content">
	 {if $actionError != null}
		 <div class="box_error">
			 {if $actionError == 1}{#mbEmailValidationError#}
			 {elseif $actionError == 2}{#mbSecretWordVeloctiyCheckExceeded#}
			 {elseif $actionError == 3}{#mbSecretWordValidationError#}
             {elseif $actionError == 99}{#nofopenError#}
			 {/if}
		 </div>
	 {/if}
	 
	 {if $showEmailInput}
		 <div style="margin-bottom:20px;">
			 <p>{#mbIntro#}</p>
         {if $actionError != 99}
			 <form method="post" action="">
				 {#mbEmailAddress#}: <input type="text" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email}{/if}" />
				 <input type="submit" name="actionValidateEmail" value="{#mbValidateEmail#}"/>
			 </form>
         {/if}
	 {else}
		 <h2>{#mbHeaderEmail#}</h2>
		 <div style="margin-bottom:20px;">
		 <p>{#mbEmailValidationSuccess#|sprintf:$email:$customerId}</p>
		 <form method="post" action="">
			 <input type="submit" name="actionDelete" value="{#mbDelete#}"/>
		 </form>
		 </div>
		 <h2>{#mbHeaderActivation#}</h2>
		 <div style="margin-bottom:20px;">
			 {if $showActivationButton}
				 <p>{#mbActivationText#} {#mbActivationDescription#}</p>
				 <form method="post" action="">
					 <input type="submit" name="actionActivate" value="{#mbActivate#}"/>
				 </form>				
			 {else}
				 <p>{#mbActivationRequestText#|sprintf:$activationRequest} {#mbActivationDescription#}</p>
			 {/if}
		 </div>
		 <div style="margin-bottom:20px;">			
			 {if $showSecretWordValidation}
				 <form method="post" action="">
					 {#mbSecretWord#}: <input type="text" name="secretWord" value="{if isset($smarty.post.secretWord)}{$smarty.post.secretWord}{/if}" />
					 <input type="submit" name="actionValidateSecretWord" value="{#mbValidateSecretWord#}"/>
				 </form>
			 {else}
				 <p>{#mbSecretWordValidationSuccess#|sprintf:$secretWord}</p>
				 <form method="post" action="">
					 <input type="submit" name="actionDeleteSecretWord" value="{#mbDelete#}"/>
				 </form>
			 {/if}
		 </div>
	 {/if}
	 </div>
	 
	 <h2>{#mbHeaderSupport#}</h2>		
	 <div style="margin-bottom:20px;">
		 {#mbSupportText#}
	 </div>
</div>				

{include file='tpl_inc/footer.tpl'}