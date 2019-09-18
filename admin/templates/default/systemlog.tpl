{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="systemlog"}

{include file="tpl_inc/seite_header.tpl" cTitel=#systemlog# cBeschreibung=#systemlogDesc# cDokuURL=#systemlogURL#}
<div id="content">
{if $cFehler|count_characters > 0}
	 <div class="box_error">{$cFehler}</div>
{/if}

{if $cHinweis|count_characters > 0}
	 <div class="box_success">{$cHinweis}</div>
{/if}

	 <div class="container">
		  <div class="tabber">				
				<div class="tabbertab{if isset($cTab) && $cTab == 'log'} tabbertabdefault{/if}">
					 <h2>{#systemlogLog#}</h2>
						  {if $oBlaetterNavi->nAktiv == 1}
								<div class="block clearall">
									 <div class="left">
												<div class="pages tright">
													 <span class="pageinfo">{#systemlogEntry#}: <strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} {#from#} {$oBlaetterNavi->nAnzahl}</span>
													 <a class="back" href="systemlog.php?s1={$oBlaetterNavi->nVoherige}&tab=log{if $cSuche|count_characters > 0}&cSucheEncode={$cSuche}{/if}{if $cSuche|count_characters > 0}&nLevel={$nLevel}{/if}">&laquo;</a>
													 {if $oBlaetterNavi->nAnfang != 0}<a href="systemlog.php?s1={$oBlaetterNavi->nAnfang}&tab=log{if $cSuche|count_characters > 0}&cSucheEncode={$cSuche}{/if}{if $cSuche|count_characters > 0}&nLevel={$nLevel}{/if}">{$oBlaetterNavi->nAnfang}</a> ... {/if}
														  {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt}
																<a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="systemlog.php?s1={$Blatt}&tab=log{if $cSuche|count_characters > 0}&cSucheEncode={$cSuche}{/if}{if $cSuche|count_characters > 0}&nLevel={$nLevel}{/if}">{$Blatt}</a>
														  {/foreach}
													 
													 {if $oBlaetterNavi->nEnde != 0}
														  ... <a class="page" href="systemlog.php?s1={$oBlaetterNavi->nEnde}&tab=log{if $cSuche|count_characters > 0}&cSucheEncode={$cSuche}{/if}{if $cSuche|count_characters > 0}&nLevel={$nLevel}{/if}">{$oBlaetterNavi->nEnde}</a>
													 {/if}
													 <a class="next" href="systemlog.php?s1={$oBlaetterNavi->nNaechste}&tab=log{if $cSuche|count_characters > 0}&cSucheEncode={$cSuche}{/if}{if $cSuche|count_characters > 0}&nLevel={$nLevel}{/if}">&raquo;</a>
												</div>
									 </div>
									 <div class="right">
										  {*<a href="systemlog.php?tab=log&a=del" class="button remove">Log l&ouml;schen</a>*}
									 </div>
								</div>
						  {else}
								<div class="container">
									 {*<a href="systemlog.php?tab=log&a=del" class="button remove">Log l&ouml;schen</a>*}
								</div>
						  {/if}

                          <div class="container">
                              <form method="POST" action="systemlog.php">
                              <input type="hidden" name="{$session_name}" value="{$session_id}">
                              <input type="hidden" name="suche" value="1">
                              <input type="hidden" name="tab" value="log">
                                  <strong>{#systemlogSearch#}:</strong>                                      
                                  <input name="cSuche" type="text" value="{$cSuche}" />
                                  <strong>{#systemlogLevel#}:</strong>
                                  <select name="nLevel">
                                      <option value="0">&Uuml;berall</option>
                                      <option value="{$JTLLOG_LEVEL_ERROR}"{if $nLevel == $JTLLOG_LEVEL_ERROR} selected{/if}>{#systemlogError#}</option>
                                      <option value="{$JTLLOG_LEVEL_NOTICE}"{if $nLevel == $JTLLOG_LEVEL_NOTICE} selected{/if}>{#systemlogNotice#}</option>
                                      <option value="{$JTLLOG_LEVEL_DEBUG}"{if $nLevel == $JTLLOG_LEVEL_DEBUG} selected{/if}>{#systemlogDebug#}</option>
                                  </select>
                                  <input name="btn_search" type="submit" class="button blue" value="{#systemlogBTNSearch#}" />
                              </form>
                          </div>
						  
						  <div class="container">
                    
                      {if $oLog_arr|@count == 0}
                          <br />
                          <p class="box_info">{#noDataAvailable#}</p>
                      {else}
                    
                        <div id="highlighted">
                           <table class="list">
                               <thead>
                                   <th class="tleft" style="width: 85%">Meldung</th>
                                   <th>Typ</th>
                                   <th>Datum</th>
                               </thead>
                               <tbody>
                                   {foreach from=$oLog_arr item="oLog"}
                                       <tr>
                                          <td>
                                               <div class="highlight">{$oLog->getcLog()}</div>
                                           </td>
                                           <td class="tcenter" valign="top">
                                               {if $oLog->getLevel() == 1}
                                                   <span class="error">{#systemlogError#}</span>
                                               {elseif $oLog->getLevel() == 2}
                                                   <span class="success">{#systemlogNotice#}</span>
                                               {elseif $oLog->getLevel() == 4}
                                                   <span class="info">{#systemlogDebug#}</span>
                                               {else}
                                                   Unbekannt
                                               {/if}
                                           </td>
                                           <td class="tcenter" valign="top">{$oLog->getErstellt()|date_format:"%d.%m.%Y - %H:%M:%S"}</td>
                                       </tr>
                                   {/foreach}
                               </tbody>
                           </table>
                        </div>
                      {/if}
						  </div>
				</div>
                <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
					 <h2>{#systemlogConfig#}</h2>
					 <div id="settings">
						  <form name="einstellen" method="post" action="systemlog.php">
                          <input type="hidden" name="{$session_name}" value="{$session_id}">
                          <input type="hidden" name="einstellungen" value="1">
                          <input type="hidden" name="tab" value="einstellungen">
                          <div class="settings">
                                <p>
                                    <label for="nFlag"> {#systemlogLevel#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#systemlogLevelDesc#}" title="{#systemlogLevelDesc#}" style="vertical-align:middle; cursor:help;" /></label>
                                    <input name="nFlag[]" type="checkbox" value="{$JTLLOG_LEVEL_ERROR}"{if $nFlag_arr[$JTLLOG_LEVEL_ERROR] != 0} checked{/if} /> Fehler
                                    <input name="nFlag[]" type="checkbox" value="{$JTLLOG_LEVEL_NOTICE}"{if $nFlag_arr[$JTLLOG_LEVEL_NOTICE] != 0} checked{/if} /> Hinweis
                                    <input name="nFlag[]" type="checkbox" value="{$JTLLOG_LEVEL_DEBUG}"{if $nFlag_arr[$JTLLOG_LEVEL_DEBUG] != 0} checked{/if} /> Debug
                                </p>                                
                          </div>

                          <p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
                          </form>
					 </div>
				</div>
		  </div>
	 </div>
</div>

{include file='tpl_inc/footer.tpl'}