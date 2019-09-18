{config_load file="$lang.conf" section="dbupdater"}
{config_load file="$lang.conf" section="shopupdate"}

{function migration_list manager=null title='' filter=0} {* filter: 0 - All, 1 - Executed, 2 - Pending *}
    {if $title|strlen > 0}
        <h4>{$title}</h4>
    {/if}

    <div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th width="5%">#</th>
            <th width="60%">Migration</th>
            <th width="250%" class="text-center">{if $filter != 2}Ausgef&uuml;hrt{/if}</th>
            <th width="10%" class="text-center"></th>
        </tr>
        </thead>
        <tbody>
          {$migrationIndex = 1}
          {$executedMigrations = $manager->getExecutedMigrations()}
          {foreach $manager->getMigrations()|@array_reverse as $m}
              {$executed = $m->getId()|in_array:$executedMigrations}
              {if $filter == 0 || ($filter == 1 && $executed) || ($filter == 2 && !$executed)}
                  <tr class="text-vcenter">
                      <th scope="row">{$migrationIndex++}</th>
                      <td>
                        {$m->getDescription()}<br>
                        {if $m->getCreated()}
                          <small class="text-muted"><i class="fa fa-clock-o" aria-hidden="true"></i> {$m->getCreated()|date_format:"d.m.Y - H:i:s"}&nbsp;&nbsp;</small>
                        {/if}
                        <small class="text-muted"><i class="fa fa-file-code-o" aria-hidden="true"></i> {$m->getName()}</small>
                      </td>
                      <td class="text-center"><span class="migration-created">{if $executed}<i class="fa fa-check text-success" aria-hidden="true"></i> {/if}{if $m->getExecuted()}{$m->getExecuted()|date_format:"d.m.Y - H:i:s"}{/if}</span></td>
                      <td class="text-center">
                          <a {if $executed}style="display:none"{/if} href="dbupdater.php?action=migration" data-callback="migration" data-dir="up" data-id="{$m->getId()}" class="btn btn-success btn-xs" {if $executed}disabled="disabled"{/if}><i class="fa fa-arrow-up"></i></a>
                          <a {if !$executed}style="display:none"{/if} href="dbupdater.php?action=migration" data-callback="migration" data-dir="down" data-id="{$m->getId()}" class="btn btn-warning btn-xs" {if !$executed}disabled="disabled"{/if}><i class="fa fa-arrow-down"></i></a>
                      </td>
                  </tr>
              {/if}
          {/foreach}
        </tbody>
    </table>
    </div>
{/function}

<form name="updateForm" method="post" id="form-update">
    {$jtl_token}
    <input type="hidden" name="update" value="1" />
    {if $updatesAvailable}
        <div class="alert alert-warning">
            <h4><i class="fa fa-warning"></i> Datenbankaktualisierung {if $currentDatabaseVersion != $currentFileVersion}von Version {formatVersion value=$currentDatabaseVersion} auf Version {formatVersion value=$currentFileVersion}{/if} erforderlich</h4>
            Klicken Sie auf <a href="dbupdater.php?action=update" data-callback="update">jetzt aktualisieren</a>, um die Datenbankaktualisierung durchzuf&uuml;hren.
        </div>

        <div class="btn-group btn-group-md" id="btn-update-group" role="group">
            <a href="dbupdater.php?action=update" class="btn btn-success" data-callback="update"><i class="fa fa-flash"></i> Jetzt aktualisieren</a>
            <div class="btn-group btn-group-md" role="group">
                <button id="backup-button" type="button" class="btn btn-default dropdown-toggle ladda-button" data-size="l" data-style="zoom-out" data-spinner-color="#000" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="ladda-label">Sicherungskopie &nbsp; <i class="fa fa-caret-down"></i></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="dbupdater.php?action=backup" data-callback="backup"><i class="fa fa-cloud-download"></i> &nbsp; Auf Server ablegen</a></li>
                    <li><a href="dbupdater.php?action=backup&download" data-callback="backup" data-download="true"><i class="fa fa-download"></i> &nbsp; Herunterladen</a></li>
                </ul>
            </div>
        </div>

        <br /><br />
        <h4>Ereignisprotokoll</h4>
        <pre id="debug"><div>{#currentShopVersion#}</div><div>     System: {formatVersion value=$currentFileVersion}</div><div>     Datenbank: {formatVersion value=$currentDatabaseVersion}</div>{if $currentTemplateFileVersion != $currentTemplateDatabaseVersion}<div>{#currentTemplateVersion#}</div><div>     System: {formatVersion value=$currentTemplateFileVersion}</div><div>     Datenbank: {formatVersion value=$currentTemplateDatabaseVersion}</div>{/if}</pre>
    {else}
        <div class="alert alert-success">
            <ul class="hlist">
                <li class="p50 text-x2 text-center"><i class="fa fa-check"></i> Ihr System ist auf dem neuesten Stand.</li>
                <li class="p50 text-x2 text-center">JTL-Shop {formatVersion value=$currentDatabaseVersion}</li>
            </ul>
        </div>
    {/if}
</form>

{if isset($manager) && is_object($manager)}
    <p>&nbsp;</p>
    {if $updatesAvailable}
        {migration_list manager=$manager filter=2 title='Nicht-ausgef&uuml;hrte Migrationen'}
    {/if}
    {migration_list manager=$manager filter=1 title='Erfolgreiche Migrationen'}
{/if}